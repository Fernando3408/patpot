<x-erp-layout title="Historial de auditoría" subtitle="Registro de todas las acciones realizadas en el sistema.">

    <div class="page-header">
    </div>

    @if($logs->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-xs">{{ $log->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td><span class="badge badge-info">{{ $log->action }}</span></td>
                            <td class="text-xs">{{ $log->description ?? '—' }}</td>
                            <td class="text-xs">{{ $log->user?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay registros de auditoría.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
