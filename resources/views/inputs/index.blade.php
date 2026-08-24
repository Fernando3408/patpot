<x-erp-layout title="Insumos" subtitle="Gestiona el inventario de materias primas con niveles de alerta y sugerencias de compra.">
    <div class="page-header">
        <form method="GET" action="{{ route('inputs.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, código, categoría..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('inputs.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <a href="{{ route('inputs.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo insumo</a>
        </div>
    </div>

    @if($inputs->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Seguridad</th>
                        <th>Unidad</th>
                        <th class="text-right">Reposición</th>
                        <th class="text-center">Cobertura</th>
                        <th>Nivel</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inputs as $input)
                        <tr data-update-url="{{ route('inputs.update', $input) }}">
                            <td data-field="code" class="font-bold text-xs">{{ $input->code }}</td>
                            <td data-field="name" data-value="{{ $input->name }}">
                                <strong>{{ $input->name }}</strong>
                                @if ($input->category)
                                    <br><span class="text-xs text-muted">{{ $input->category }}</span>
                                @endif
                            </td>
                            <td data-field="stock" data-cleanup="int" class="text-right font-bold">{{ number_format($input->stock, 0, ',', '.') }}</td>
                            <td data-field="safety_stock" data-cleanup="int" class="text-right text-xs">{{ number_format($input->safety_stock, 0, ',', '.') }}</td>
                            <td data-field="unit" class="text-xs font-bold">{{ $input->unit }}</td>
                            <td class="text-right text-xs">{{ number_format($input->reorder_point, 0, ',', '.') }}</td>
                            <td data-readonly="true" class="text-xs text-center">
                                @if($input->coverage_days !== null)
                                    <div style="font-weight:600;margin-bottom:4px;">{{ $input->coverage_days }} días</div>
                                    @php
                                        $maxDays = 90;
                                        $pct = min(($input->coverage_days / $maxDays) * 100, 100);
                                        $color = $input->coverage_days <= 7 ? '#dc2626' : ($input->coverage_days <= 21 ? '#f59e0b' : '#16a34a');
                                    @endphp
                                    <div style="background:#e5e7eb;border-radius:4px;height:6px;width:100%;overflow:hidden;">
                                        <div style="width:{{ $pct }}%;height:100%;background:{{ $color }};border-radius:4px;"></div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-readonly="true">
                                @php $level = $input->inventory_level; @endphp
                                @if($level === 'ok')
                                    <span class="badge badge-success">Óptimo</span>
                                @elseif($level === 'atencion')
                                    <span class="badge badge-warning" style="background-color:#f59e0b;color:#fff;">Atención</span>
                                @else
                                    <span class="badge badge-danger">Crítico</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('inputs.show', $input) }}" data-title="Detalle: {{ $input->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('inputs.destroy', $input) }}" class="inline-form" style="display:inline;">
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
                <p>No hay insumos registrados.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
