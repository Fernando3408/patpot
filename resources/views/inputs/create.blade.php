<x-erp-layout title="Nuevo insumo" subtitle="Registra las materias primas, servicios o insumos para producción, planificación de compras e inventario.">
    <div class="form-card">
        <form method="POST" action="/insumos">
            @csrf

            {{-- Sección: Identificación General --}}
            <section class="input-form-section"><div class="input-form-section__heading"><div><h3>Información general</h3><p>Identifica el insumo y define cómo se utilizará en la operación.</p></div></div><div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="code">Código</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Ej: INS-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nombre del insumo</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej: Azúcar Rubia" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="type">Tipo</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="material" @selected(old('type', 'material') === 'material')>Material</option>
                        <option value="service" @selected(old('type') === 'service')>Servicio (maquila, flete, etc.)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">Categoría</label>
                    <input type="text" id="category" name="category" class="form-control" value="{{ old('category') }}" placeholder="Ej: Materias Primas, Envases, Servicios">
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
            </div></section>

            {{-- Sección: Stock y Costos --}}
            <section class="input-form-section"><div class="input-form-section__heading"><div><h3 id="inventory-title">Inventario y costos</h3><p>Define stock disponible, mínimo de alerta y costo unitario.</p></div></div><div class="form-grid" id="inventory-fields">
                <div class="form-group">
                    <label class="form-label" for="stock">Stock actual</label>
                    <input type="number" step="0.01" id="stock" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="safety_stock">Stock mínimo <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="safety_stock" name="safety_stock" class="form-control" value="{{ old('safety_stock', 0) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="transit">Stock en tránsito</label>
                    <input type="number" step="0.01" id="transit" name="transit" class="form-control" value="{{ old('transit', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="unit_cost">Costo unitario ($)</label>
                    <input type="number" step="1" id="unit_cost" name="unit_cost" class="form-control" value="{{ old('unit_cost', 0) }}" min="0" oninput="updateVatPreview(this, 'input-vat-preview')">
                    <div id="input-vat-preview" class="vat-preview">IVA 19%: $0<br><strong>Total con IVA: $0</strong></div>
                </div>
            </div></section>

            {{-- Sección: Parámetros de Reposición (solo material) --}}
             <section class="input-form-section"><div class="input-form-section__heading"><div><h3 id="planning-title">Abastecimiento</h3><p>Define el tiempo estimado de reposición del insumo.</p></div></div><div class="form-grid" id="planning-fields">
                <div class="form-group">
                    <label class="form-label" for="lead_time_days">Lead time (días)</label>
                    <input type="number" step="1" id="lead_time_days" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', 0) }}" min="0">
                </div>

            </div></section>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Guardar insumo
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleTypeFields() {
            var isService = document.getElementById('type').value === 'service';
            var invFields = document.getElementById('inventory-fields');
            var planFields = document.getElementById('planning-fields');
            var invTitle = document.getElementById('inventory-title');
            var planTitle = document.getElementById('planning-title');
            var invSection = invFields.closest('.input-form-section');
            var planSection = planFields.closest('.input-form-section');

            if (isService) {
                invTitle.textContent = 'Costo del Servicio';
                invSection.style.display = '';
                invFields.querySelectorAll('.form-group').forEach(function(el) {
                    var visible = Boolean(el.querySelector('#unit_cost'));
                    el.style.display = visible ? '' : 'none';
                    el.querySelectorAll('input').forEach(function(input) { input.required = visible && input.id === 'unit_cost'; });
                });
                planSection.style.display = 'none';
            } else {
                invTitle.textContent = 'Inventario y Costos';
                invSection.style.display = '';
                invFields.querySelectorAll('.form-group').forEach(function(el) {
                    el.style.display = '';
                    el.querySelectorAll('input').forEach(function(input) { input.required = input.id === 'safety_stock' || input.id === 'unit_cost'; });
                });
                planSection.style.display = '';
                planFields.style.display = '';
                planTitle.style.display = '';
            }
        }

        document.getElementById('type').addEventListener('change', toggleTypeFields);
        toggleTypeFields();
    </script>
</x-erp-layout>
