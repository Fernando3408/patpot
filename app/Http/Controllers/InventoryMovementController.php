<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryMovement::with(['input', 'product', 'user']);

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to.' 23:59:59');
        }
        if ($request->filled('kind')) {
            $query->where('kind', $request->kind);
        }

        $movements = $query->latest()->get();

        return view('inventory-movements.index', compact('movements'));
    }
}
