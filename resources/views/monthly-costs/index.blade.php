<x-erp-layout title="Costos del mes" subtitle="Registra costos fijos y variables que no pertenecen a un pedido.">
    <div class="page-header">
        <form method="GET" class="search-form">
            <input type="month" name="month" class="form-control" value="{{ $month }}">
            <button class="btn btn-outline-success btn-sm">Consultar</button>
        </form>
        @if(!\App\Models\MonthlyClosure::where('month', $month)->exists())
            <form method="POST" action="{{ route('monthly-costs.close') }}" onsubmit="return confirm('¿Cerrar el mes {{ $month }}? Se congelarán ventas, costos, gastos y utilidad. Solo un administrador podrá reabrirlo.')">@csrf<input type="hidden" name="month" value="{{ $month }}"><button class="btn btn-primary btn-sm">Cerrar mes</button></form>
        @elseif(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('monthly-costs.reopen', $month) }}">@csrf<button class="btn btn-outline-warning btn-sm">Reabrir mes</button></form>
        @endif
    </div>
    @php($closure = \App\Models\MonthlyClosure::where('month', $month)->first())
    @if($closure)
        <div class="form-card mb-4">
            <h3 class="text-sm font-semibold mb-3">Cierre congelado</h3>
            <div class="stats-grid">
                <div><span class="text-muted">Ventas</span><strong>${{ number_format($closure->sales, 0, ',', '.') }}</strong></div>
                <div><span class="text-muted">Costo productos</span><strong>${{ number_format($closure->product_cost, 0, ',', '.') }}</strong></div>
                <div><span class="text-muted">Gastos</span><strong>${{ number_format($closure->expenses + $closure->monthly_costs, 0, ',', '.') }}</strong></div>
                <div><span class="text-muted">Utilidad neta</span><strong class="{{ $closure->net_profit >= 0 ? 'text-positive' : 'text-negative' }}">${{ number_format($closure->net_profit, 0, ',', '.') }}</strong></div>
            </div>
            <p class="text-xs text-muted mt-3">Cerrado el {{ $closure->closed_at?->format('d/m/Y H:i') }}. Los valores no cambian hasta reabrir el mes.</p>
        </div>
    @endif
    <div class="form-card mb-4">
        <h3 class="text-sm font-semibold mb-3">Registrar costo</h3>
        <form method="POST" action="{{ route('monthly-costs.store') }}" class="form-grid" enctype="multipart/form-data">
            @csrf
            <input type="date" name="cost_on" class="form-control" value="{{ $month }}-01" required>
            <input type="text" name="concept" class="form-control" placeholder="Concepto" required>
            <select name="category" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
            <input type="number" step="1" min="1" name="amount" class="form-control" placeholder="Monto" required>
            <select name="type" class="form-control"><option value="variable">Variable</option><option value="fixed">Fijo</option></select>
            <label class="form-check"><input type="checkbox" name="recurring" value="1"> Recurrente</label>
            <input type="text" name="notes" class="form-control" placeholder="Observaciones">
            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.csv">
            <button class="btn btn-primary">Guardar costo</button>
        </form>
    </div>
    <div class="table-container"><table class="data-table"><thead><tr><th>Fecha</th><th>Concepto</th><th>Categoría</th><th>Tipo</th><th class="text-right">Monto</th><th></th></tr></thead><tbody>
        @forelse($costs as $cost)
            <tr><form method="POST" action="{{ route('monthly-costs.update', $cost) }}">@csrf @method('PUT')<td><input type="date" name="cost_on" value="{{ $cost->cost_on->format('Y-m-d') }}" class="form-control"></td><td><input name="concept" value="{{ $cost->concept }}" class="form-control">@foreach($cost->attachments as $attachment)<a class="text-xs" href="{{ route('attachments.download', $attachment) }}">{{ $attachment->original_name }}</a>@endforeach</td><td><input name="category" value="{{ $cost->category }}" class="form-control"></td><td><select name="type" class="form-control"><option value="fixed" @selected($cost->type === 'fixed')>Fijo</option><option value="variable" @selected($cost->type === 'variable')>Variable</option></select></td><td class="text-right"><input type="number" step="1" min="1" name="amount" value="{{ $cost->amount }}" class="form-control"></td><td class="text-right"><button class="btn btn-primary btn-sm">Guardar</button></td></form></tr>
        @empty
            <tr><td class="text-center">No hay costos registrados para este mes.</td><td></td><td></td><td></td><td></td><td></td></tr>
        @endforelse
    </tbody></table></div>
</x-erp-layout>
