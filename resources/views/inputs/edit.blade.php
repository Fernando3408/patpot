<form method="POST" action="/insumos/{{ $input->id }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Identificación General</h3>
    <div class="form-grid">
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
    </div>

    <h3 class="text-sm" id="inventory-title">Inventario y Costos</h3>
    <div class="form-grid" id="inventory-fields">
        <div class="form-group">
            <label class="form-label">Stock actual</label>
            <input type="number" step="1" min="0" name="stock" class="form-control" value="{{ old('stock', floatval($input->stock)) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Stock de seguridad</label>
            <input type="number" step="1" min="0" name="safety_stock" class="form-control" value="{{ old('safety_stock', floatval($input->safety_stock)) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Stock en tránsito</label>
            <input type="number" step="1" min="0" name="transit" class="form-control" value="{{ old('transit', floatval($input->transit)) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Costo unitario ($)</label>
            <input type="number" step="1" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost', floatval($input->unit_cost)) }}" required>
        </div>
    </div>

    <h3 class="text-sm" id="planning-title">Parámetros de Reposición</h3>
    <div class="form-grid" id="planning-fields">
        <div class="form-group">
            <label class="form-label">Consumo semanal</label>
            <input type="number" step="1" min="0" id="weekly_consumption" name="weekly_consumption" class="form-control" value="{{ old('weekly_consumption', floatval($input->weekly_consumption)) }}">
            @php $auto = $input->auto_weekly_consumption; @endphp
            @if($auto > 0)
                <p class="form-hint">Promedio real: <strong>{{ number_format($auto, 0, ',', '.') }}</strong>
                    <button type="button" class="btn btn-outline-primary btn-xs" onclick="document.getElementById('weekly_consumption').value = Math.round({{ $auto }});">Usar promedio</button>
                </p>
            @endif
        </div>
        <div class="form-group">
            <label class="form-label">Lead time (días)</label>
            <input type="number" step="1" min="0" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $input->lead_time_days) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Cobertura objetivo (semanas)</label>
            <input type="number" step="1" min="0" name="target_weeks" class="form-control" value="{{ old('target_weeks', $input->target_weeks) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Compra mínima</label>
            <input type="number" step="0.001" min="0" name="min_purchase" class="form-control" value="{{ old('min_purchase', floatval($input->min_purchase)) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Múltiplo de compra</label>
            <input type="number" step="0.001" min="0.001" name="purchase_multiple" class="form-control" value="{{ old('purchase_multiple', floatval($input->purchase_multiple)) }}">
        </div>
    </div>

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
        if (invTitle) invTitle.textContent = isService ? 'Costo del Servicio' : 'Inventario y Costos';
        if (invFields) invFields.querySelectorAll('input[type=number]').forEach(function(inp) {
            if (inp.name === 'unit_cost') return;
            if (isService) { inp.value = '0'; inp.closest('.form-group').style.display = 'none'; }
            else { inp.closest('.form-group').style.display = ''; }
        });
        if (planFields) planFields.style.display = isService ? 'none' : '';
        if (planTitle) planTitle.style.display = isService ? 'none' : '';
    }
    el.addEventListener('change', toggleTypeFields);
    toggleTypeFields();
})();
</script>
