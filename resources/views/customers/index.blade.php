<x-erp-layout title="Clientes" subtitle="Gestiona los clientes, sus condiciones de pago y descuentos.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo cliente</a>
        </div>
    </div>

    @if ($customers->isNotEmpty())
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>RUT</th>
                        <th>Contacto</th>
                        <th>Pago</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr data-update-url="{{ route('customers.update', $customer) }}">
                            <td data-field="code" class="font-bold text-xs">{{ $customer->code }}</td>
                            <td data-field="business_name" data-value="{{ $customer->business_name }}">
                                <strong>{{ $customer->business_name }}</strong>
                                @if($customer->trade_name)
                                    <br><span class="text-xs text-muted">{{ $customer->trade_name }}</span>
                                @endif
                            </td>
                            <td data-field="rut" class="text-xs">{{ $customer->rut ?? '—' }}</td>
                            <td data-field="contact" class="text-xs">{{ $customer->contact ?? '—' }}</td>
                            <td data-field="payment_terms" class="text-xs">{{ $customer->payment_terms ?? '—' }}</td>
                            <td data-field="discount" data-value="{{ (int) $customer->discount }}" data-cleanup="int" class="text-xs font-bold">{{ $customer->discount }}%</td>
                            <td data-field="status" data-type="select" data-options='[{"value":"1","label":"Activo"},{"value":"0","label":"Inactivo"}]'>
                                <span class="badge {{ $customer->status ? 'badge-success' : 'badge-danger' }}">
                                    {{ $customer->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('customers.show', $customer) }}" data-title="Detalle: {{ $customer->business_name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay clientes registrados.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
