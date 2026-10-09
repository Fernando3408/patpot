<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
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

        // ---- KPIs ----
        $salesMonth = Shipment::where('shipped_on', '>=', $startOfMonth)->sum('total');

        $marginMonth = ShipmentLine::query()
            ->whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', $startOfMonth))
            ->with('orderLine.product.recipes.input')
            ->get()
            ->sum(function (ShipmentLine $line) {
                $storedCost = (float) $line->cost_box + (float) $line->variable_cost_box;
                $cost = $storedCost > 0 ? $storedCost : ($line->orderLine?->product?->cost_per_box ?? 0);
                $revenue = $line->price_box * $line->boxes;

                return $revenue - ($cost * $line->boxes);
            });

        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();
        $pendingOrdersList = Order::whereIn('status', ['pending', 'partial'])
            ->with('customer', 'lines.product', 'store')
            ->orderBy('delivery_on')
            ->get();
        $overdueOrders = Order::whereIn('status', ['pending', 'partial'])
            ->where('delivery_on', '<', $now->toDateString())->count();
        $overdueOrdersList = Order::whereIn('status', ['pending', 'partial'])
            ->where('delivery_on', '<', $now->toDateString())
            ->with('customer', 'lines.product', 'store')
            ->orderBy('delivery_on')
            ->get();
        $overduePurchases = Purchase::where('status', '!=', 'received')
            ->where('expected_on', '<', $now->toDateString())->count();
        $overduePurchasesList = Purchase::where('status', '!=', 'received')
            ->where('expected_on', '<', $now->toDateString())
            ->with('supplier', 'lines.input')
            ->orderBy('expected_on')
            ->get();
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();
        $pendingProductionsList = Production::whereIn('status', ['planned', 'in_progress'])
            ->with('product')
            ->orderBy('planned_on')
            ->get();
        $allProductsWithRecipes = Product::with('recipes.input')->get();
        $stockPT = $allProductsWithRecipes
            ->sum(fn (Product $p) => $p->stock_boxes * $p->cost_per_box);
        $stockInputs = Input::sum(\DB::raw('stock * unit_cost'));
        $urgentTasks = Task::where('status', 'pending')
            ->where('priority', 'urgent')->count();
        $urgentTasksList = Task::where('status', 'pending')
            ->where('priority', 'urgent')
            ->orderBy('due_on')
            ->get();

        // Capacidad producible por producto
        $productionCapacities = $allProductsWithRecipes->map(fn ($p) => [
            'name' => $p->name,
            'capacity' => $p->production_capacity,
            'stock' => $p->stock_boxes,
            'limiting' => $p->recipes->isEmpty() ? 'Sin receta' : $p->recipes->map(fn ($r) => [
                'name' => $r->input?->name ?? '—',
                'available' => $r->input ? floor((float) $r->input->stock / (float) $r->qty_per_box) : 0,
            ])->sortBy('available')->first()['name'] ?? '—',
        ])->filter(fn ($p) => $p['capacity'] !== null)->sortBy('capacity')->values()->toArray();

        // Alertas
        $alerts = app(AlertService::class)->getAlerts();
        $criticalAlerts = $alerts->where('level', 'critical');
        $attentionAlerts = $alerts->where('level', 'warning');

        // ---- CHART DATA ----

        // 1. Venta del mes: últimos 6 meses (solo meses con datos)
        $salesMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $value = (float) Shipment::whereMonth('shipped_on', $date->month)
                ->whereYear('shipped_on', $date->year)
                ->sum('total');
            if ($value > 0) {
                $salesMonths->push(['label' => $date->format('M Y'), 'value' => $value]);
            }
        }

        // 2. Margen del mes: últimos 6 meses
        $marginMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $value = (float) ShipmentLine::query()
                ->whereHas('shipment', fn ($q) => $q->whereMonth('shipped_on', $date->month)
                    ->whereYear('shipped_on', $date->year))
                ->with('orderLine.product.recipes.input')
                ->get()
                ->sum(function (ShipmentLine $line): float {
                    $storedCost = (float) $line->cost_box + (float) $line->variable_cost_box;
                    $cost = $storedCost > 0 ? $storedCost : ($line->orderLine?->product?->cost_per_box ?? 0);

                    return $line->price_box * $line->boxes - ($cost * $line->boxes);
                });
            if ($value > 0) {
                $marginMonths->push(['label' => $date->format('M'), 'value' => $value]);
            }
        }

        // 3. Pedidos pendientes: por estado
        $chartOrderLabels = ['Pendiente', 'Parcial', 'Despachado'];
        $chartOrderCounts = [
            Order::where('status', 'pending')->count(),
            Order::where('status', 'partial')->count(),
            Order::where('status', 'completed')->count(),
        ];

        // 4. Pedidos atrasados: a tiempo vs atrasados
        $chartOverdueLabels = ['A tiempo', 'Atrasados'];
        $chartOverdueCounts = [
            Order::whereIn('status', ['pending', 'partial'])
                ->where('delivery_on', '>=', $now->toDateString())->count(),
            $overdueOrders,
        ];

        // 5. Compras atrasadas: por estado
        $chartPurchLabels = ['Pendiente', 'Parcial', 'Recibida', 'Atrasada'];
        $chartPurchCounts = [
            Purchase::where('status', 'pending')->count(),
            Purchase::where('status', 'partial')->count(),
            Purchase::where('status', 'received')->count(),
            $overduePurchases,
        ];

        // 6. Producciones: por estado
        $chartProdLabels = ['Planificada', 'En proceso', 'Cerrada'];
        $chartProdCounts = [
            Production::where('status', 'planned')->count(),
            Production::where('status', 'in_progress')->count(),
            Production::where('status', 'closed')->count(),
        ];

        // 7. Stock PT: por producto
        $chartPTLabels = $allProductsWithRecipes->pluck('name')->toArray();
        $chartPTValues = $allProductsWithRecipes->pluck('stock_boxes')->map(fn ($v) => (int) $v)->toArray();

        // 8. Stock insumos: datos para selector
        $allInputs = Input::select('id', 'name', 'unit', 'stock', 'safety_stock')->get();
        $chartInputsData = $allInputs->map(fn ($input) => [
            'id' => $input->id,
            'name' => $input->name,
            'unit' => $input->unit,
            'stock' => (float) $input->stock,
            'safety' => (float) $input->safety_stock,
        ])->values()->toArray();
        $stockAlerts = collect($allInputs->filter(fn ($input) => (float) $input->stock <= (float) $input->safety_stock)->map(fn ($input) => ['name' => $input->name, 'unit' => strtolower($input->unit) === 'unidad' && (float) $input->stock != 1.0 ? 'unidades' : $input->unit, 'stock' => (float) $input->stock, 'minimum' => (float) $input->safety_stock, 'level' => (float) $input->stock <= 0 ? 'red' : 'yellow'])->values()->all());
        $stockAlerts = $stockAlerts->merge(Product::where('status', 'active')->whereColumn('stock_boxes', '<=', 'min_stock_boxes')->get()->map(fn ($product) => ['name' => $product->name, 'unit' => 'cajas', 'stock' => (float) $product->stock_boxes, 'minimum' => (float) $product->min_stock_boxes, 'level' => (float) $product->stock_boxes <= 0 ? 'red' : 'yellow']))->values();

        // 9. Tareas urgentes: por prioridad
        $chartTaskLabels = ['Urgente', 'Alta', 'Media', 'Baja'];
        $chartTaskCounts = [
            Task::where('priority', 'urgent')->where('status', 'pending')->count(),
            Task::where('priority', 'high')->where('status', 'pending')->count(),
            Task::where('priority', 'medium')->where('status', 'pending')->count(),
            Task::where('priority', 'low')->where('status', 'pending')->count(),
        ];

        return view('welcome', compact(
            'salesMonth', 'marginMonth', 'pendingOrders', 'pendingOrdersList', 'overdueOrders', 'overdueOrdersList',
            'overduePurchases', 'overduePurchasesList', 'pendingProductions', 'pendingProductionsList', 'stockPT', 'stockInputs',
            'urgentTasks', 'urgentTasksList', 'alerts', 'criticalAlerts', 'attentionAlerts',
            'salesMonths', 'marginMonths',
            'chartOrderLabels', 'chartOrderCounts',
            'chartOverdueLabels', 'chartOverdueCounts',
            'chartPurchLabels', 'chartPurchCounts',
            'chartProdLabels', 'chartProdCounts',
            'chartPTLabels', 'chartPTValues',
            'chartInputsData', 'stockAlerts',
            'chartTaskLabels', 'chartTaskCounts',
            'productionCapacities',
        ));
    }
}
