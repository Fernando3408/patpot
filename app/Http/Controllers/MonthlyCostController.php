<?php

namespace App\Http\Controllers;

use App\Models\ExpenseOption;
use App\Models\MonthlyClosure;
use App\Models\MonthlyCost;
use App\Models\OrderExpense;
use App\Models\Shipment;
use App\Models\ShipmentLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyCostController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (! MonthlyClosure::where('month', $month)->exists()) {
            $existing = MonthlyCost::whereBetween('cost_on', [$month.'-01', date('Y-m-t', strtotime($month.'-01'))])->pluck('concept');
            MonthlyCost::where('recurring', true)->latest('cost_on')->get()->unique('concept')->whereNotIn('concept', $existing)->each(function (MonthlyCost $cost) use ($month): void {
                MonthlyCost::create($cost->only(['concept', 'category', 'amount', 'type', 'recurring', 'notes', 'user_id']) + ['cost_on' => $month.'-01']);
            });
        }
        $costs = MonthlyCost::with('attachments')->whereBetween('cost_on', [$month.'-01', date('Y-m-t', strtotime($month.'-01'))])->latest('cost_on')->get();

        $categories = ExpenseOption::where('type', 'category')->where('active', true)->orderBy('name')->pluck('name');
        if ($categories->isEmpty()) {
            $categories = collect(['Reposición', 'Personal', 'Servicios e internet', 'Arriendo', 'Marketing', 'Transporte general', 'Administración y contabilidad', 'Otros']);
        }

        return view('monthly-costs.index', compact('costs', 'month', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cost_on' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', 'in:fixed,variable'],
            'recurring' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,xls,xlsx,csv'],
        ]);
        $month = substr($data['cost_on'], 0, 7);
        if (MonthlyClosure::where('month', $month)->exists()) {
            return back()->withErrors(['cost_on' => 'El mes ya está cerrado y no admite modificaciones.']);
        }
        $data['user_id'] = auth()->id();
        $data['recurring'] = (bool) ($data['recurring'] ?? false);
        $cost = MonthlyCost::create($data);
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments');
            $cost->attachments()->create(['original_name' => $request->file('attachment')->getClientOriginalName(), 'stored_name' => basename($path), 'path' => $path, 'mime_type' => $request->file('attachment')->getMimeType(), 'size' => $request->file('attachment')->getSize(), 'user_id' => auth()->id()]);
        }

        return back()->with('success', 'Costo registrado correctamente.');
    }

    public function destroy(MonthlyCost $monthlyCost): RedirectResponse
    {
        if (MonthlyClosure::where('month', $monthlyCost->cost_on->format('Y-m'))->exists()) {
            return back()->withErrors(['cost' => 'El mes ya está cerrado.']);
        }
        $monthlyCost->delete();

        return back()->with('success', 'Costo eliminado correctamente.');
    }

    public function update(Request $request, MonthlyCost $monthlyCost): RedirectResponse
    {
        $data = $request->validate(['cost_on' => ['required', 'date'], 'concept' => ['required', 'string', 'max:255'], 'category' => ['required', 'string', 'max:100'], 'amount' => ['required', 'numeric', 'gt:0'], 'type' => ['required', 'in:fixed,variable'], 'recurring' => ['nullable', 'boolean'], 'notes' => ['nullable', 'string']]);
        if (MonthlyClosure::where('month', $monthlyCost->cost_on->format('Y-m'))->exists() || MonthlyClosure::where('month', substr($data['cost_on'], 0, 7))->exists()) {
            return back()->withErrors(['cost' => 'El mes está cerrado.']);
        }
        $data['recurring'] = (bool) ($data['recurring'] ?? false);
        $monthlyCost->update($data);

        return back()->with('success', 'Costo actualizado correctamente.');
    }

    public function close(Request $request): RedirectResponse
    {
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']])['month'];
        [$year, $monthNumber] = explode('-', $month);
        $from = $month.'-01';
        $to = date('Y-m-t', strtotime($from));
        $shipments = Shipment::whereBetween('shipped_on', [$from, $to])->get();
        $lines = ShipmentLine::with('orderLine')->whereHas('shipment', fn ($query) => $query->whereBetween('shipped_on', [$from, $to]))->get();
        $sales = (float) $shipments->sum('total');
        $productCost = (float) $lines->sum(fn ($line) => (float) $line->boxes * ((float) $line->cost_box + (float) $line->variable_cost_box));
        $expenses = (float) $shipments->sum(fn ($shipment) => (float) $shipment->freight_cost + (float) $shipment->management_cost + (float) $shipment->other_cost) + (float) OrderExpense::whereHas('order.shipments', fn ($query) => $query->whereBetween('shipped_on', [$from, $to]))->sum('amount');
        $monthlyCosts = (float) MonthlyCost::whereBetween('cost_on', [$from, $to])->sum('amount');
        $recurringConcepts = MonthlyCost::where('recurring', true)->pluck('concept')->unique();
        $monthConcepts = MonthlyCost::whereBetween('cost_on', [$from, $to])->pluck('concept');
        $missingRecurring = $recurringConcepts->diff($monthConcepts);
        if ($missingRecurring->isNotEmpty()) {
            return back()->withErrors(['month' => 'Faltan costos recurrentes por confirmar: '.$missingRecurring->implode(', ')]);
        }
        MonthlyClosure::firstOrCreate(['month' => $month], ['sales' => $sales, 'product_cost' => $productCost, 'expenses' => $expenses, 'monthly_costs' => $monthlyCosts, 'net_profit' => $sales - $productCost - $expenses - $monthlyCosts, 'closed_at' => now(), 'closed_by' => auth()->id()]);

        return back()->with('success', 'Mes cerrado correctamente.');
    }

    public function reopen(Request $request, string $month): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        MonthlyClosure::where('month', $month)->delete();

        return back()->with('success', 'Mes reabierto correctamente.');
    }
}
