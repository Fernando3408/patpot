<x-erp-layout title="Resultados" subtitle="Ventas, costos y utilidad del período seleccionado.">
    <h2 class="results-print-title">PatPot — Resultados {{ $month }}</h2>
    <div class="results-toolbar results-reference-toolbar">
        <form method="GET" action="{{ route('results.index') }}" class="search-form">
            <input type="month" name="month" class="form-control" value="{{ $month }}">
            <input type="date" name="day" class="form-control" value="{{ $day }}" title="Opcional: consultar un día">
            <select name="customer_id" class="form-control"><option value="">Todos los clientes</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected($customerId === $customer->id)>{{ $customer->trade_name ?: $customer->business_name }}</option>@endforeach</select>
            <select name="product_id" class="form-control"><option value="">Todos los productos</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->name }}</option>@endforeach</select>
            <button type="submit" class="btn btn-outline-success btn-sm">Consultar</button>
            <a href="{{ route('results.pdf', ['month' => $month, 'day' => $day, 'customer_id' => $customerId, 'product_id' => $productId]) }}" class="btn btn-outline-primary btn-sm">Descargar PDF</a>
            <a href="{{ route('results.export', ['month' => $month, 'day' => $day, 'customer_id' => $customerId, 'product_id' => $productId]) }}" class="btn btn-outline-primary btn-sm">Exportar CSV</a>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">Imprimir / PDF</button>
        </form>
    </div>

    <div class="results-section-heading"><span>Resumen financiero</span><small>Período {{ $month }}</small></div>
    <div class="kpi-grid results-reference-kpis">
        <div class="kpi-card kpi-card--sales"><span>Ventas sin IVA</span><strong>${{ number_format($sales, 0, ',', '.') }}</strong><small>Con IVA: ${{ number_format($sales * 1.19, 0, ',', '.') }}</small></div>
        <div class="kpi-card"><span>Costo productos sin IVA</span><strong>${{ number_format($productCost, 0, ',', '.') }}</strong><small>Con IVA: ${{ number_format($productCost * 1.19, 0, ',', '.') }}</small></div>
        <div class="kpi-card"><span>Gastos sin IVA</span><strong>${{ number_format($expenses, 0, ',', '.') }}</strong><small>Con IVA: ${{ number_format($expenses * 1.19, 0, ',', '.') }}</small></div>
        <div class="kpi-card kpi-card--profit"><span>Utilidad bruta</span><strong>${{ number_format($grossProfit, 0, ',', '.') }}</strong><small>Margen {{ number_format($margin, 1, ',', '.') }}%</small></div>
        <div class="kpi-card"><span>Costos del mes</span><strong>${{ number_format($monthlyCosts, 0, ',', '.') }}</strong><small>Costos fijos y variables</small></div>
        <div class="kpi-card kpi-card--net"><span>Utilidad neta</span><strong>${{ number_format($netProfit, 0, ',', '.') }}</strong><small>Margen {{ number_format($netMargin, 1, ',', '.') }}%</small></div>
    </div>
</x-erp-layout>
