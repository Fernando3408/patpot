<x-erp-layout title="Nuevo insumo" subtitle="Registra las materias primas o insumos para producción, planificación de compras e inventario.">
    <div class="form-card">
        <form method="POST" action="/insumos">
            @csrf

            {{-- Sección: Identificación General --}}
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Información General</h3>
            <div class="form-grid mb-6">
                <div class="form-group">
                    <label class="form-label" for="code">Código</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Ej: INS-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nombre del insumo</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej: Azúcar Rubia" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">Categoría</label>
                    <input type="text" id="category" name="category" class="form-control" value="{{ old('category') }}" placeholder="Ej: Materias Primas">
                </div>

                <div class="form-group">
                    <label class="form-label" for="unit">Unidad de medida</label>
                    <input type="text" id="unit" name="unit" class="form-control" value="{{ old('unit') }}" placeholder="kg, litro, unidad..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="supplier_id">Proveedor principal</label>
                    <select id="supplier_id" name="supplier_id" class="form-control">
                        <option value="">Sin proveedor asignado</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Estado</label>
                    <select id="status" name="status" class="form-control">
                        <option value="1" @selected(old('status', '1') == '1')>Activo</option>
                        <option value="0" @selected(old('status') == '0')>Inactivo</option>
                    </select>
                </div>
            </div>

            {{-- Sección: Stock y Costos --}}
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Inventario y Costos</h3>
            <div class="form-grid mb-6">
                <div class="form-group">
                    <label class="form-label" for="stock">Stock actual</label>
                    <input type="number" step="1" id="stock" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="safety_stock">Stock de seguridad</label>
                    <input type="number" step="1" id="safety_stock" name="safety_stock" class="form-control" value="{{ old('safety_stock', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="transit">Stock en tránsito</label>
                    <input type="number" step="1" id="transit" name="transit" class="form-control" value="{{ old('transit', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="unit_cost">Costo unitario ($)</label>
                    <input type="number" step="1" id="unit_cost" name="unit_cost" class="form-control" value="{{ old('unit_cost', 0) }}" min="0">
                </div>
            </div>

            {{-- Sección: Planificación de Compras --}}
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Parámetros de Reposición</h3>
            <div class="form-grid mb-6">
                <div class="form-group">
                    <label class="form-label" for="weekly_consumption">Consumo semanal</label>
                    <input type="number" step="1" id="weekly_consumption" name="weekly_consumption" class="form-control" value="{{ old('weekly_consumption', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="lead_time_days">Lead time (días)</label>
                    <input type="number" step="1" id="lead_time_days" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="target_weeks">Cobertura objetivo (semanas)</label>
                    <input type="number" step="1" id="target_weeks" name="target_weeks" class="form-control" value="{{ old('target_weeks', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="min_purchase">Compra mínima</label>
                    <input type="number" step="1" id="min_purchase" name="min_purchase" class="form-control" value="{{ old('min_purchase', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="purchase_multiple">Múltiplo de compra</label>
                    <input type="number" step="1" id="purchase_multiple" name="purchase_multiple" class="form-control" value="{{ old('purchase_multiple', 1) }}" min="1">
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Guardar insumo
                </button>
            </div>
        </form>
    </div>
</x-erp-layout>