<?php

namespace App\Services;

use App\Models\Input;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Production;
use App\Models\Retail;
use App\Models\ShipmentLine;
use Illuminate\Support\Facades\DB;

class LocalChatService
{
    public function canHandle(string $message): bool
    {
        return true;
    }

    public function handle(string $message): string
    {
        $lower = mb_strtolower(trim($message));

        if (str_contains($lower, 'resumen') || str_contains($lower, 'dashboard')) {
            return $this->resumen();
        }
        if (str_contains($lower, 'stock') || str_contains($lower, 'inventario')) {
            return $this->stock();
        }
        if (str_contains($lower, 'pedido') || str_contains($lower, 'pedidos') || str_contains($lower, 'orden')) {
            return $this->pedidos();
        }
        if (str_contains($lower, 'produccion') || str_contains($lower, 'producciones')) {
            return $this->produccion();
        }
        if (str_contains($lower, 'retail') || str_contains($lower, 'quiebre')) {
            return $this->retail();
        }
        if (str_contains($lower, 'venta') || str_contains($lower, 'ventas') || str_contains($lower, 'vendido')) {
            return $this->ventas();
        }
        if (str_contains($lower, 'critico') || str_contains($lower, 'urgente') || str_contains($lower, 'alerta')) {
            return $this->criticos();
        }
        if (str_contains($lower, 'comprar') || str_contains($lower, 'reponer') || str_contains($lower, 'reorden') || str_contains($lower, 'cuando debo')) {
            return $this->sugerencias();
        }
        if (str_contains($lower, 'compra') || str_contains($lower, 'compras')) {
            return $this->compras();
        }
        if (str_contains($lower, 'precio') || str_contains($lower, 'precios')) {
            return $this->precios();
        }
        if (str_contains($lower, 'receta') || str_contains($lower, 'recetas') || str_contains($lower, 'ingrediente')) {
            return $this->recetas();
        }
        if (str_contains($lower, 'cliente')) {
            return $this->clientes();
        }

        return $this->resumen();
    }

    private function resumen(): string
    {
        $products = Product::where('status', 'active')->get();
        $inputs = Input::where('type', 'material')->where('status', true)->get();
        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();
        $retailBreaks = Retail::where('cataloged', true)->get()->filter(fn ($r) => $r->is_break)->count();
        $salesMonth = ShipmentLine::whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', now()->startOfMonth()->toDateString()))
            ->sum(DB::raw('price_box * boxes'));

        $totalStock = $products->sum('stock_boxes');
        $totalValue = $products->sum(fn ($p) => $p->stock_boxes * $p->sale_price_box);

        $criticos = $inputs->filter(fn ($i) => $i->stock <= 0);
        $bajos = $inputs->filter(fn ($i) => $i->stock > 0 && $i->stock <= $i->safety_stock);

        $txt = "RESUMEN PATPOT - " . now()->format('d/m/Y H:i') . "\n\n";

        $txt .= "PRODUCTOS (" . $products->count() . " activos, {$totalStock} cajas, \$" . number_format($totalValue, 0, ',', '.') . " en stock):\n";
        foreach ($products as $p) {
            $estado = $p->stock_boxes < $p->min_stock_boxes ? ' ⚠ BAJO MÍNIMO' : '';
            $txt .= "• {$p->name}: {$p->stock_boxes} cajas (\${$p->sale_price_box}/caja){$estado}\n";
        }

        $txt .= "\nINSUMOS (" . $inputs->count() . " materiales): ";
        if ($criticos->isNotEmpty()) {
            $txt .= "{$criticos->count()} sin stock, ";
        }
        if ($bajos->isNotEmpty()) {
            $txt .= "{$bajos->count()} bajo mínimo, ";
        }
        $txt .= ($inputs->count() - $criticos->count() - $bajos->count()) . " OK\n";

        $txt .= "\nOPERACIONES:\n";
        $txt .= "• Pedidos pendientes: {$pendingOrders}\n";
        $txt .= "• Producciones en curso: {$pendingProductions}\n";
        $txt .= "• Quiebres retail: {$retailBreaks}\n";
        $txt .= "• Ventas del mes: \$" . number_format($salesMonth, 0, ',', '.') . "\n";

        return $txt;
    }

    private function stock(): string
    {
        $products = Product::where('status', 'active')->get();
        $inputs = Input::where('type', 'material')->where('status', true)->get();

        $txt = "STOCK DE PRODUCTOS:\n";
        foreach ($products as $p) {
            $min = $p->stock_boxes < $p->min_stock_boxes ? ' ⚠ BAJO' : '';
            $txt .= "- {$p->name} ({$p->sku}): {$p->stock_boxes} cajas (mínimo: {$p->min_stock_boxes}){$min}\n";
        }

        $txt .= "\nSTOCK DE INSUMOS:\n";
        foreach ($inputs as $i) {
            $nivel = $i->stock <= 0 ? '❌ SIN STOCK' : ($i->stock <= $i->safety_stock ? '⚠ BAJO' : '✅ OK');
            $txt .= "- {$i->name}: {$i->stock} {$i->unit} (seguridad: {$i->safety_stock}) {$nivel}\n";
        }

        return $txt;
    }

    private function pedidos(): string
    {
        $orders = Order::whereIn('status', ['pending', 'partial'])
            ->with(['customer', 'lines.product'])
            ->get();

        if ($orders->isEmpty()) {
            return "No hay pedidos pendientes. Todo al día.";
        }

        $txt = "PEDIDOS PENDIENTES ({$orders->count()}):\n";
        foreach ($orders as $o) {
            $lines = $o->lines->map(fn ($l) => "{$l->product->name}: {$l->boxes} cajas")->implode(', ');
            $atrasado = $o->delivery_on && $o->delivery_on->isPast() ? ' ⚠ ATRASADO' : '';
            $txt .= "- {$o->number} | {$o->customer->business_name} | {$lines} | entrega: {$o->delivery_on}{$atrasado}\n";
        }

        return $txt;
    }

    private function produccion(): string
    {
        $productions = Production::whereIn('status', ['planned', 'in_progress'])
            ->with('product')
            ->get();

        if ($productions->isEmpty()) {
            return "No hay producciones en curso.";
        }

        $txt = "PRODUCCIONES EN CURSO ({$productions->count()}):\n";
        foreach ($productions as $p) {
            $txt .= "- {$p->number} | {$p->product->name} | {$p->planned_boxes} cajas planificadas | {$p->status}\n";
        }

        return $txt;
    }

    private function retail(): string
    {
        $breaks = Retail::where('cataloged', true)
            ->with(['store.customer', 'product'])
            ->get()
            ->filter(fn ($r) => $r->is_break);

        if ($breaks->isEmpty()) {
            return "No hay quiebres en retail. Todo bien.";
        }

        $txt = "QUIEBRES RETAIL ({$breaks->count()}):\n";
        foreach ($breaks as $r) {
            $txt .= "- {$r->store->name} ({$r->store->customer->business_name}) | {$r->product->name} | stock: {$r->stock_units} u\n";
        }

        return $txt;
    }

    private function ventas(): string
    {
        $total = ShipmentLine::whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', now()->startOfMonth()->toDateString()))
            ->sum(DB::raw('price_box * boxes'));

        $lastMonth = ShipmentLine::whereHas('shipment', fn ($q) => $q->whereBetween('shipped_on', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]))
            ->sum(DB::raw('price_box * boxes'));

        $diff = $lastMonth > 0 ? round((($total - $lastMonth) / $lastMonth) * 100) : 0;
        $trend = $diff > 0 ? "+{$diff}%" : "{$diff}%";

        $txt = "VENTAS DEL MES:\n";
        $txt .= "- Este mes: $" . number_format($total, 0, ',', '.') . "\n";
        $txt .= "- Mes anterior: $" . number_format($lastMonth, 0, ',', '.') . "\n";
        $txt .= "- Variación: {$trend}\n";

        $byProduct = ShipmentLine::whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', now()->startOfMonth()->toDateString()))
            ->join('order_lines', 'shipment_lines.order_line_id', '=', 'order_lines.id')
            ->join('products', 'order_lines.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(shipment_lines.boxes) as total_boxes'), DB::raw('SUM(shipment_lines.price_box * shipment_lines.boxes) as total_sales'))
            ->groupBy('products.name')
            ->get();

        if ($byProduct->isNotEmpty()) {
            $txt .= "\nPOR PRODUCTO:\n";
            foreach ($byProduct as $row) {
                $txt .= "- {$row->name}: {$row->total_boxes} cajas, $" . number_format($row->total_sales, 0, ',', '.') . "\n";
            }
        }

        return $txt;
    }

    private function criticos(): string
    {
        $inputs = Input::where('type', 'material')->where('status', true)->get();
        $criticos = $inputs->filter(fn ($i) => $i->stock <= 0);
        $bajos = $inputs->filter(fn ($i) => $i->stock > 0 && $i->stock <= $i->safety_stock);

        if ($criticos->isEmpty() && $bajos->isEmpty()) {
            return "No hay alertas de stock. Todo en orden.";
        }

        $txt = "";
        if ($criticos->isNotEmpty()) {
            $txt .= "CRÍTICOS (sin stock):\n";
            foreach ($criticos as $i) {
                $txt .= "❌ {$i->name} ({$i->code}): 0 {$i->unit}\n";
            }
        }
        if ($bajos->isNotEmpty()) {
            $txt .= ($criticos->isNotEmpty() ? "\n" : "") . "BAJO MÍNIMO:\n";
            foreach ($bajos as $i) {
                $txt .= "⚠ {$i->name} ({$i->code}): {$i->stock} {$i->unit} (seguridad: {$i->safety_stock})\n";
            }
        }

        return $txt;
    }

    private function compras(): string
    {
        $purchases = \App\Models\Purchase::whereIn('status', ['pending', 'partial'])
            ->with(['supplier', 'lines.input'])
            ->get();

        if ($purchases->isEmpty()) {
            return "No hay compras pendientes.";
        }

        $txt = "COMPRAS PENDIENTES ({$purchases->count()}):\n";
        foreach ($purchases as $p) {
            $atrasado = $p->expected_on && $p->expected_on->isPast() ? ' ⚠ ATRASADO' : '';
            $txt .= "- {$p->number} | {$p->supplier->name} | esperado: {$p->expected_on}{$atrasado}\n";
        }

        return $txt;
    }

    private function clientes(): string
    {
        $customers = \App\Models\Customer::where('status', true)
            ->withCount('orders')
            ->get();

        $txt = "CLIENTES ACTIVOS:\n";
        foreach ($customers as $c) {
            $txt .= "• {$c->business_name} ({$c->code}) | pedidos: {$c->orders_count}\n";
        }

        return $txt;
    }

    private function sugerencias(): string
    {
        $inputs = Input::where('type', 'material')
            ->where('status', true)
            ->get();

        $txt = "SUGERENCIAS DE COMPRA:\n";
        $urgentes = collect();
        $preventivos = collect();

        foreach ($inputs as $i) {
            $semanasStock = $i->weekly_consumption > 0 ? $i->stock / $i->weekly_consumption : 999;
            $puntoReorden = $i->safety_stock + ($i->weekly_consumption * $i->lead_time_days / 7);
            $diasRestantes = $i->weekly_consumption > 0 ? ($i->stock - $i->safety_stock) / ($i->weekly_consumption / 7) : 999;

            if ($i->stock <= $i->safety_stock) {
                $urgentes->push("  ❌ {$i->name}: {$i->stock} {$i->unit} (safety: {$i->safety_stock}). ¡COMPRAR HOY!");
            } elseif ($diasRestantes <= $i->lead_time_days) {
                $urgentes->push("  ⚠ {$i->name}: {$i->stock} {$i->unit}. Comprar en " . round($diasRestantes) . " días (lead time: {$i->lead_time_days} días).");
            } else {
                $preventivos->push("  ✅ {$i->name}: {$i->stock} {$i->unit}. Compra sugerida en " . round($diasRestantes) . " días.");
            }
        }

        if ($urgentes->isNotEmpty()) {
            $txt .= "\nURGENTES:\n" . $urgentes->implode("\n") . "\n";
        }
        if ($preventivos->isNotEmpty()) {
            $txt .= "\nPREVENTIVOS:\n" . $preventivos->implode("\n") . "\n";
        }

        return $txt;
    }

    private function precios(): string
    {
        $prices = Price::with(['customer', 'product'])
            ->get()
            ->groupBy('customer_id');

        if ($prices->isEmpty()) {
            return "No hay precios configurados.";
        }

        $txt = "PRECIOS POR CLIENTE:\n";
        foreach ($prices as $customerId => $customerPrices) {
            $customer = $customerPrices->first()->customer;
            $txt .= "\n{$customer->business_name} ({$customer->code}):\n";
            foreach ($customerPrices as $p) {
                $oferta = $p->offer_price && $p->offer_until && $p->offer_until->isFuture()
                    ? " (oferta: \${$p->offer_price} hasta {$p->offer_until->format('d/m/Y')})"
                    : '';
                $txt .= "  - {$p->product->name}: \${$p->price_box}/caja{$oferta}\n";
            }
        }

        return $txt;
    }

    private function recetas(): string
    {
        $products = Product::where('status', 'active')->with(['recipes.input'])->get();

        $txt = "RECETAS POR PRODUCTO:\n";
        foreach ($products as $p) {
            $txt .= "\n{$p->name} ({$p->sku}):\n";
            if ($p->recipes->isEmpty()) {
                $txt .= "  Sin receta definida\n";
            } else {
                foreach ($p->recipes as $r) {
                    $tipo = $r->input->type === 'service' ? ' (servicio)' : '';
                    $costo = $r->qty_per_box * $r->input->unit_cost;
                    $costoFmt = number_format($costo, 0, ',', '.');
                    $txt .= "  - {$r->input->name}: {$r->qty_per_box} {$r->input->unit} (\${$costoFmt}){$tipo}\n";
                }
            }
        }

        return $txt;
    }
}
