<x-erp-layout title="Dashboard" subtitle="Lo que requiere una decisión, sin revisar hojas ni fórmulas.">

    {{-- KPIs principales --}}
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1.5rem;">
        <div class="metric-card">
            <p class="metric-title">Venta despachada del mes</p>
            <p class="metric-value">${{ number_format($salesMonth, 0, ',', '.') }}</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ $marginMonth >= 0 ? '#166534' : '#991b1b' }};">
            <p class="metric-title">Margen despachado del mes</p>
            <p class="metric-value">${{ number_format($marginMonth, 0, ',', '.') }}</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ $pendingOrders > 0 ? '#92400e' : '#166534' }};">
            <p class="metric-title">Pedidos pendientes</p>
            <p class="metric-value">{{ $pendingOrders }}</p>
            <p class="text-xs text-muted">Con cajas aún por despachar</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ $criticalInputs > 0 ? '#991b1b' : '#166534' }};">
            <p class="metric-title">Insumos críticos</p>
            <p class="metric-value">{{ $criticalInputs }}</p>
            <p class="text-xs text-muted">Riesgo de detener producción</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ $retailBreaks > 0 ? '#991b1b' : '#166534' }};">
            <p class="metric-title">Salas en quiebre</p>
            <p class="metric-value">{{ $retailBreaks }}</p>
            <p class="text-xs text-muted">Sin stock ni tránsito</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ ($overduePurchases + $overdueOrders) > 0 ? '#991b1b' : '#166534' }};">
            <p class="metric-title">Atrasados</p>
            <p class="metric-value">{{ $overduePurchases + $overdueOrders }}</p>
            <p class="text-xs text-muted">Compras + pedidos atrasados</p>
        </div>
        <div class="metric-card" style="border-left: 4px solid {{ $pendingProductions > 0 ? '#92400e' : '#166534' }};">
            <p class="metric-title">Producciones pendientes</p>
            <p class="metric-value">{{ $pendingProductions }}</p>
            <p class="text-xs text-muted">Planificadas o en proceso</p>
        </div>
        <div class="metric-card">
            <p class="metric-title">Stock PT valorizado</p>
            <p class="metric-value">${{ number_format($stockPT, 0, ',', '.') }}</p>
            <p class="text-xs text-muted">Costo actual de recetas</p>
        </div>
        <div class="metric-card">
            <p class="metric-title">Stock insumos valorizado</p>
            <p class="metric-value">${{ number_format($stockInputs, 0, ',', '.') }}</p>
            <p class="text-xs text-muted">Capital en MP y envases</p>
        </div>
        <div class="metric-card">
            <p class="metric-title">Tareas urgentes</p>
            <p class="metric-value">{{ $urgentTasks }}</p>
            <p class="text-xs text-muted">Pendientes con prioridad urgente</p>
        </div>
    </div>

    {{-- Alertas críticas --}}
    @if($criticalAlerts->count() > 0)
        <div class="card" style="max-width: 100%; margin-bottom: 1.5rem; border-left: 4px solid #991b1b;">
            <div class="card__header">
                <h2 class="card__title">Alertas críticas ({{ $criticalAlerts->count() }})</h2>
            </div>
            <div class="card__body">
                @foreach($criticalAlerts as $alert)
                    <div style="display: flex; justify-content: space-between; padding: 0.625rem 0; border-bottom: 1px solid #f3efe6;">
                        <div>
                            <strong>{{ $alert['title'] }}</strong>
                            <span class="badge badge-danger" style="margin-left: 0.5rem;">{{ $alert['module'] }}</span>
                            <br>
                            <span class="text-xs text-muted">{{ $alert['detail'] }}</span>
                        </div>
                        <a href="{{ $alert['action_url'] }}" class="btn btn-outline-info btn-sm" style="height: fit-content;">Ver</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Capacidad producible --}}
    <div class="card" style="max-width: 100%;">
        <div class="card__header">
            <h2 class="card__title">Capacidad producible</h2>
            <span class="text-xs text-muted">Según insumo limitante</span>
        </div>
        <div class="card__body">
            @forelse($productionCapacity as $item)
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3efe6;">
                    <div>
                        <strong>{{ $item['product']->name }}</strong>
                        <span class="text-xs text-muted"> · Stock PT: {{ $item['product']->stock_boxes }} cajas</span>
                    </div>
                    <span class="badge badge-info">{{ number_format($item['capacity'] ?? 0, 0) }} cajas</span>
                </div>
            @empty
                <div class="data-table-empty">
                    <p>No hay productos con recetas definidas.</p>
                </div>
            @endforelse
        </div>
    </div>

</x-erp-layout>
