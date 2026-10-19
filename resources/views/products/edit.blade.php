<form method="POST" action="/productos/{{ $product->id }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Identificación</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Nombre del producto</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Gramos por unidad</label>
            <input type="number" name="grams" class="form-control" value="{{ old('grams', (int) $product->grams) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Unidades por caja</label>
            <input type="number" name="units_per_box" class="form-control" value="{{ old('units_per_box', (int) $product->units_per_box) }}" step="1" min="1" required>
        </div>
        <div class="form-group" style="grid-column:span 2;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:start">
            <div>
                <label for="reference_image" class="form-label">Imagen referencial</label>
                <div>
                    <select id="reference_image" name="reference_image" style="display:none">
                        <option value="">Sin imagen referencial</option>
                        @foreach($referenceImages as $image)<option value="{{ $image }}" @selected(old('reference_image', $product->reference_image) === $image)>{{ pathinfo($image, PATHINFO_FILENAME) }}</option>@endforeach
                    </select>
                    <details class="reference-picker" id="reference-picker">
                        <summary class="form-control" id="reference-picker-label" onclick="event.preventDefault(); this.parentElement.open = !this.parentElement.open">{{ $product->reference_image ? pathinfo($product->reference_image, PATHINFO_FILENAME) : 'Sin imagen referencial' }}</summary>
                        <div class="reference-picker-menu">
                            <button type="button" onclick="chooseReferenceImage('', 'Sin imagen referencial')">Sin imagen referencial</button>
                            @foreach($referenceImages as $image)
                                <div class="reference-picker-option"><button type="button" onclick="chooseReferenceImage(@js($image), @js(pathinfo($image, PATHINFO_FILENAME)))">{{ pathinfo($image, PATHINFO_FILENAME) }}</button><img src="{{ route('products.reference-image', ['filename' => $image]) }}"><button type="button" onclick="deleteCatalogReferenceImage(@js($image))">×</button></div>
                            @endforeach
                            <button type="button" onclick="document.getElementById('reference_image_file').click()">+ Agregar otra imagen</button>
                        </div>
                    </details>
                </div>
                <input id="reference_image_file" name="reference_image_file" type="file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadReferenceImage(this)">
            </div>
            <div style="height:70px;margin-top:24px;border:1px solid var(--border-color,#d6cec3);border-radius:10px;background:#fffaf3;display:flex;align-items:center;justify-content:center;overflow:hidden">
                <img id="reference-image-preview" src="{{ $product->reference_image ? route('products.reference-image', ['filename' => $product->reference_image]) : '' }}" style="{{ $product->reference_image ? '' : 'display:none;' }}max-height:64px;max-width:250px;object-fit:contain" alt="Vista previa">
                <span id="reference-image-placeholder" style="{{ $product->reference_image ? 'display:none;' : '' }}font-size:12px;color:#8b8175;text-align:center">Vista previa</span>
            </div>
        </div>
    </div>

    <h3 class="text-sm">Inventario y Precios</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Stock en cajas</label>
            <input type="number" name="stock_boxes" class="form-control" value="{{ old('stock_boxes', (int) $product->stock_boxes) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock mínimo (cajas)</label>
            <input type="number" name="min_stock_boxes" class="form-control" value="{{ old('min_stock_boxes', (int) $product->min_stock_boxes) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Precio venta por caja ($)</label>
        </div>
        <div class="form-group">
            <label class="form-label">Costo piso por caja ($)</label>
            <input type="number" name="production_cost" class="form-control" value="{{ old('production_cost', $product->production_cost) }}" placeholder="Vacío = costo calculado" step="1" min="0">
            <p class="form-hint">Si se define, reemplaza el costo calculado por receta.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control" required>
                <option value="active" @selected(old('status', $product->status) === 'active')>Activo</option>
                <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactivo</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
<script>
    function previewReferenceImage(select) {
        const preview = document.getElementById('reference-image-preview');
        const fileInput = document.getElementById('reference_image_file');
        if (select.value === '__add__') { select.value = ''; fileInput.click(); return; }
        fileInput.value = '';
        const placeholder = document.getElementById('reference-image-placeholder');
        if (!select.value) { preview.style.display = 'none'; placeholder.style.display = 'block'; return; }
        preview.src = '{{ url('/productos/imagen-referencial') }}/' + encodeURIComponent(select.value);
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    }
    function previewUploadedReferenceImage(input) {
        if (!input.files[0]) return;
        const preview = document.getElementById('reference-image-preview');
        preview.src = URL.createObjectURL(input.files[0]);
        preview.style.display = 'block';
        document.getElementById('reference-image-placeholder').style.display = 'none';
    }
    async function uploadReferenceImage(input) {
        if (!input.files[0]) return;
        const data = new FormData(); data.append('reference_image_file', input.files[0]);
        const response = await fetch('{{ route('products.upload-reference-image') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:data});
        if (!response.ok) return;
        const result = await response.json();
        const select = document.getElementById('reference_image');
        const option = new Option(result.filename.replace(/\.[^.]+$/, ''), result.filename, true, true);
        select.add(option); chooseReferenceImage(result.filename, option.text);
    }
    function clearReferenceImage() {
        document.getElementById('reference_image').value = '';
        document.getElementById('reference_image_file').value = '';
        document.getElementById('reference-image-preview').style.display = 'none';
        document.getElementById('reference-image-placeholder').style.display = 'block';
    }
    function toggleReferencePicker() { document.getElementById('reference-picker').classList.toggle('open'); }
    function chooseReferenceImage(value, label) { document.getElementById('reference_image').value = value; document.getElementById('reference-picker-label').textContent = label; document.getElementById('reference-picker').classList.remove('open'); document.getElementById('reference-picker').open = false; previewReferenceImage(document.getElementById('reference_image')); }
    async function deleteCatalogReferenceImage(filename) {
        if (!confirm('¿Eliminar esta imagen del catálogo?')) return;
        const response = await fetch('{{ route('products.delete-reference-image') }}', { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({filename}) });
        if (response.ok) location.reload();
    }
    document.addEventListener('DOMContentLoaded', () => previewReferenceImage(document.getElementById('reference_image')));
</script>
