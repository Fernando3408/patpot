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

        // ---- KPIs ----
        $salesMonth = Shipment::where('shipped_on', '>=', $startOfMonth)->sum('total');

        $marginMonth = ShipmentLine::query()
            ->whereHas('shipment', fn ($q) => $q->where('shipped_on', '>=', $startOfMonth))
            ->get()
            ->sum(function (ShipmentLine $line) {
                $cost = $line->orderLine?->product?->cost_per_box ?? 0;
                $revenue = $line->price_box * $line->boxes;
                return $revenue - ($cost * $line->boxes);
            });

        $pendingOrders = Order::whereIn('status', ['pending', 'partial'])->count();
        $overdueOrders = Order::whereIn('status', ['pending', 'partial'])
            ->where('delivery_on', '<', $now->toDateString())->count();
        $overduePurchases = Purchase::whereNotIn('status', ['received', 'cancelled'])
            ->where('expected_on', '<', $now->toDateString())->count();
        $pendingProductions = Production::whereIn('status', ['planned', 'in_progress'])->count();
        $stockPT = Product::with('recipes.input')->get()
            ->sum(fn (Product $p) => $p->stock_boxes * $p->cost_per_box);
        $stockInputs = Input::sum(\DB::raw('stock * unit_cost'));
        $urgentTasks = Task::where('status', 'pending')
            ->where('priority', 'urgent')->count();

        // Alertas
        $alerts = app(AlertService::class)->getAlerts();
        $criticalAlerts = $alerts->where('level', 'critical');

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
            $marginMonths->push([
                'label' => $date->format('M'),
                'value' => (float) ShipmentLine::query()
                    ->whereHas('shipment', fn ($q) => $q->whereMonth('shipped_on', $date->month)
                        ->whereYear('shipped_on', $date->year))
                    ->get()
                    ->sum(fn (ShipmentLine $line) => $line->price_box * $line->boxes
                        - (($line->orderLine?->product?->cost_per_box ?? 0) * $line->boxes)),
            ]);
        }

        // 3. Pedidos pendientes: por estado
        $chartOrderLabels = ['Pendiente', 'Parcial', 'Despachado'];
        $chartOrderCounts = [
            Order::where('status', 'pending')->count(),
            Order::where('status', 'partial')->count(),
            Order::where('status', 'dispatched')->count(),
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
        $chartProdLabels = ['Planificada', 'En proceso', 'Completada'];
        $chartProdCounts = [
            Production::where('status', 'planned')->count(),
            Production::where('status', 'in_progress')->count(),
            Production::where('status', 'completed')->count(),
        ];

        // 7. Stock PT: por producto
        $allProducts = Product::all();
        $chartPTLabels = $allProducts->pluck('name')->toArray();
        $chartPTValues = $allProducts->pluck('stock_boxes')->map(fn ($v) => (int) $v)->toArray();

        // 8. Stock insumos: datos para selector
        $allInputs = Input::select('id', 'name', 'unit', 'stock', 'safety_stock')->get();
        $chartInputsData = $allInputs->map(fn ($input) => [
            'id' => $input->id,
            'name' => $input->name,
            'unit' => $input->unit,
            'stock' => (float) $input->stock,
            'safety' => (float) $input->safety_stock,
        ])->values()->toArray();

        // 9. Tareas urgentes: por prioridad
        $chartTaskLabels = ['Urgente', 'Alta', 'Media', 'Baja'];
        $chartTaskCounts = [
            Task::where('priority', 'urgent')->where('status', 'pending')->count(),
            Task::where('priority', 'high')->where('status', 'pending')->count(),
            Task::where('priority', 'medium')->where('status', 'pending')->count(),
            Task::where('priority', 'low')->where('status', 'pending')->count(),
        ];

        return view('welcome', compact(
            'salesMonth', 'marginMonth', 'pendingOrders', 'overdueOrders',
            'overduePurchases', 'pendingProductions', 'stockPT', 'stockInputs',
            'urgentTasks', 'alerts', 'criticalAlerts',
            'salesMonths', 'marginMonths',
            'chartOrderLabels', 'chartOrderCounts',
            'chartOverdueLabels', 'chartOverdueCounts',
            'chartPurchLabels', 'chartPurchCounts',
            'chartProdLabels', 'chartProdCounts',
            'chartPTLabels', 'chartPTValues',
            'chartInputsData',
            'chartTaskLabels', 'chartTaskCounts',
        ));
    }
}
