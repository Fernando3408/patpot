<x-erp-layout title="Costos del mes" subtitle="Registra costos fijos y variables que no pertenecen a un pedido.">
<div class="monthly-costs-page">
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
    <div class="form-card mb-4 monthly-cost-form-card">
        <h3 class="text-sm font-semibold mb-3">Registrar costo</h3>
        <form method="POST" action="{{ route('monthly-costs.store') }}" class="form-grid monthly-cost-form" enctype="multipart/form-data">
            @csrf
            <div class="monthly-field"><label class="form-label" for="cost_on">Fecha</label><input id="cost_on" type="date" name="cost_on" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
            <div class="monthly-field"><label class="form-label" for="concept">Concepto</label><input id="concept" type="text" name="concept" class="form-control" placeholder="Ej: Arriendo bodega" required></div>
            <div class="monthly-field"><label class="form-label" for="category">Categoría</label><select id="category" name="category" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select></div>
            <div class="monthly-field"><label class="form-label" for="amount">Monto sin IVA</label><input id="amount" type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="$ 0" oninput="updateVatPreview(this, 'monthly-vat-preview')" required><div id="monthly-vat-preview" class="vat-preview">IVA 19%: —<br><strong>Total con IVA: —</strong></div></div>
            <div class="monthly-field"><label class="form-label" for="type">Tipo de costo</label><select id="type" name="type" class="form-control"><option value="variable">Variable</option><option value="fixed">Fijo mensual</option></select></div>
            <div class="monthly-field monthly-notes"><label class="form-label" for="notes">Observaciones</label><input id="notes" type="text" name="notes" class="form-control" placeholder="Detalle opcional"></div>
            <div class="monthly-field monthly-attachment"><label class="form-label" for="attachment">Documento adjunto <span>(opcional)</span></label><input id="attachment" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.csv"></div>
            <label class="form-check monthly-recurring"><input type="checkbox" name="recurring" value="1"> Fijo mensual</label>
            <button class="btn btn-primary monthly-save-cost">Guardar costo</button>
        </form>
    </div>
    <div class="monthly-costs-columns"><div class="table-container monthly-costs-table"><div class="monthly-costs-heading"><h3>Costos de {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F') }}</h3><span>{{ $costs->count() }} costos</span></div><table class="data-table"><thead><tr><th>Fecha</th><th>Concepto</th><th>Categoría</th><th>Tipo</th><th class="text-right">Monto sin IVA</th><th class="text-right">Monto con IVA</th><th></th></tr></thead><tbody>
        @forelse($costs as $cost)
            <tr><form method="POST" action="{{ route('monthly-costs.update', $cost) }}">@csrf @method('PUT')<td><input type="date" name="cost_on" value="{{ $cost->cost_on->format('Y-m-d') }}" class="form-control"></td><td><input name="concept" value="{{ $cost->concept }}" class="form-control"><div class="cost-file-list">@foreach($cost->attachments as $attachment)<span class="cost-file"><a href="{{ route('attachments.download', $attachment) }}" title="Descargar">&#128196; {{ $attachment->original_name }}</a><button type="button" class="btn-link-danger" data-url="{{ route('attachments.destroy', $attachment) }}" onclick="deleteMonthlyAttachment(this)" title="Eliminar">&times;</button></span>@endforeach</div></td><td><input name="category" value="{{ $cost->category }}" class="form-control"></td><td><select name="type" class="form-control"><option value="fixed" @selected($cost->type === 'fixed')>Fijo</option><option value="variable" @selected($cost->type === 'variable')>Variable</option></select><small class="cost-type-badge">{{ $cost->recurring ? 'Recurrente' : 'Manual' }}</small></td><td class="text-right"><input type="number" step="0.01" min="1" name="amount" value="{{ (float) $cost->amount == (int) $cost->amount ? (int) $cost->amount : rtrim(rtrim((string) $cost->amount, '0'), '.') }}" class="form-control"></td><td class="text-right">${{ number_format((float) $cost->amount * 1.19, 0, ',', '.') }}</td><td class="text-right"><button class="btn btn-primary btn-sm">Guardar</button></td></form></tr>
        @empty
            <tr><td class="text-center">No hay costos registrados para este mes.</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="monthly-closure-panel">@if($closure)<h3>Cierre de {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F') }}</h3><p class="monthly-closure-subtitle">Así se calcula la utilidad neta del mes.</p><div class="monthly-closure-lines"><div><span>Ventas del mes</span><strong>${{ number_format($closure->sales, 0, ',', '.') }}</strong></div><div><span>− Costo de productos</span><strong>${{ number_format($closure->product_cost, 0, ',', '.') }}</strong></div><div><span>− Gastos asociados a pedidos</span><strong>${{ number_format($closure->expenses, 0, ',', '.') }}</strong></div><div class="monthly-total"><span>= Utilidad neta</span><strong>${{ number_format($closure->net_profit, 0, ',', '.') }}</strong></div></div><p class="text-muted">Cierre realizado el {{ $closure->closed_at?->format('d/m/Y H:i') }}.</p>@else<p class="monthly-closure-empty">El cierre estará disponible cuando confirmes los costos del mes.</p>@endif</div></div>
<script>
function deleteMonthlyAttachment(button) {
    if (!confirm('¿Eliminar este archivo?')) return;
    fetch(button.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: new URLSearchParams({ _method: 'DELETE' }) }).then(function(response) { if (response.ok) button.closest('.cost-file').remove(); });
}
</script>
</div>
</x-erp-layout>
