<x-erp-layout title="Registro de ingresos" subtitle="Historial de inicio de sesión de usuarios.">
    <div class="page-header">
        <form method="GET" action="{{ route('admin.login-logs.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por usuario..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
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
    {{ $logs->links() }}
</x-erp-layout>
