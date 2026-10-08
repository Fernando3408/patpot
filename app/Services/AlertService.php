<?php

namespace App\Services;

use App\Models\Input;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Retail;
use App\Models\Task;
use Illuminate\Support\Collection;

class AlertService
{
    public function getAlerts(): Collection
    {
        $alerts = collect();

        // Insumos críticos
        Input::with(['recipes.product.productions'])->get()
            ->filter(fn (Input $i) => $i->inventory_level === 'critico')
            ->each(function (Input $i) use ($alerts) {
                $alerts->push([
                    'level' => 'critical',
                    'module' => 'Insumos',
                    'title' => $i->name,
                    'detail' => "Stock {$i->formattedStock()} {$i->unit}; cobertura {$i->coverage_weeks} semanas; compra sugerida {$i->suggested_purchase} {$i->unit}.",
                    'action_url' => '/insumos',
                ]);
            });

        // Insumos y servicios bajo stock de seguridad
        Input::where('status', true)
            ->where('safety_stock', '>', 0)
            ->get()
            ->filter(fn (Input $i) => (float) $i->stock > 0 && (float) $i->stock <= (float) $i->safety_stock && $i->inventory_level !== 'critico')
            ->each(function (Input $i) use ($alerts) {
                $pct = $i->safety_stock > 0 ? round((float) $i->stock / (float) $i->safety_stock * 100) : 0;
                $alerts->push([
                    'level' => 'warning',
                    'module' => 'Insumos',
                    'title' => "{$i->name} bajo el mínimo",
                    'detail' => "Quedan {$i->formattedStock()} {$this->unitLabel($i->unit, (float) $i->stock)}; mínimo definido {$i->formattedSafetyStock()} {$this->unitLabel($i->unit, (float) $i->safety_stock)}.",
                    'action_url' => '/insumos',
                ]);
            });

        Product::where('status', 'active')->whereColumn('stock_boxes', '<=', 'min_stock_boxes')->get()->each(function (Product $product) use ($alerts): void {
            $stock = (float) $product->stock_boxes;
            $minimum = (float) $product->min_stock_boxes;
            $alerts->push(['level' => $stock <= 0 ? 'critical' : 'warning', 'module' => 'Productos', 'title' => $stock <= 0 ? "{$product->name} sin stock" : "{$product->name} bajo el mínimo", 'detail' => "Quedan ".number_format($stock, 0, ',', '.')." cajas; mínimo definido ".number_format($minimum, 0, ',', '.')." cajas.", 'action_url' => '/productos']);
        });

        $pendingConsumption = collect();
        Order::whereIn('status', ['pending', 'partial'])->with('lines.product.recipes')->get()->each(function (Order $order) use ($pendingConsumption): void {
            foreach ($order->lines as $line) {
                foreach ($line->product?->recipes ?? [] as $recipe) {
                    $pendingConsumption[$recipe->input_id] = ($pendingConsumption[$recipe->input_id] ?? 0) + ((float) $recipe->qty_per_box * max(0, (int) $line->boxes - (int) $line->dispatched_boxes));
                }
            }
        });
        Input::whereIn('id', $pendingConsumption->keys())->get()->each(function (Input $input) use ($pendingConsumption, $alerts): void {
            $required = (float) $pendingConsumption[$input->id];
            if ((float) $input->stock < $required) {
                $alerts->push(['level' => 'critical', 'module' => 'Pedidos', 'title' => "Stock insuficiente: {$input->name}", 'detail' => "Stock actual {$input->formattedStock()} {$input->unit}; pedidos pendientes requieren ".rtrim(rtrim(number_format($required, 3, ',', '.'), '0'), ',')." {$input->unit}.", 'action_url' => '/pedidos']);
            }
        });

        // Compras atrasadas
        Purchase::where('status', '!=', 'received')
            ->where('expected_on', '<', now()->toDateString())
            ->with('supplier')
            ->get()
            ->each(function (Purchase $p) use ($alerts) {
                $supplierName = $p->supplier?->name ?? '—';
                $expectedDate = $p->expected_on?->format('d-m-Y') ?? '—';
                $alerts->push([
                    'level' => 'critical',
                    'module' => 'Compras',
                    'title' => "{$p->number} atrasada",
                    'detail' => "Proveedor: {$supplierName}. Entrega comprometida {$expectedDate}.",
                    'action_url' => '/compras',
                ]);
            });

        // Pedidos atrasados
        Order::whereIn('status', ['pending', 'partial'])
            ->where('delivery_on', '<', now()->toDateString())
            ->with('customer')
            ->get()
            ->each(function (Order $o) use ($alerts) {
                $pending = $o->lines->sum(fn ($l) => $l->boxes - $l->dispatched_boxes);
                $customerName = $o->customer?->trade_name ?? $o->customer?->business_name ?? '—';
                $alerts->push([
                    'level' => 'critical',
                    'module' => 'Pedidos',
                    'title' => "{$o->number} atrasado",
                    'detail' => "Cliente: {$customerName}. Pendiente {$pending} cajas.",
                    'action_url' => '/pedidos',
                ]);
            });

        // Retail en quiebre
        Retail::with(['store', 'product'])->get()
            ->filter(fn (Retail $r) => $r->is_break)
            ->each(function (Retail $r) use ($alerts) {
                $alerts->push([
                    'level' => 'critical',
                    'module' => 'Retail',
                    'title' => "{$r->store?->name} · {$r->product?->name}",
                    'detail' => 'Stock '.(int) $r->stock_units.' unidades; tránsito '.(int) $r->transit_units.'; venta semanal '.(int) $r->weekly_sales.'.',
                    'action_url' => '/retail',
                ]);
            });

        // Tareas vencidas
        Task::where('status', 'pending')
            ->where('due_on', '<', now()->toDateString())
            ->get()
            ->each(function (Task $t) use ($alerts) {
                $dueDate = $t->due_on?->format('d-m-Y') ?? '—';
                $owner = $t->owner ?? 'Sin asignar';
                $alerts->push([
                    'level' => 'critical',
                    'module' => 'Tareas',
                    'title' => $t->title,
                    'detail' => "Venció {$dueDate} · responsable {$owner}.",
                    'action_url' => '/tareas',
                ]);
            });

        return $alerts;
    }

    private function unitLabel(string $unit, float $quantity): string
    {
        return strtolower($unit) === 'unidad' && $quantity != 1.0 ? 'unidades' : $unit;
    }
}
