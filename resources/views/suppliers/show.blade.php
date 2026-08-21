<x-erp-layout title="Detalle: {{ $supplier->name }}" subtitle="Información completa del registro.">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Proveedor</h2>
            <a href="{{ route('proveedores.index') }}" class="btn-outline-info btn-sm">← Volver</a>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>Nombre:</strong> {{ $supplier->name }}</div>
                <div><strong>RUT:</strong> {{ $supplier->rut ?? '—' }}</div>
                <div><strong>Contacto:</strong> {{ $supplier->contact_name ?? '—' }}</div>
                <div><strong>Email:</strong> {{ $supplier->email ?? '—' }}</div>
                <div><strong>Teléfono:</strong> {{ $supplier->phone ?? '—' }}</div>
                <div><strong>Lead time:</strong> {{ $supplier->lead_time_days }} días</div>
                <div><strong>Condiciones de pago:</strong> {{ $supplier->payment_terms ?? '—' }}</div>
                <div>
                    <strong>Estado:</strong>
                    <span class="badge {{ $supplier->status ? 'badge-success' : 'badge-danger' }}">
                        {{ $supplier->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($supplier->inputs->count())
    <div class="card mt-4">
        <div class="card__header">
            <h2 class="card__title">Insumos ({{ $supplier->inputs->count() }})</h2>
        </div>
        <div class="card__body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Costo unit.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplier->inputs as $input)
                        <tr>
                            <td>{{ $input->name }}</td>
                            <td class="text-xs">{{ $input->code }}</td>
                            <td class="text-right">{{ number_format($input->stock, 0, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($input->unit_cost, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-erp-layout>
