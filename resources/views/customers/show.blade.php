<x-erp-layout title="Ficha del cliente" subtitle="Información comercial, contactos, salas y pedidos.">
    <div class="page-header"><a href="{{ route('customers.index') }}" class="btn btn-outline-warning btn-sm">Volver a clientes</a></div>
    @include('customers._detail', ['customer' => $customer])
</x-erp-layout>
