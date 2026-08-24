<x-erp-layout title="Dashboard" subtitle="Lo que requiere una decision, sin revisar hojas ni formulas.">

    <div class="dash-fit">

        {{-- BLOQUE 1: ACCIONES URGENTES --}}
        <div class="dash-urgent-grid">
            <div class="dash-urgent-card {{ $overdueOrders > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count" style="color: {{ $overdueOrders > 0 ? '#dc2626' : '#16a34a' }}">{{ $overdueOrders }}</span>
                <span class="dash-urgent-label">Pedidos atrasados</span>
            </div>
            <div class="dash-urgent-card {{ $overduePurchases > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count" style="color: {{ $overduePurchases > 0 ? '#dc2626' : '#16a34a' }}">{{ $overduePurchases }}</span>
                <span class="dash-urgent-label">Compras atrasadas</span>
            </div>
            <div class="dash-urgent-card {{ $pendingProductions > 0 ? 'dash-urgent--warn' : '' }}">
                <span class="dash-urgent-count" style="color: {{ $pendingProductions > 0 ? '#d97706' : '#16a34a' }}">{{ $pendingProductions }}</span>
                <span class="dash-urgent-label">Producciones pendientes</span>
            </div>
            <div class="dash-urgent-card {{ $urgentTasks > 0 ? 'dash-urgent--critical' : '' }}">
                <span class="dash-urgent-count" style="color: {{ $urgentTasks > 0 ? '#dc2626' : '#16a34a' }}">{{ $urgentTasks }}</span>
                <span class="dash-urgent-label">Tareas urgentes</span>
            </div>
        </div>

        {{-- BLOQUE 2: KPIs DE NEGOCIO --}}
        <div class="dash-kpi-grid">
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Venta del mes</p>
                <p class="dash-kpi-value">${{ number_format($salesMonth, 0, ',', '.') }}</p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Margen del mes</p>
                <p class="dash-kpi-value" style="color: {{ $marginMonth >= 0 ? '#16a34a' : '#dc2626' }}">
                    ${{ number_format($marginMonth, 0, ',', '.') }}
                </p>
            </div>
            <div class="dash-kpi-card">
                <p class="dash-kpi-title">Stock total valorizado</p>
                <p class="dash-kpi-value">${{ number_format($stockPT + $stockInputs, 0, ',', '.') }}</p>
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
                            <span style="font-size:1.5rem;">&#10003;</span>
                            <p>Sin alertas criticas</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="dash-charts-panel">
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <h3>Tendencia de ventas</h3>
                    <span class="text-xs text-muted">Ultimos 6 meses</span>
                </div>
                <div class="dash-chart-body">
                    @if($salesMonths->count() > 0)
                        <canvas id="chartSales"></canvas>
                    @else
                        <div class="dash-chart-empty">
                            <span>Sin datos de ventas</span>
                        </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var orange = '#df6403';
            var red = '#dc2626';
            var green = '#16a34a';

            @if($salesMonths->count() > 0)
            new Chart(document.getElementById('chartSales'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($salesMonths->pluck('label')) !!},
                    datasets: [{
                        label: 'Ventas',
                        data: {!! json_encode($salesMonths->pluck('value')) !!},
                        backgroundColor: orange,
                        borderRadius: 4,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString('es-CL') } },
                        x: { grid: { display: false } }
                    }
                }
            });
            @endif

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

</x-erp-layout>
