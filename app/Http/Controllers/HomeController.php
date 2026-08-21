<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Retail;
use App\Models\Shipment;
use App\Models\ShipmentLine;
use App\Models\Task;
use App\Services\AlertService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();

        // Ventas del mes (shipments del mes)
        $salesMonth = Shipment::where('shipped_on', '>=', $startOfMonth)->sum('total');

        // Margen del mes
        $marginMonth = ShipmentLine::query()
            ->whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', $startOfMonth))
            ->get()
            ->sum(function (ShipmentLine $line) {
                $cost = $line->orderLine?->product?->cost_per_box ?? 0;
                $revenue = $line->price_box * $line->boxes;

                return $revenue - ($cost * $line->boxes);
            });

        // Pedidos pendientes
        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();

        // Insumos críticos
        $criticalInputs = Input::query()->with(['recipes.product.productions'])->get()
            ->filter(fn (Input $i) => $i->inventory_level === 'critico')->count();

        // Salas en quiebre
        $retailBreaks = Retail::query()->get()
            ->filter(fn (Retail $r) => $r->is_break)->count();

        // Producciones pendientes
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();

        // Compras atrasadas
        $overduePurchases = Purchase::whereNotIn('status', ['received', 'cancelled'])
            ->where('expected_on', '<', $now->toDateString())
            ->count();

        // Pedidos atrasados
        $overdueOrders = Order::whereIn('status', ['pending', 'partial'])
            ->where('delivery_on', '<', $now->toDateString())
            ->count();

        // Stock PT valorizado
        $stockPT = Product::with('recipes.input')->get()
            ->sum(fn (Product $p) => $p->stock_boxes * $p->cost_per_box);

        // Stock insumos valorizado
        $stockInputs = Input::sum(\DB::raw('stock * unit_cost'));

        // Tareas urgentes pendientes
        $urgentTasks = Task::where('status', 'pending')
            ->where('priority', 'urgent')
            ->count();

        // Capacidad producible por producto
        $productionCapacity = Product::with('recipes.input')->get()
            ->map(fn (Product $p) => [
                'product' => $p,
                'capacity' => $p->production_capacity,
            ]);

        // Alertas
        $alerts = app(AlertService::class)->getAlerts();
        $criticalAlerts = $alerts->where('level', 'critical');

        return view('welcome', compact(
            'salesMonth', 'marginMonth', 'pendingOrders', 'criticalInputs',
            'retailBreaks', 'pendingProductions', 'overduePurchases', 'overdueOrders',
            'stockPT', 'stockInputs', 'urgentTasks', 'productionCapacity',
            'alerts', 'criticalAlerts'
        ));
    }
}
