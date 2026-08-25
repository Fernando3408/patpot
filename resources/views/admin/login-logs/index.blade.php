<x-erp-layout title="Registro de ingresos" subtitle="Historial de inicio de sesión de usuarios.">
    <div class="page-header">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->user?->name ?? 'Desconocido' }}</td>
                        <td>
                            @if($log->success)
                                @if($log->type === 'logout')
                                    <span class="badge badge--warning">Cierre de sesión</span>
                                @else
                                    <span class="badge badge--success">Inicio de sesión</span>
                                @endif
                            @else
                                <span class="badge badge--danger">Fallido</span>
                            @endif
                        </td>
                        <td class="text-xs">{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="data-table-empty">No hay registros de ingreso.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-erp-layout>
