<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatKnowledge;
use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\Retail;
use App\Models\ShipmentLine;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    private string $apiKey;
    private string $model = 'openai/gpt-oss-120b';
    private int $maxHistory = 20;
    private int $maxContextTokens = 120000;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', env('GROQ_API_KEY'));
    }

    public function handle(string $message, ?int $userId = null): string
    {
        $lower = mb_strtolower(trim($message));

        // Saludos rápidos (sin IA)
        if (in_array($lower, ['hola', 'buenas', 'hey', 'hello', 'hi', 'buenos dias', 'buenas tardes'])) {
            return $this->greeting();
        }

        // Comandos directos (sin IA)
        if (in_array($lower, ['ayuda', 'help', 'comandos', 'opciones'])) {
            return $this->helpText();
        }

        // Detectar intención para pre-cargar datos específicos
        $intent = $this->detectIntent($lower);

        // Obtener o crear conversación
        $conversation = $this->getOrCreateConversation($userId);
        $this->saveMessage($conversation->id, 'user', $message);

        // Obtener historial conversacional
        $history = $this->getConversationHistory($conversation->id);

        // Construir contexto enriquecido
        $systemPrompt = $this->buildSystemPrompt($intent);

        // Enviar a IA
        $response = $this->askAI($message, $history, $systemPrompt);

        // Si la IA falló, intentar con LocalChatService
        if ($this->isError($response)) {
            $localService = app(LocalChatService::class);
            if ($localService->canHandle($message)) {
                $response = $localService->handle($message);
            } else {
                $response = 'No pude procesar tu consulta. Intenta reformular o usa "ayuda" para ver los comandos disponibles.';
            }
        }

        $this->saveMessage($conversation->id, 'assistant', $response);

        return $response;
    }

    /**
     * Detectar la intención del usuario para pre-cargar contexto relevante
     */
    private function detectIntent(string $message): string
    {
        $intents = [
            'stock' => ['stock', 'inventario', 'disponible', 'cuanto hay', 'cuanta', 'cantidad'],
            'pedidos' => ['pedido', 'pedidos', 'orden', 'venta', 'despacho', 'despachar', 'entrega'],
            'produccion' => ['produccion', 'producciones', 'fabricar', 'fabricacion', 'cerrar produccion', 'op'],
            'compras' => ['compra', 'compras', 'proveedor', 'recepcion', 'recibir', 'oc'],
            'retail' => ['retail', 'quiebre', 'tienda', 'sala', 'repuesto'],
            'precios' => ['precio', 'precios', 'costo', 'costos', 'margen'],
            'recetas' => ['receta', 'recetas', 'ingrediente', 'insumo', 'formula', 'bom'],
            'clientes' => ['cliente', 'clientes', 'cuenta'],
            'critico' => ['critico', 'urgente', 'alerta', 'alertas', 'problema', 'bajo minimo'],
            'resumen' => ['resumen', 'dashboard', 'estado', 'general', 'todo'],
            'sugerencias' => ['comprar', 'reponer', 'reorden', 'cuando debo', 'sugerencia', 'sugerir'],
            'tareas' => ['tarea', 'tareas', 'pendiente', 'pendientes'],
        ];

        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    /**
     * System prompt mejorado con rol, personalidad y capacidades definidas
     */
    private function buildSystemPrompt(string $intent = 'general'): string
    {
        $cacheKey = 'chatbot_system_prompt_v3';
        return Cache::remember($cacheKey, 1800, fn () => $this->buildFreshPrompt());
    }

    private function buildFreshPrompt(): string
    {
        $prompt = <<<'PROMPT'
Eres PatBot, el asistente inteligente del sistema ERP de PatPot, una empresa chilena de papas fritas gourmet artesanales.

## TU ROL
- Eres el asistente operativo del negocio. Ayudas a gerencia, producción, ventas y compras.
- Respondes preguntas sobre el estado actual del negocio usando datos reales del sistema.
- Eres preciso, directo y profesional. Usas chilenismos de forma natural cuando es apropiado.
- Si no tienes un dato, dilo claramente. Nunca inventes información.

## CAPACIDADES
Puedes responder sobre:
- **Stock**: productos terminados e insumos con niveles, alertas y cobertura
- **Pedidos**: estado de pedidos, despachos, entregas pendientes
- **Producción**: órdenes de producción, cierres, consumo de insumos
- **Compras**: órdenes de compra, recepciones, tránsito de material
- **Precios**: precios por cliente, ofertas, márgenes
- **Recetas**: ingredientes, cantidades, costos por producto
- **Clientes**: información de clientes y sus tiendas
- **Retail**: stock en tiendas, quiebres, reposiciones
- **Sugerencias**: cuándo comprar, qué reponer, prioridades
- **Resumen**: dashboard ejecutivo del negocio

## REGLAS DE RESPUESTA
1. Usa SOLO los datos que te proporciono en el contexto. No inventes.
2. Sé conciso: máximo 5-6 líneas por respuesta a menos que te pidan detalle.
3. Usa formato: listas, negritas, separadores para hacer la info legible.
4. Si te piden crear/editar/eliminar algo, indica que eso se hace desde el ERP web.
5. Si detectas un problema (stock bajo, pedido atrasado), menciónalo proactivamente.
6. Puedes hacer cálculos simples (totales, porcentajes, días restantes).
7. Responde en español chileno natural.

## CONOCIMIENTO DEL NEGOCIO
PROMPT;

        // Cargar conocimiento de la base de datos
        $knowledge = ChatKnowledge::where('active', true)->get();
        if ($knowledge->isNotEmpty()) {
            $byCategory = $knowledge->groupBy('category');
            foreach ($byCategory as $category => $items) {
                $prompt .= "\n### {$category}\n";
                foreach ($items as $item) {
                    $prompt .= "- {$item->key}: {$item->content}\n";
                }
            }
        }

        // Cargar datos actuales del sistema
        $prompt .= $this->loadCurrentData();

        return $prompt;
    }

    /**
     * Cargar datos actuales del sistema para el contexto
     */
    private function loadCurrentData(): string
    {
        $ctx = "\n## DATOS ACTUALES DEL SISTEMA\n";
        $ctx .= "_(actualizados el " . now()->format('d/m/Y H:i') . ")_\n";

        // Productos
        $products = Product::where('status', 'active')->get(['id', 'name', 'sku', 'stock_boxes', 'min_stock_boxes', 'sale_price_box', 'production_cost']);
        if ($products->isNotEmpty()) {
            $ctx .= "\n### Productos terminados\n";
            foreach ($products as $p) {
                $alert = $p->stock_boxes <= 0 ? ' ❌ SIN STOCK' : ($p->stock_boxes < $p->min_stock_boxes ? ' ⚠️ BAJO MÍNIMO' : '');
                $costo = $p->production_cost ? " costo: \$" . number_format($p->production_cost, 0, ',', '.') : '';
                $ctx .= "- **{$p->name}** ({$p->sku}): {$p->stock_boxes} cajas, \${$p->sale_price_box}/caja{$costo}{$alert}\n";
            }
        }

        // Recetas
        $recipes = Recipe::with('input:id,name,unit,unit_cost,type')->get();
        $recipesByProduct = $recipes->groupBy('product_id');
        if ($recipesByProduct->isNotEmpty()) {
            $ctx .= "\n### Recetas (insumo por caja)\n";
            foreach ($products as $p) {
                $productRecipes = $recipesByProduct->get($p->id, collect());
                if ($productRecipes->isNotEmpty()) {
                    $ctx .= "**{$p->name}:**\n";
                    foreach ($productRecipes as $r) {
                        $tipo = $r->input->type === 'service' ? ' _(servicio, no descuenta stock)_' : '';
                        $costoLinea = $r->qty_per_box * $r->input->unit_cost;
                        $ctx .= "  - {$r->input->name}: " . rtrim(rtrim(rtrim(number_format($r->qty_per_box, 3, ',', '.'), '0'), '.'), ',') . " {$r->input->unit} (\$" . number_format($costoLinea, 0, ',', '.') . "){$tipo}\n";
                    }
                }
            }
        }

        // Insumos
        $inputs = Input::where('type', 'material')->where('status', true)
            ->get(['name', 'code', 'stock', 'safety_stock', 'weekly_consumption', 'lead_time_days', 'target_weeks', 'min_purchase', 'purchase_multiple', 'unit', 'unit_cost', 'transit']);
        if ($inputs->isNotEmpty()) {
            $ctx .= "\n### Insumos (materiales)\n";
            foreach ($inputs as $i) {
                $nivel = $i->stock <= 0 ? '❌ SIN STOCK' : ($i->stock <= $i->safety_stock ? '⚠️ BAJO' : '✅ OK');
                $cobertura = $i->weekly_consumption > 0 ? round($i->stock / $i->weekly_consumption, 1) : '∞';
                $diasRestantes = $i->weekly_consumption > 0 ? round(($i->stock - $i->safety_stock) / ($i->weekly_consumption / 7)) : '∞';
                $transito = $i->transit > 0 ? ", tránsito: {$i->transit} {$i->unit}" : '';
                $ctx .= "- **{$i->name}** ({$i->code}): {$i->stock} {$i->unit} {$nivel}{$transito}\n";
                $ctx .= "  Cobertura: {$cobertura} sem | Lead time: {$i->lead_time_days} días | Días para reordenar: {$diasRestantes}\n";
            }
        }

        // Clientes
        $customers = Customer::where('status', true)->withCount('orders')->get(['id', 'business_name', 'trade_name', 'code']);
        if ($customers->isNotEmpty()) {
            $ctx .= "\n### Clientes\n";
            foreach ($customers as $c) {
                $ctx .= "- **{$c->business_name}** ({$c->code}): {$c->orders_count} pedidos\n";
            }
        }

        // Precios
        $prices = Price::with(['customer', 'product'])->get()->groupBy('customer_id');
        if ($prices->isNotEmpty()) {
            $ctx .= "\n### Precios por cliente\n";
            foreach ($prices as $customerPrices) {
                $customer = $customerPrices->first()->customer;
                if (!$customer) continue;
                $ctx .= "**{$customer->business_name}:**\n";
                foreach ($customerPrices as $pr) {
                    $oferta = $pr->offer_price && $pr->offer_until && $pr->offer_until->isFuture()
                        ? " _(oferta: \$" . number_format($pr->offer_price, 0, ',', '.') . " hasta {$pr->offer_until->format('d/m/Y')})_"
                        : '';
                    $ctx .= "  - {$pr->product->name}: \$" . number_format($pr->price_box, 0, ',', '.') . "/caja{$oferta}\n";
                }
            }
        }

        // Proveedores
        $suppliers = Supplier::where('status', true)->get(['name', 'rut']);
        if ($suppliers->isNotEmpty()) {
            $ctx .= "\n### Proveedores\n";
            foreach ($suppliers as $s) {
                $ctx .= "- {$s->name} ({$s->rut})\n";
            }
        }

        // Resumen operativo
        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();
        $pendingPurchases = Purchase::whereIn('status', ['pending', 'partial'])->count();
        $retailBreaks = Retail::where('cataloged', true)->get()->filter(fn ($r) => $r->is_break)->count();
        $salesMonth = ShipmentLine::whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', now()->startOfMonth()->toDateString()))
            ->sum(DB::raw('price_box * boxes'));
        $salesLastMonth = ShipmentLine::whereHas('shipment', fn ($q) => $q->whereBetween('shipped_on', [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()]))
            ->sum(DB::raw('price_box * boxes'));

        $ctx .= "\n### Resumen operativo\n";
        $ctx .= "- Pedidos pendientes: {$pendingOrders}\n";
        $ctx .= "- Producciones en curso: {$pendingProductions}\n";
        $ctx .= "- Compras pendientes: {$pendingPurchases}\n";
        $ctx .= "- Quiebres retail: {$retailBreaks}\n";
        $ctx .= "- Ventas este mes: \$" . number_format($salesMonth, 0, ',', '.') . "\n";
        if ($salesLastMonth > 0) {
            $diff = round((($salesMonth - $salesLastMonth) / $salesLastMonth) * 100);
            $trend = $diff > 0 ? "+{$diff}%" : "{$diff}%";
            $ctx .= "- Variación vs mes anterior: {$trend}\n";
        }

        // Alertas automáticas
        $criticos = $inputs->filter(fn ($i) => $i->stock <= 0);
        $bajos = $inputs->filter(fn ($i) => $i->stock > 0 && $i->stock <= $i->safety_stock);
        if ($criticos->isNotEmpty() || $bajos->isNotEmpty()) {
            $ctx .= "\n### ⚠️ ALERTAS ACTIVAS\n";
            foreach ($criticos as $i) {
                $ctx .= "- ❌ **{$i->name}**: SIN STOCK (consumo semanal: {$i->weekly_consumption} {$i->unit})\n";
            }
            foreach ($bajos as $i) {
                $ctx .= "- ⚠️ **{$i->name}**: {$i->stock} {$i->unit} bajo mínimo (seguridad: {$i->safety_stock})\n";
            }
        }

        return $ctx;
    }

    private function greeting(): string
    {
        $name = auth()->check() ? auth()->user()->name : 'usuario';
        return "Hola {$name}! 👋 Soy PatBot, tu asistente del ERP de PatPot.\n\n"
            . "Puedo ayudarte con:\n"
            . "• **Stock** de productos e insumos\n"
            . "• **Pedidos** y despachos\n"
            . "• **Producción** y recetas\n"
            . "• **Compras** y proveedores\n"
            . "• **Precios** y clientes\n"
            . "• **Alertas** y sugerencias\n\n"
            . "Escribe tu pregunta o usa **ayuda** para ver todos los comandos.";
    }

    private function helpText(): string
    {
        return "🤖 **Comandos de PatBot:**\n\n"
            . "**Consultas rápidas:**\n"
            . "• `stock` — Inventario completo\n"
            . "• `pedidos` — Pedidos pendientes\n"
            . "• `producción` — Órdenes en curso\n"
            . "• `compras` — Órdenes de compra\n"
            . "• `retail` — Quiebres en tiendas\n"
            . "• `precios` — Precios por cliente\n"
            . "• `recetas` — Fórmulas de productos\n"
            . "• `clientes` — Lista de clientes\n"
            . "• `alertas` — Stock crítico\n"
            . "• `sugerencias` — Qué comprar\n"
            . "• `resumen` — Dashboard ejecutivo\n\n"
            . "**Preguntas libres:**\n"
            . "• \"¿Cuánto stock hay de papas?\"\n"
            . "• \"¿Qué pedidos están atrasados?\"\n"
            . "• \"¿Cuánto cuesta producir una caja?\"\n"
            . "• \"¿Cuándo debo comprar aceite?\"\n"
            . "• \"Dame un resumen del mes\"\n\n"
            . "**Contexto:**\n"
            . "Puedo recordar lo que hablamos en esta conversación. Si me dices \"recuerda que...\", lo guardaré.";
    }

    private function getOrCreateConversation(?int $userId): ChatConversation
    {
        if (!$userId) {
            return ChatConversation::create(['user_id' => 1, 'title' => 'Chat sin sesión']);
        }

        // Reutilizar conversación activa de las últimas 2 horas
        $recent = ChatConversation::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHours(2))
            ->latest()
            ->first();

        if ($recent) {
            return $recent;
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

    private function askAI(string $message, array $history, string $systemPrompt): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 1024,
            'temperature' => 0.5,
            'top_p' => 0.9,
        ];

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(20)->post('https://api.groq.com/openai/v1/chat/completions', $payload);

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content') ?? '';
                    // Strip thinking blocks
                    $text = preg_replace('#<think>[\s\S]*?</think>#u', '', $text);
                    $text = preg_replace('#<think>.*?</think>#us', '', $text);
                    $text = preg_replace('#<think>[\s\S]*?$#u', '', $text);
                    $text = trim($text);
                    return $text ?: 'No pude generar una respuesta.';
                }

                // Si es rate limit, no reintentar
                if ($response->status() === 429) {
                    return 'Error IA';
                }

                \Log::warning('Groq API error', [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                    'body' => $response->body(),
                ]);

                if ($attempt === 2) {
                    return 'Error IA';
                }

                sleep(1);
            } catch (\Exception $e) {
                \Log::error('Groq API exception', ['message' => $e->getMessage()]);
                return 'Error IA';
            }
        }

        return 'Error IA';
    }

    private function isError(string $response): bool
    {
        return $response === 'Error IA'
            || str_starts_with($response, 'Error')
            || str_starts_with($response, 'Estoy')
            || str_starts_with($response, 'La IA')
            || str_starts_with($response, 'No pude procesar');
    }
}
