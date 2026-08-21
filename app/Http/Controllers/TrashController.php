<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\Product;
use App\Models\Retail;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $deletedProducts = Product::onlyTrashed()->with('deleter')->get();
        $deletedInputs = Input::onlyTrashed()->with('deleter')->get();
        $deletedCustomers = Customer::onlyTrashed()->with('deleter')->get();
        $deletedSuppliers = Supplier::onlyTrashed()->with('deleter')->get();
        $deletedStores = Store::onlyTrashed()->with('deleter')->get();
        $deletedRetails = Retail::onlyTrashed()->with('deleter')->get();

        if ($request->filled('search')) {
            $search = $request->search;
            $deletedProducts = $deletedProducts->filter(fn ($item) => str_contains($item->name, $search));
            $deletedInputs = $deletedInputs->filter(fn ($item) => str_contains($item->name, $search));
            $deletedCustomers = $deletedCustomers->filter(fn ($item) => str_contains($item->business_name, $search));
            $deletedSuppliers = $deletedSuppliers->filter(fn ($item) => str_contains($item->name, $search));
            $deletedStores = $deletedStores->filter(fn ($item) => str_contains($item->code, $search));
            $deletedRetails = $deletedRetails->filter(fn ($item) => str_contains($item->name, $search));
        }

        return view('admin.trash.index', compact(
            'deletedProducts', 'deletedInputs', 'deletedCustomers',
            'deletedSuppliers', 'deletedStores', 'deletedRetails'
        ));
    }

    public function restore(Request $request)
    {
        $model = $this->getModel($request->model);
        $item = $model::withTrashed()->findOrFail($request->id);
        $item->update(['deleted_by' => null, 'status' => $this->activeStatus($model)]);
        $item->restore();
        AuditService::log('RESTAURACIÓN', 'Restauró registro: '.($item->name ?? $item->business_name ?? $item->number ?? 'Registro'), $item);
        $this->cascadeRestore($item);

        return back()->with('success', 'Registro restaurado.');
    }

    public function forceDelete(Request $request)
    {
        $model = $this->getModel($request->model);
        $item = $model::withTrashed()->findOrFail($request->id);
        AuditService::log('ELIMINACIÓN PERMANENTE', 'Eliminó permanentemente: '.($item->name ?? $item->business_name ?? $item->number ?? 'Registro'), $item);
        $this->cascadeForceDelete($item);

        return back()->with('success', 'Registro eliminado permanentemente.');
    }

    public function restoreMultiple(Request $request)
    {
        $model = $this->getModel($request->model);
        $items = $model::withTrashed()->whereIn('id', $request->ids)->get();
        $items->each->update(['deleted_by' => null, 'status' => $this->activeStatus($model)]);
        $items->each->restore();
        AuditService::log('RESTAURACIÓN', "Restauró {$items->count()} registros del modelo: {$request->model}");
        $items->each(fn ($item) => $this->cascadeRestore($item));

        return back()->with('success', 'Registros restaurados.');
    }

    public function forceDeleteMultiple(Request $request)
    {
        $model = $this->getModel($request->model);
        $items = $model::withTrashed()->whereIn('id', $request->ids)->get();

        AuditService::log('ELIMINACIÓN PERMANENTE', "Eliminó permanentemente {$items->count()} registros del modelo: {$request->model}");
        foreach ($items as $item) {
            $this->cascadeForceDelete($item);
        }

        return back()->with('success', 'Registros eliminados permanentemente.');
    }

    private function activeStatus(string $model): mixed
    {
        return $model === Product::class ? 'active' : true;
    }

    private function cascadeForceDelete($item): void
    {
        $class = get_class($item);

        if ($class === Customer::class) {
            $item->stores()->withTrashed()->each(function ($store) {
                $this->cascadeForceDelete($store);
            });
            $item->prices()->withTrashed()->forceDelete();
            $item->orders()->withTrashed()->each(function ($order) {
                $this->cascadeForceDelete($order);
            });
        } elseif ($class === Store::class) {
            $item->retail()->withTrashed()->forceDelete();
            $item->orders()->withTrashed()->each(function ($order) {
                $this->cascadeForceDelete($order);
            });
        } elseif ($class === Order::class) {
            $item->lines()->withTrashed()->each(function ($line) {
                $line->shipmentLines()->withTrashed()->forceDelete();
                $line->forceDelete();
            });
            $item->shipments()->withTrashed()->each(function ($shipment) {
                $shipment->lines()->withTrashed()->forceDelete();
                $shipment->forceDelete();
            });
        } elseif ($class === Product::class) {
            $item->productions()->withTrashed()->forceDelete();
            $item->prices()->withTrashed()->forceDelete();
            $item->retail()->withTrashed()->forceDelete();
            $item->recipes()->withTrashed()->forceDelete();
            $item->orderLines()->withTrashed()->each(function ($line) {
                $line->shipmentLines()->withTrashed()->forceDelete();
                $line->forceDelete();
            });
        } elseif ($class === Supplier::class) {
            $item->purchases()->withTrashed()->each(function ($purchase) {
                $purchase->lines()->withTrashed()->forceDelete();
                $purchase->forceDelete();
            });
            $item->inputs()->withTrashed()->each(function ($input) {
                $this->cascadeForceDelete($input);
            });
        } elseif ($class === Input::class) {
            $item->recipes()->withTrashed()->forceDelete();
            $item->purchaseLines()->withTrashed()->forceDelete();
        }

        $item->forceDelete();
    }

    private function cascadeRestore($item): void
    {
        $class = get_class($item);

        if ($class === Input::class) {
            $item->recipes()->withTrashed()
                ->whereHas('product')
                ->restore();
        } elseif ($class === Product::class) {
            $item->recipes()->withTrashed()->restore();
            $item->productions()->withTrashed()->restore();
            $item->prices()->withTrashed()->restore();
            $item->retail()->withTrashed()->restore();
            $item->orderLines()->withTrashed()->restore();
        } elseif ($class === Customer::class) {
            $item->stores()->withTrashed()->each(function ($store) {
                $store->restore();
                $store->retail()->withTrashed()->restore();
            });
            $item->orders()->withTrashed()->each(function ($order) {
                $order->restore();
                $order->lines()->withTrashed()->restore();
                $order->shipments()->withTrashed()->restore();
            });
            $item->prices()->withTrashed()->restore();
        } elseif ($class === Supplier::class) {
            $item->inputs()->withTrashed()->each(function ($input) {
                $input->restore();
                $input->recipes()->withTrashed()->restore();
            });
            $item->purchases()->withTrashed()->restore();
        } elseif ($class === Store::class) {
            $item->retail()->withTrashed()->restore();
            $item->orders()->withTrashed()->each(function ($order) {
                $order->restore();
                $order->lines()->withTrashed()->restore();
                $order->shipments()->withTrashed()->restore();
            });
        }
    }

    private function getModel(string $model): string
    {
        $map = [
            'product' => Product::class,
            'input' => Input::class,
            'customer' => Customer::class,
            'supplier' => Supplier::class,
            'store' => Store::class,
            'retail' => Retail::class,
        ];

        return $map[$model] ?? abort(404);
    }
}
