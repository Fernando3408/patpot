<x-erp-layout title="Dashboard" subtitle="Lo que requiere una decision, sin revisar hojas ni formulas.">

    <div class="dash-fit">

        {{-- BLOQUE 1: ACCIONES URGENTES --}}
        <div class="dash-urgent-grid">
            <div class="dash-urgent-card {{ $overdueOrders > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count {{ $overdueOrders > 0 ? 'text-urgent' : 'text-ok' }}">{{ $overdueOrders }}</span>
                <span class="dash-urgent-label">Pedidos atrasados</span>
            </div>
            <div class="dash-urgent-card {{ $overduePurchases > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count {{ $overduePurchases > 0 ? 'text-urgent' : 'text-ok' }}">{{ $overduePurchases }}</span>
                <span class="dash-urgent-label">Compras atrasadas</span>
            </div>
            <div class="dash-urgent-card {{ $pendingProductions > 0 ? 'dash-urgent--warn' : '' }}">
                <span class="dash-urgent-count {{ $pendingProductions > 0 ? 'text-warning-color' : 'text-ok' }}">{{ $pendingProductions }}</span>
                <span class="dash-urgent-label">Producciones pendientes</span>
            </div>
            <div class="dash-urgent-card {{ $urgentTasks > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count {{ $urgentTasks > 0 ? 'text-urgent' : 'text-ok' }}">{{ $urgentTasks }}</span>
                <span class="dash-urgent-label">Tareas urgentes</span>
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
        </div>

        {{-- BLOQUE 3: OPERACION Y ANALISIS --}}
        <div class="dash-bottom-grid">

            <div class="dash-alerts-panel">
                <div class="dash-panel-header">
                    <h3>Alertas criticas</h3>
                    @if($criticalAlerts->count() > 0)
                        <span class="dash-alerts-badge">{{ $criticalAlerts->count() }}</span>
                    @endif
                </div>
                <div class="dash-alerts-body">
                    @forelse($criticalAlerts as $alert)
                        <div class="dash-alert-row">
                            <div class="dash-alert-info">
                                <strong>{{ $alert['title'] }}</strong>
                                <span class="dash-alert-module">{{ $alert['module'] }}</span>
                                <p class="dash-alert-detail">{{ $alert['detail'] }}</p>
                            </div>
                            <a href="{{ $alert['action_url'] }}" class="btn btn-outline-info btn-sm">Ver</a>
                        </div>
                    @empty
                        <div class="dash-alerts-empty">
                            <span class="icon-lg">&#10003;</span>
                            <p>Sin alertas criticas</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="dash-charts-panel">
                <div class="dash-charts-row">
                    <div class="dash-chart-card">
                        <div class="dash-chart-header">
                            <h3>Capacidad producible</h3>
                            <span class="text-xs text-muted">Segun insumo limitante</span>
                        </div>
                        <div class="dash-chart-body">
                            @if(count($productionCapacities) > 0)
                                @foreach($productionCapacities as $cap)
                                    <div class="capacity-row">
                                        <div>
                                            <strong class="capacity-name">{{ $cap['name'] }}</strong>
                                            <span class="text-xs text-muted"> · Limita: {{ $cap['limiting'] }}</span>
                                        </div>
                                        <div class="capacity-value">
                                            <strong>{{ number_format($cap['capacity'], 0, ',', '.') }}</strong>
                                            <span class="text-xs text-muted">cajas</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="dash-chart-empty"><span>Sin datos de produccion</span></div>
                            @endif
                        </div>
                    </div>
                    <div class="dash-chart-card">
                        <div class="dash-chart-header">
                            <h3>Stock insumos vs seguridad</h3>
                            <select id="inputSelector" class="dash-select"></select>
                        </div>
                        <div class="dash-chart-body">
                            <canvas id="chartStockInputs"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var orange = '#df6403';
            var red = '#dc2626';
            var green = '#16a34a';

            var inputsData = {!! json_encode($chartInputsData) !!};
            var selector = document.getElementById('inputSelector');
            var gray = '#94a3b8';
            var greenLine = '#16a34a';

            inputsData.forEach(function(inp, i) {
                var opt = document.createElement('option');
                opt.value = i;
                opt.textContent = inp.name;
                selector.appendChild(opt);
            });

            var unitMap = { 'kg': 'kilos', 'g': 'gramos', 'gr': 'gramos', 'ml': 'mililitros', 'l': 'litros', 'un': 'unidades', 'unid': 'unidades', 'unidades': 'unidades' };
            function formatUnit(u) { return unitMap[(u || '').toLowerCase()] || u || 'Unidades'; }

            var criticalIdx = 0;
            var maxDeficit = 0;
            inputsData.forEach(function(inp, i) {
                var deficit = inp.safety - inp.stock;
                if (deficit > maxDeficit) { maxDeficit = deficit; criticalIdx = i; }
            });
            selector.value = criticalIdx;

            var ctxInputs = document.getElementById('chartStockInputs');
            var chartInputs = new Chart(ctxInputs, {
                type: 'line',
                data: {
                    labels: ['Stock actual', 'Stock seguridad'],
                    datasets: [{
                        label: inputsData[criticalIdx].name,
                        data: [inputsData[criticalIdx].stock, inputsData[criticalIdx].safety],
                        borderColor: [greenLine, gray],
                        backgroundColor: [greenLine + '22', gray + '22'],
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: [greenLine, gray],
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 3,
                        tension: 0,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('es-CL');
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: formatUnit(inputsData[criticalIdx].unit), font: { size: 11 } } },
                        x: { grid: { display: false } }
                    }
                }
            });

            selector.addEventListener('change', function() {
                var idx = parseInt(this.value);
                var inp = inputsData[idx];
                chartInputs.data.datasets[0].label = inp.name;
                chartInputs.data.datasets[0].data = [inp.stock, inp.safety];
                chartInputs.options.scales.y.title.text = formatUnit(inp.unit);
                chartInputs.update();
            });
        });
    </script>

    <div id="salesModal" class="sales-modal-overlay" onclick="if(event.target===this)closeSalesModal()">
        <div class="sales-modal-content">
            <div class="sales-modal-header">
                <h3 class="sales-modal-title">Venta ultimos 6 meses</h3>
                <button onclick="closeSalesModal()" class="sales-modal-close">&times;</button>
            </div>
            <div class="sales-chart-area">
                <canvas id="chartSalesModal"></canvas>
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
    </script>

</x-erp-layout>
