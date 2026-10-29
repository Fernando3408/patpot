<form method="POST" action="/insumos/{{ $input->id }}">
    @csrf
    @method('PUT')

    <section class="input-form-section"><div class="input-form-section__heading"><div><h3>Identificación General</h3><p>Identifica el insumo y define cómo se utilizará en la operación.</p></div></div><div class="form-grid">
        <div class="form-group">
            <label class="form-label">Código</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $input->code) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre del insumo</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $input->name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tipo</label>
            <select id="type" name="type" class="form-control" required>
                <option value="material" @selected(old('type', $input->type) === 'material')>Material</option>
                <option value="service" @selected(old('type', $input->type) === 'service')>Servicio (maquila, flete, etc.)</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Categoría</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', $input->category) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Unidad de medida</label>
            <input type="text" name="unit" class="form-control" value="{{ old('unit', $input->unit) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Proveedor principal</label>
            <select name="supplier_id" class="form-control">
                <option value="">Sin proveedor asignado</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $input->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control" required>
                <option value="1" @selected(old('status', $input->status) == 1)>Activo</option>
                <option value="0" @selected(old('status', $input->status) == 0)>Inactivo</option>
            </select>
        </div>
    </div></section>

    <section class="input-form-section"><div class="input-form-section__heading"><div><h3 id="inventory-title">{{ $input->isService() ? 'Costo del servicio' : 'Inventario y costos' }}</h3><p>Define stock disponible, mínimo de alerta y costo unitario.</p></div></div><div class="form-grid" id="inventory-fields">
        <div class="form-group" style="{{ $input->isService() ? 'display:none' : '' }}">
            <label class="form-label">Stock actual</label>
            <input type="number" step="0.01" min="0" name="stock" class="form-control" value="{{ old('stock', floatval($input->stock)) }}">
        </div>
        <div class="form-group" style="{{ $input->isService() ? 'display:none' : '' }}">
            <label class="form-label">Stock mínimo <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="safety_stock" class="form-control" value="{{ old('safety_stock', floatval($input->safety_stock)) }}" required>
        </div>
        <div class="form-group" style="{{ $input->isService() ? 'display:none' : '' }}">
            <label class="form-label">Stock en tránsito</label>
            <input type="number" step="0.01" min="0" name="transit" class="form-control" value="{{ old('transit', floatval($input->transit)) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Costo unitario ($)</label>
            <input type="number" step="1" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost', floatval($input->unit_cost)) }}" required oninput="updateVatPreview(this, 'input-vat-preview')">
            <div id="input-vat-preview" class="vat-preview">IVA 19%: ${{ number_format((float) $input->unit_cost * .19, 0, ',', '.') }}<br><strong>Total con IVA: ${{ number_format((float) $input->unit_cost * 1.19, 0, ',', '.') }}</strong></div>
        </div>
    </div></section>

    <section class="input-form-section"><div class="input-form-section__heading"><div><h3 id="planning-title">Abastecimiento</h3><p>Define el tiempo estimado de reposición del insumo.</p></div></div><div class="form-grid" id="planning-fields">
        <div class="form-group">
            <label class="form-label">Lead time (días)</label>
            <input type="number" step="1" min="0" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $input->lead_time_days) }}">
        </div>
    </div></section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>

<script>
(function() {
    var el = document.getElementById('type');
    if (!el) return;
    function toggleTypeFields() {
        var isService = el.value === 'service';
        var invTitle = document.getElementById('inventory-title');
        var invFields = document.getElementById('inventory-fields');
        var planTitle = document.getElementById('planning-title');
        var planFields = document.getElementById('planning-fields');
        var invSection = invFields?.closest('.input-form-section');
        var planSection = planFields?.closest('.input-form-section');
        if (invTitle) invTitle.textContent = isService ? 'Costo del Servicio' : 'Inventario y Costos';
        if (invSection) invSection.style.display = '';
        if (planSection) planSection.style.display = isService ? 'none' : '';
        if (invFields) invFields.querySelectorAll('input[type=number]').forEach(function(inp) {
            inp.closest('.form-group').style.display = isService && inp.name !== 'unit_cost' ? 'none' : '';
            inp.required = !isService && (inp.name === 'safety_stock' || inp.name === 'unit_cost');
        });
        // Planning params visible for both types
        if (planFields) planFields.style.display = '';
        if (planTitle) planTitle.style.display = '';
    }
    el.addEventListener('change', toggleTypeFields);
    toggleTypeFields();
})();
</script>
