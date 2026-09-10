<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatKnowledge;
use App\Models\Input;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Retail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    private string $apiKey;
    private string $model = 'openai/gpt-oss-120b';
    private int $maxHistory = 6;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', env('GROQ_API_KEY'));
    }

    public function handle(string $message, ?int $userId = null): string
    {
        $lower = mb_strtolower(trim($message));

        if (in_array($lower, ['hola', 'buenas', 'hey', 'hello', 'hi'])) {
            return 'Hola! Soy el asistente de PatPot. Puedo consultarte sobre stock, pedidos, producciones, retail y más. ¿Qué necesitas?';
        }

        if (in_array($lower, ['ayuda', 'help', 'comandos', 'opciones'])) {
            return $this->helpText();
        }

        $conversation = $this->getOrCreateConversation($userId);
        $this->saveMessage($conversation->id, 'user', $message);

        $history = $this->getConversationHistory($conversation->id);
        $response = $this->askAI($message, $history);

        // Si la IA falló, informar al usuario
        if ($response === 'Error IA' || str_starts_with($response, 'Error') || str_starts_with($response, 'Estoy') || str_starts_with($response, 'La IA')) {
            $response = 'Se agotaron las consultas de IA disponibles. Intenta más tarde o consulta directamente en el ERP.';
        }

        $this->saveMessage($conversation->id, 'assistant', $response);

        return $response;
    }

    private function getOrCreateConversation(?int $userId): ChatConversation
    {
        if (!$userId) {
            return ChatConversation::create(['user_id' => 1, 'title' => 'Chat sin sesión']);
        }

        $existing = ChatConversation::where('user_id', $userId)->latest()->first();
        if ($existing) {
            return $existing;
        }

        return ChatConversation::create([
            'user_id' => $userId,
            'title' => 'Conversación ' . now()->format('d/m/Y H:i'),
        ]);
    }

    private function saveMessage(int $conversationId, string $role, string $content): void
    {
        ChatMessage::create([
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
            'tokens' => (int) ceil(strlen($content) / 4),
        ]);
    }

    private function getConversationHistory(int $conversationId): array
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->latest()
            ->limit($this->maxHistory)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'role' => $m->role === 'assistant' ? 'assistant' : 'user',
                'content' => $m->content,
            ])
            ->values()
            ->toArray();
    }

    private function buildSystemPrompt(): string
    {
        $cacheKey = 'chatbot_context_' . now()->format('Y-m-d_H');
        return Cache::remember($cacheKey, 3600, fn () => $this->buildFreshContext());
    }

    private function buildFreshContext(): string
    {
        $ctx = "IDENTIDAD: Eres el chatbot interno del ERP de PatPot. Eres parte del sistema. Respondes preguntas sobre el negocio.\n\n";
        $ctx .= "DATOS QUE YA TIENES (NO necesitas pedir nada, NO necesitas acceso adicional):\n";
        $ctx .= "- Inventario de productos terminados con stock y precios\n";
        $ctx .= "- Stock de todos los insumos materiales\n";
        $ctx .= "- Recetas completas de cada producto con ingredientes y cantidades\n";
        $ctx .= "- Clientes y cantidad de pedidos\n";
        $ctx .= "- Proveedores\n";
        $ctx .= "- Resumen de pedidos, producciones, quiebres y ventas\n";
        $ctx .= "- Documentación completa del sistema (tablas, relaciones, flujos, reglas)\n\n";
        $ctx .= "COMO RESPONDER:\n";
        $ctx .= "- Usa SOLO los datos de arriba. Si algo no está, di 'No tengo ese dato'.\n";
        $ctx .= "- Sé directo. Máximo 3 líneas.\n";
        $ctx .= "- Si te piden crear/modificar algo, di que eso se hace desde el ERP web, no desde el chat.\n\n";

        // Leer documentación del sistema
        $mdPath = base_path('ARQUITECTURA.md');
        if (file_exists($mdPath)) {
            $mdContent = file_get_contents($mdPath);
            $ctx .= "DOCUMENTACIÓN DEL SISTEMA:\n" . $mdContent . "\n\n";
        }

        $products = Product::where('status', 'active')->get(['id', 'name', 'sku', 'stock_boxes', 'sale_price_box']);
        $ctx .= "PRODUCTOS ACTUALES:\n";
        foreach ($products as $p) {
            $ctx .= "{$p->name} ({$p->sku}): {$p->stock_boxes} cajas, \${$p->sale_price_box}/caja\n";
        }

        // Recetas por producto
        $recipes = \App\Models\Recipe::with('input:id,name,unit,unit_cost,type')->get();
        $recipesByProduct = $recipes->groupBy('product_id');
        $ctx .= "\nRECETAS (insumo por caja):\n";
        foreach ($products as $p) {
            $productRecipes = $recipesByProduct->get($p->id, collect());
            if ($productRecipes->isNotEmpty()) {
                $ctx .= "{$p->name}:\n";
                foreach ($productRecipes as $r) {
                    $tipo = $r->input->type === 'service' ? ' (servicio, no descuenta)' : '';
                    $ctx .= "  - {$r->input->name}: {$r->qty_per_box} {$r->input->unit} (\${$r->input->unit_cost}/{$r->input->unit}){$tipo}\n";
                }
            }
        }

        $inputs = Input::where('type', 'material')
            ->where('status', true)
            ->get(['name', 'code', 'stock', 'safety_stock', 'weekly_consumption', 'lead_time_days', 'target_weeks', 'min_purchase', 'purchase_multiple', 'unit', 'unit_cost', 'transit']);
        $ctx .= "\nINSUMOS ACTUALES:\n";
        foreach ($inputs as $i) {
            $nivel = $i->stock <= 0 ? 'SIN STOCK' : ($i->stock <= $i->safety_stock ? 'BAJO' : 'OK');
            $cobertura = $i->weekly_consumption > 0 ? round($i->stock / $i->weekly_consumption, 1) : '∞';
            $puntoReorden = $i->safety_stock + ($i->weekly_consumption * $i->lead_time_days / 7);
            $ctx .= "{$i->name} ({$i->code}): {$i->stock} {$i->unit} [{$nivel}]\n";
            $ctx .= "  Safety: {$i->safety_stock} {$i->unit} | Consumo semanal: {$i->weekly_consumption} {$i->unit}\n";
            $ctx .= "  Cobertura: {$cobertura} semanas | Lead time: {$i->lead_time_days} días | Transito: {$i->transit} {$i->unit}\n";
            $ctx .= "  Costo: \${$i->unit_cost}/{$i->unit} | Mín compra: {$i->min_purchase} {$i->unit} | Múltiplo: {$i->purchase_multiple} {$i->unit}\n";
        }

        $customers = \App\Models\Customer::where('status', true)->withCount('orders')->get(['id', 'business_name', 'trade_name', 'code']);
        $ctx .= "\nCLIENTES:\n";
        foreach ($customers as $c) {
            $ctx .= "{$c->business_name} ({$c->code}): {$c->orders_count} pedidos\n";
        }

        $prices = \App\Models\Price::with(['customer', 'product'])->get();
        $pricesByCustomer = $prices->groupBy('customer_id');
        $ctx .= "\nPRECIOS POR CLIENTE:\n";
        foreach ($pricesByCustomer as $customerId => $customerPrices) {
            $customer = $customerPrices->first()->customer;
            if (!$customer) continue;
            $ctx .= "{$customer->business_name} ({$customer->code}):\n";
            foreach ($customerPrices as $pr) {
                $oferta = $pr->offer_price && $pr->offer_until && $pr->offer_until->isFuture()
                    ? " OFERTA \${$pr->offer_price} hasta {$pr->offer_until->format('d/m/Y')}"
                    : '';
                $ctx .= "  - {$pr->product->name}: \${$pr->price_box}/caja{$oferta}\n";
            }
        }

        $suppliers = \App\Models\Supplier::where('status', true)->get(['name', 'rut']);
        $ctx .= "\nPROVEEDORES:\n";
        foreach ($suppliers as $s) {
            $ctx .= "{$s->name} ({$s->rut})\n";
        }

        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();
        $retailBreaks = Retail::where('cataloged', true)->get()->filter(fn ($r) => $r->is_break)->count();
        $salesMonth = \App\Models\ShipmentLine::whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', now()->startOfMonth()->toDateString()))
            ->sum(\DB::raw('price_box * boxes'));

        $ctx .= "\nRESUMEN ACTUAL:\n";
        $ctx .= "Pedidos pendientes: {$pendingOrders}\n";
        $ctx .= "Producciones en curso: {$pendingProductions}\n";
        $ctx .= "Quiebres retail: {$retailBreaks}\n";
        $ctx .= "Ventas del mes: \$" . number_format($salesMonth, 0, ',', '.') . "\n";

        return $ctx;
    }

    private function askAI(string $message, array $history): string
    {
        $systemPrompt = $this->buildSystemPrompt();

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 800,
            'temperature' => 0.7,
        ];

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', $payload);

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content') ?? '';
                    // Strip all thinking blocks (various formats)
                    $text = preg_replace('#<think>[\s\S]*?</think>#u', '', $text);
                    $text = preg_replace('#<think>.*?</think>#us', '', $text);
                    $text = preg_replace('#<think>[\s\S]*?$#u', '', $text);
                    $text = trim($text);
                    return $text ?: 'No pude generar una respuesta.';
                }

                \Log::warning('Groq API error', [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                ]);

                return 'Error IA';
            } catch (\Exception $e) {
                return 'Error IA';
            }
        }

        return 'Error IA';
    }

    private function helpText(): string
    {
        return "Comandos disponibles:\n\n"
            . "- Stock: Consulta de inventario\n"
            . "- Pedidos: Pedidos pendientes\n"
            . "- Produccion: Producciones en curso\n"
            . "- Retail: Quiebres y estado\n"
            . "- Ventas: Resumen de ventas\n"
            . "- Resumen: Dashboard completo\n"
            . "- Ayuda: Esta lista\n\n"
            . "También puedes hacer preguntas libres como:\n"
            . "\"¿Cuánto stock hay de papas?\"\n"
            . "\"¿Qué pedidos están atrasados?\"";
    }
}
