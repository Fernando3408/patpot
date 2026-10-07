<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MonthlyClosure;
use App\Models\MonthlyCost;
use App\Models\OrderExpense;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultsController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        $day = $request->input('day');
        $customerId = $request->integer('customer_id') ?: null;
        $productId = $request->integer('product_id') ?: null;
        [$year, $monthNumber] = array_pad(explode('-', $month), 2, now()->format('m'));
        $from = sprintf('%04d-%02d-01', (int) $year, (int) $monthNumber);
        $to = date('Y-m-t', strtotime($from));
        if ($day && checkdate((int) substr($day, 5, 2), (int) substr($day, 8, 2), (int) substr($day, 0, 4))) {
            $from = $day;
            $to = $day;
        }

        $shipments = Shipment::whereBetween('shipped_on', [$from, $to])->when($customerId, fn ($q) => $q->whereHas('order', fn ($order) => $order->where('customer_id', $customerId)))->get();
        $lines = ShipmentLine::with('orderLine.product')->whereHas('shipment', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->when($customerId, fn ($q) => $q->whereHas('orderLine.order', fn ($order) => $order->where('customer_id', $customerId)))->when($productId, fn ($q) => $q->whereHas('orderLine', fn ($line) => $line->where('product_id', $productId)))->get();
        $sales = (float) $shipments->sum('total');
        $productCost = (float) $lines->sum(fn ($line) => (float) $line->boxes * ((float) $line->cost_box + (float) $line->variable_cost_box));
        $shipmentExpenses = (float) $shipments->sum(fn ($shipment) => (float) $shipment->freight_cost + (float) $shipment->management_cost + (float) $shipment->other_cost);
        $orderExpenses = (float) OrderExpense::whereHas('order.shipments', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->when($customerId, fn ($q) => $q->whereHas('order', fn ($order) => $order->where('customer_id', $customerId)))->when($productId, fn ($q) => $q->whereHas('order.lines', fn ($line) => $line->where('product_id', $productId)))->sum('amount');
        $expenses = $shipmentExpenses + $orderExpenses;
        $grossProfit = $sales - $productCost - $expenses;
        $margin = $sales > 0 ? round(($grossProfit / $sales) * 100, 1) : 0;
        $monthlyCosts = (float) MonthlyCost::whereBetween('cost_on', [$from, $to])->sum('amount');
        $closure = MonthlyClosure::where('month', $month)->first();
        if ($closure) {
            $sales = (float) $closure->sales;
            $productCost = (float) $closure->product_cost;
            $expenses = (float) $closure->expenses;
            $monthlyCosts = (float) $closure->monthly_costs;
        }
        $netProfit = $grossProfit - $monthlyCosts;
        $netMargin = $sales > 0 ? round(($netProfit / $sales) * 100, 1) : 0;

        return view('results.index', ['month' => $month, 'day' => $day, 'customerId' => $customerId, 'productId' => $productId, 'customers' => Customer::orderBy('business_name')->get(), 'products' => Product::where('status', 'active')->orderBy('name')->get(), 'sales' => $sales, 'productCost' => $productCost, 'expenses' => $expenses, 'grossProfit' => $grossProfit, 'margin' => $margin, 'monthlyCosts' => $monthlyCosts, 'netProfit' => $netProfit, 'netMargin' => $netMargin]);
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNumber] = array_pad(explode('-', $month), 2, now()->format('m'));
        $from = sprintf('%04d-%02d-01', (int) $year, (int) $monthNumber);
        $to = date('Y-m-t', strtotime($from));
        $shipments = Shipment::with(['order.customer', 'lines.orderLine.product'])->whereBetween('shipped_on', [$from, $to])->get();
        $sales = (float) $shipments->sum('total');
        $lines = ShipmentLine::with('orderLine')->whereHas('shipment', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->get();
        $productCost = (float) $lines->sum(fn ($line) => (float) $line->boxes * ((float) $line->cost_box + (float) $line->variable_cost_box));
        $expenses = (float) $shipments->sum(fn ($shipment) => (float) $shipment->freight_cost + (float) $shipment->management_cost + (float) $shipment->other_cost);
        $expenses += (float) OrderExpense::whereHas('order.shipments', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->sum('amount');
        $closure = MonthlyClosure::where('month', $month)->first();
        $monthlyCosts = (float) MonthlyCost::whereBetween('cost_on', [$from, $to])->sum('amount');
        $filename = 'PatPot_Resultados_'.$month.'.csv';

        return response()->streamDownload(function () use ($month, $sales, $productCost, $expenses, $closure, $monthlyCosts, $shipments): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            $sales = (float) ($closure?->sales ?? $sales);
            fputcsv($handle, ['Período', 'Ventas', 'Costo productos', 'Gastos asociados', 'Costos del mes', 'Utilidad neta'], ';');
            $productCost = (float) ($closure?->product_cost ?? $productCost);
            $expenses = (float) ($closure?->expenses ?? $expenses);
            $monthlyCosts = (float) ($closure?->monthly_costs ?? $monthlyCosts);
            fputcsv($handle, [$month, number_format($sales, 0, ',', '.'), number_format($productCost, 0, ',', '.'), number_format($expenses, 0, ',', '.'), number_format($monthlyCosts, 0, ',', '.'), number_format($sales - $productCost - $expenses - $monthlyCosts, 0, ',', '.')], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Pedido', 'Cliente', 'Producto', 'Cajas', 'Venta'], ';');
            foreach ($shipments as $shipment) foreach ($shipment->lines as $line) fputcsv($handle, [$shipment->order?->number, $shipment->order?->customer?->business_name, $line->orderLine?->product?->name, $line->boxes, number_format((float) $line->boxes * (float) ($line->orderLine?->price_box ?? 0), 0, ',', '.')], ';');
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request): Response
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNumber] = array_pad(explode('-', $month), 2, now()->format('m'));
        $from = sprintf('%04d-%02d-01', (int) $year, (int) $monthNumber);
        $to = date('Y-m-t', strtotime($from));
        $shipments = Shipment::with(['order.customer', 'lines.orderLine.product'])->whereBetween('shipped_on', [$from, $to])->get();
        $lines = ShipmentLine::with('orderLine.product')->whereHas('shipment', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->get();
        $sales = (float) $shipments->sum('total');
        $productCost = (float) $lines->sum(fn ($line) => (float) $line->boxes * ((float) $line->cost_box + (float) $line->variable_cost_box));
        $expenses = (float) $shipments->sum(fn ($shipment) => (float) $shipment->freight_cost + (float) $shipment->management_cost + (float) $shipment->other_cost) + (float) OrderExpense::whereHas('order.shipments', fn ($q) => $q->whereBetween('shipped_on', [$from, $to]))->sum('amount');
        $monthlyCosts = (float) MonthlyCost::whereBetween('cost_on', [$from, $to])->sum('amount');
        $closure = MonthlyClosure::where('month', $month)->first();
        if ($closure) {
            $sales = (float) $closure->sales;
            $productCost = (float) $closure->product_cost;
            $expenses = (float) $closure->expenses;
            $monthlyCosts = (float) $closure->monthly_costs;
        }

        return Pdf::loadView('results.pdf', compact('month', 'sales', 'productCost', 'expenses', 'monthlyCosts', 'shipments'))->download('PatPot_Resultados_'.$month.'.pdf');
    }
}
