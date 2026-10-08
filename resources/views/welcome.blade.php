<x-erp-layout title="Dashboard" subtitle="Lo que requiere una decisión, sin revisar hojas ni fórmulas.">

    <div class="dash-fit">

        {{-- BLOQUE 1: ACCIONES URGENTES --}}
        <div class="dash-urgent-grid">
            <div class="dash-urgent-card {{ $pendingOrders > 0 ? 'dash-urgent--critical' : '' }}" onclick="openPendingOrdersModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $pendingOrders > 0 ? 'text-urgent' : 'text-ok' }}">{{ $pendingOrders }}</span>
                <span class="dash-urgent-label">Nuevos pedidos</span>
            </div>
            <div class="dash-urgent-card {{ $overdueOrders > 0 ? 'dash-urgent--critical' : '' }}" onclick="openOverdueOrdersModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $overdueOrders > 0 ? 'text-urgent' : 'text-ok' }}">{{ $overdueOrders }}</span>
                <span class="dash-urgent-label">Pedidos atrasados</span>
            </div>
            <div class="dash-urgent-card {{ $overduePurchases > 0 ? 'dash-urgent--critical' : '' }}" onclick="openOverduePurchasesModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $overduePurchases > 0 ? 'text-urgent' : 'text-ok' }}">{{ $overduePurchases }}</span>
                <span class="dash-urgent-label">Compras atrasadas</span>
            </div>
            <div class="dash-urgent-card {{ $pendingProductions > 0 ? 'dash-urgent--warn' : '' }}" onclick="openPendingProductionsModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $pendingProductions > 0 ? 'text-warning-color' : 'text-ok' }}">{{ $pendingProductions }}</span>
                <span class="dash-urgent-label">Producciones pendientes</span>
            </div>
            <div class="dash-urgent-card {{ $urgentTasks > 0 ? 'dash-urgent--critical' : '' }}" onclick="openUrgentTasksModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $urgentTasks > 0 ? 'text-urgent' : 'text-ok' }}">{{ $urgentTasks }}</span>
                <span class="dash-urgent-label">Tareas urgentes</span>
            </div>
            <div class="dash-urgent-card {{ $criticalAlerts->count() > 0 ? 'dash-urgent--critical' : '' }}" onclick="openCriticalAlertsModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $criticalAlerts->count() > 0 ? 'text-urgent' : 'text-ok' }}">{{ $criticalAlerts->count() }}</span>
                <span class="dash-urgent-label">Alertas críticas</span>
            </div>
            <div class="dash-urgent-card {{ $attentionAlerts->count() > 0 ? 'dash-urgent--warn' : '' }}" onclick="openAttentionAlertsModal()" style="cursor:pointer;">
                <span class="dash-urgent-count {{ $attentionAlerts->count() > 0 ? 'text-warning-color' : 'text-ok' }}">{{ $attentionAlerts->count() }}</span>
                <span class="dash-urgent-label">Alertas de atención</span>
            </div>
        </div>

        {{-- BLOQUE 2: KPIs DE NEGOCIO --}}
        <div class="dash-kpi-grid">
            <div class="dash-kpi-card kpi-clickable" onclick="openSalesModal()">
                <p class="dash-kpi-title">Venta del mes <span class="kpi-link-detail">&#9656; ver detalle</span></p>
                <p class="dash-kpi-value">${{ number_format($salesMonth, 0, ',', '.') }}</p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Margen del mes</p>
                <p class="dash-kpi-value {{ $marginMonth >= 0 ? 'text-positive' : 'text-negative' }}">
                    ${{ number_format($marginMonth, 0, ',', '.') }}
                </p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Stock producto terminado</p>
                <p class="dash-kpi-value">${{ number_format($stockPT, 0, ',', '.') }}</p>
                <p class="dash-kpi-hint">Costo de recetas</p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Stock insumos</p>
                <p class="dash-kpi-value">${{ number_format($stockInputs, 0, ',', '.') }}</p>
                <p class="dash-kpi-hint">Materias primas y envases</p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Inventario total</p>
                <p class="dash-kpi-value">${{ number_format($stockPT + $stockInputs, 0, ',', '.') }}</p>
                <p class="dash-kpi-hint">PT + insumos</p>
            </div>
            <div class="dash-kpi-card kpi-clickable" onclick="openCapacityModal()" style="cursor:pointer;">
                <p class="dash-kpi-title">Capacidad producible <span class="kpi-link-detail">&#9656; ver detalle</span></p>
                <p class="dash-kpi-value">{{ count($productionCapacities) }} productos</p>
            </div>
        </div>

        {{-- BLOQUE 3: OPERACION Y ANALISIS --}}
        <div class="dash-bottom-grid">

            <div class="dash-charts-panel">

            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <h3>Alertas de stock</h3>
                    <span class="text-xs text-muted">Stock actual versus mínimo definido</span>
                </div>
                <div class="dash-chart-body" style="padding:1rem;">
                    @forelse($stockAlerts as $alert)
                        <div class="stock-alert-list-row stock-alert-{{ $alert['level'] }}"><strong>{{ $alert['name'] }}</strong><span>{{ number_format($alert['stock'], 0, ',', '.') }} {{ $alert['unit'] }}</span><span>Mínimo: {{ number_format($alert['minimum'], 0, ',', '.') }} {{ $alert['unit'] }}</span><b>{{ $alert['level'] === 'red' ? 'Sin stock' : 'Bajo mínimo' }}</b></div>
                    @empty
                        <div class="data-table-empty"><p>No hay insumos ni productos bajo el mínimo.</p></div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <div id="salesModal" class="sales-modal-overlay" onclick="if(event.target===this)closeSalesModal()">
        <div class="sales-modal-content">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Ventas últimos 6 meses</h3>
                <button onclick="closeSalesModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area">
                <canvas id="chartSalesModal"></canvas>
            </div>
        </div>
    </div>

    <div id="pendingOrdersModal" class="sales-modal-overlay" onclick="if(event.target===this)closePendingOrdersModal()">
        <div class="sales-modal-content" style="max-width:900px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Nuevos pedidos ({{ $pendingOrdersList->count() }})</h3>
                <button onclick="closePendingOrdersModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($pendingOrdersList->count() > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Sala</th>
                            <th>Fecha entrega</th>
                            <th>Estado</th>
                            <th class="text-right">Cajas</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingOrdersList as $o)
                            <tr>
                                <td class="font-bold">{{ $o->number }}</td>
                                <td>{{ $o->customer?->business_name ?? '—' }}</td>
                                <td>{{ $o->store?->name ?? '—' }}</td>
                                <td>{{ $o->delivery_on?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match($o->status) { 'partial' => 'badge-warning', default => 'badge-info' };
                                        $label = match($o->status) { 'partial' => 'Parcial', default => 'Pendiente' };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="text-right">{{ $o->lines->sum('boxes') }}</td>
                                <td class="text-right font-bold">${{ number_format($o->lines->sum(fn($l) => $l->boxes * ($l->price_box ?? 0)), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>No hay pedidos pendientes.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="attentionAlertsModal" class="sales-modal-overlay" onclick="if(event.target===this)closeAttentionAlertsModal()"><div class="sales-modal-content" style="max-width:800px;"><div class="sales-modal-header"><h3 class="sales-modal-title">Alertas de atención ({{ $attentionAlerts->count() }})</h3><button onclick="closeAttentionAlertsModal()" class="sales-modal-close">&times;</button></div><div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">@forelse($attentionAlerts as $alert)<div class="dash-alert-row"><div class="dash-alert-info"><strong>{{ $alert['title'] }}</strong><span class="dash-alert-module">{{ $alert['module'] }}</span><p class="dash-alert-detail">{{ $alert['detail'] }}</p></div><a href="{{ $alert['action_url'] }}" class="btn btn-outline-warning btn-sm">Ver</a></div>@empty<div class="data-table-empty"><p>No hay alertas de atención.</p></div>@endforelse</div></div></div>
    <div id="criticalAlertsModal" class="sales-modal-overlay" onclick="if(event.target===this)closeCriticalAlertsModal()">
        <div class="sales-modal-content" style="max-width:800px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Alertas críticas ({{ $criticalAlerts->count() }})</h3>
                <button onclick="closeCriticalAlertsModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($criticalAlerts->count() > 0)
                <div style="padding:0.75rem;">
                    @foreach($criticalAlerts as $alert)
                        <div class="dash-alert-row">
                            <div class="dash-alert-info">
                                <strong>{{ $alert['title'] }}</strong>
                                <span class="dash-alert-module">{{ $alert['module'] }}</span>
                                <p class="dash-alert-detail">{{ $alert['detail'] }}</p>
                            </div>
                            <a href="{{ $alert['action_url'] }}" class="btn btn-outline-info btn-sm">Ver</a>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="data-table-empty"><p>Sin alertas críticas.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="capacityModal" class="sales-modal-overlay" onclick="if(event.target===this)closeCapacityModal()">
        <div class="sales-modal-content" style="max-width:700px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Capacidad producible</h3>
                <button onclick="closeCapacityModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if(count($productionCapacities) > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Insumo limitante</th>
                            <th class="text-right">Stock PT</th>
                            <th class="text-right">Capacidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productionCapacities as $cap)
                            <tr>
                                <td class="font-bold">{{ $cap['name'] }}</td>
                                <td>{{ $cap['limiting'] }}</td>
                                <td class="text-right">{{ number_format($cap['stock'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold">{{ number_format($cap['capacity'], 0, ',', '.') }} cajas</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>Sin datos de producción.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="overdueOrdersModal" class="sales-modal-overlay" onclick="if(event.target===this)closeOverdueOrdersModal()">
        <div class="sales-modal-content" style="max-width:900px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Pedidos atrasados ({{ $overdueOrdersList->count() }})</h3>
                <button onclick="closeOverdueOrdersModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($overdueOrdersList->count() > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Sala</th>
                            <th>Fecha entrega</th>
                            <th class="text-right">Cajas</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueOrdersList as $o)
                            <tr>
                                <td class="font-bold">{{ $o->number }}</td>
                                <td>{{ $o->customer?->business_name ?? '—' }}</td>
                                <td>{{ $o->store?->name ?? '—' }}</td>
                                <td class="text-negative">{{ $o->delivery_on?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-right">{{ $o->lines->sum('boxes') }}</td>
                                <td class="text-right font-bold">${{ number_format($o->lines->sum(fn($l) => $l->boxes * ($l->price_box ?? 0)), 0, ',', '.') }}</td>
                                <td><a href="{{ route('pedidos.index') }}" class="btn btn-outline-info btn-sm">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>No hay pedidos atrasados.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="overduePurchasesModal" class="sales-modal-overlay" onclick="if(event.target===this)closeOverduePurchasesModal()">
        <div class="sales-modal-content" style="max-width:900px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Compras atrasadas ({{ $overduePurchasesList->count() }})</h3>
                <button onclick="closeOverduePurchasesModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($overduePurchasesList->count() > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Compra</th>
                            <th>Proveedor</th>
                            <th>Fecha esperada</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overduePurchasesList as $p)
                            <tr>
                                <td class="font-bold">{{ $p->number }}</td>
                                <td>{{ $p->supplier?->name ?? '—' }}</td>
                                <td class="text-negative">{{ $p->expected_on?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match($p->status) { 'partial' => 'badge-warning', default => 'badge-info' };
                                        $label = match($p->status) { 'partial' => 'Parcial', default => 'Pendiente' };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td><a href="{{ route('compras.index') }}" class="btn btn-outline-info btn-sm">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>No hay compras atrasadas.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="pendingProductionsModal" class="sales-modal-overlay" onclick="if(event.target===this)closePendingProductionsModal()">
        <div class="sales-modal-content" style="max-width:800px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Producciones pendientes ({{ $pendingProductionsList->count() }})</h3>
                <button onclick="closePendingProductionsModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($pendingProductionsList->count() > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Producto</th>
                            <th>Fecha planificada</th>
                            <th>Estado</th>
                            <th class="text-right">Cajas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProductionsList as $pr)
                            <tr>
                                <td class="font-bold">{{ $pr->number }}</td>
                                <td>{{ $pr->product?->name ?? '—' }}</td>
                                <td>{{ $pr->planned_on?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match($pr->status) { 'in_progress' => 'badge-warning', default => 'badge-info' };
                                        $label = match($pr->status) { 'in_progress' => 'En proceso', default => 'Planificada' };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="text-right">{{ number_format($pr->planned_boxes, 0, ',', '.') }}</td>
                                <td><a href="{{ route('produccion.index') }}" class="btn btn-outline-info btn-sm">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>No hay producciones pendientes.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div id="urgentTasksModal" class="sales-modal-overlay" onclick="if(event.target===this)closeUrgentTasksModal()">
        <div class="sales-modal-content" style="max-width:800px;">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Tareas urgentes ({{ $urgentTasksList->count() }})</h3>
                <button onclick="closeUrgentTasksModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area" style="padding:0;overflow-y:auto;max-height:60vh;">
                @if($urgentTasksList->count() > 0)
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Tarea</th>
                            <th>Responsable</th>
                            <th>Vencimiento</th>
                            <th>Modulo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($urgentTasksList as $t)
                            <tr>
                                <td class="font-bold">{{ $t->title }}</td>
                                <td>{{ $t->owner ?? '—' }}</td>
                                <td class="text-negative">{{ $t->due_on?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $t->module ?? '—' }}</td>
                                <td><a href="{{ route('tasks.index') }}" class="btn btn-outline-info btn-sm">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="data-table-empty"><p>No hay tareas urgentes.</p></div>
                @endif
            </div>
        </div>
    </div>

    <script>
        var salesModalChart = null;

        function openSalesModal() {
            var modal = document.getElementById('salesModal');
            modal.style.display = 'flex';

            requestAnimationFrame(function() {
                var ctx = document.getElementById('chartSalesModal').getContext('2d');
                if (salesModalChart) { salesModalChart.destroy(); }

                var labels = {!! json_encode($salesMonths->pluck('label')) !!};
                var values = {!! json_encode($salesMonths->pluck('value')) !!};

                salesModalChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas',
                            data: values,
                            backgroundColor: '#df6403',
                            borderRadius: 6,
                            barPercentage: 0.55
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toLocaleString('es-CL'); } } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        }

        function closeSalesModal() {
            document.getElementById('salesModal').style.display = 'none';
        }

        function openPendingOrdersModal() {
            document.getElementById('pendingOrdersModal').style.display = 'flex';
        }

        function closePendingOrdersModal() {
            document.getElementById('pendingOrdersModal').style.display = 'none';
        }

        function openCriticalAlertsModal() {
            document.getElementById('criticalAlertsModal').style.display = 'flex';
        }

        function closeCriticalAlertsModal() {
            document.getElementById('criticalAlertsModal').style.display = 'none';
        }

        function openAttentionAlertsModal() { document.getElementById('attentionAlertsModal').style.display = 'flex'; }
        function closeAttentionAlertsModal() { document.getElementById('attentionAlertsModal').style.display = 'none'; }

        function openCapacityModal() {
            document.getElementById('capacityModal').style.display = 'flex';
        }

        function closeCapacityModal() {
            document.getElementById('capacityModal').style.display = 'none';
        }

        function openOverdueOrdersModal() {
            document.getElementById('overdueOrdersModal').style.display = 'flex';
        }
        function closeOverdueOrdersModal() {
            document.getElementById('overdueOrdersModal').style.display = 'none';
        }

        function openOverduePurchasesModal() {
            document.getElementById('overduePurchasesModal').style.display = 'flex';
        }
        function closeOverduePurchasesModal() {
            document.getElementById('overduePurchasesModal').style.display = 'none';
        }

        function openPendingProductionsModal() {
            document.getElementById('pendingProductionsModal').style.display = 'flex';
        }
        function closePendingProductionsModal() {
            document.getElementById('pendingProductionsModal').style.display = 'none';
        }

        function openUrgentTasksModal() {
            document.getElementById('urgentTasksModal').style.display = 'flex';
        }
        function closeUrgentTasksModal() {
            document.getElementById('urgentTasksModal').style.display = 'none';
        }
    </script>

</x-erp-layout>
