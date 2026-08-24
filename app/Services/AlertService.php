<?php

namespace App\Services;

use App\Models\Input;
use App\Models\Order;
use App\Models\Purchase;
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

        // Compras atrasadas
        Purchase::whereNotIn('status', ['received', 'cancelled'])
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
                    'detail' => "Stock " . (int) $r->stock_units . " unidades; tránsito " . (int) $r->transit_units . "; venta semanal " . (int) $r->weekly_sales . ".",
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
}
