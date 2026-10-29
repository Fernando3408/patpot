<x-erp-layout title="Nuevo producto" subtitle="Ingresa los datos base para registrar un nuevo producto en el catálogo.">
    <div class="form-card">
        <form method="POST" action="/productos" enctype="multipart/form-data">
            @csrf

            {{-- Información Principal --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="name" class="form-label">Nombre del producto</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Ej: Papas Chips Sal de Mar 150 g"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="sku" class="form-label">SKU</label>
                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        class="form-control"
                        value="{{ old('sku') }}"
                        placeholder="Ej: PROD-FRU-250"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="grams" class="form-label">Gramos por unidad</label>
                    <input
                        type="number"
                        id="grams"
                        name="grams"
                        class="form-control"
                        value="{{ old('grams') }}"
                        placeholder="250"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="units_per_box" class="form-label">Unidades por caja</label>
                    <input
                        type="number"
                        id="units_per_box"
                        name="units_per_box"
                        class="form-control"
                        value="{{ old('units_per_box') }}"
                        placeholder="12"
                        step="1"
                        min="1"
                    >
                </div>

                <div class="form-group" style="grid-column:span 2;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:start">
                    <div>
                        <label for="reference_image" class="form-label">Imagen referencial</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <select id="reference_image" name="reference_image" style="display:none">
                                <option value="">Sin imagen referencial</option>
                                @foreach($referenceImages as $image)<option value="{{ $image }}">{{ pathinfo($image, PATHINFO_FILENAME) }}</option>@endforeach
                            </select>
                            <details class="reference-picker" id="reference-picker">
                                <summary class="form-control" id="reference-picker-label" onclick="event.preventDefault(); this.parentElement.open = !this.parentElement.open">Sin imagen referencial</summary>
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
                        <img id="reference-image-preview" style="display:none;max-height:64px;max-width:250px;object-fit:contain" alt="Vista previa">
                        <span id="reference-image-placeholder" style="font-size:12px;color:#8b8175;text-align:center">Vista previa</span>
                    </div>
                </div>
            </div>

            {{-- Inventario y Precios --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="stock_boxes" class="form-label">Stock inicial (cajas)</label>
                    <input
                        type="number"
                        id="stock_boxes"
                        name="stock_boxes"
                        class="form-control"
                        value="{{ old('stock_boxes', 0) }}"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="min_stock_boxes" class="form-label">Stock mínimo (cajas)</label>
                    <input
                        type="number"
                        id="min_stock_boxes"
                        name="min_stock_boxes"
                        class="form-control"
                        value="{{ old('min_stock_boxes', 0) }}"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="production_cost" class="form-label">Costo piso por caja ($)</label>
                    <input
                        type="number"
                        id="production_cost"
                        name="production_cost"
                        class="form-control"
                        value="{{ old('production_cost') }}"
                        placeholder="Dejar vacío para costo calculado"
                        step="1"
                        min="0"
                        oninput="updateVatPreview(this, 'product-vat-preview')"
                    >
                    <p class="text-xs text-gray-500 mt-1">Si se define, reemplaza el costo calculado por receta.</p>
                    <div id="product-vat-preview" class="vat-preview">IVA 19%: —<br><strong>Total con IVA: —</strong></div>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" @selected(old('status') === 'active')>Activo</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactivo</option>
                    </select>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
    <script>
        function previewReferenceImage(select, targetId) {
            const preview = document.getElementById(targetId);
            const fileInput = document.getElementById('reference_image_file');
            if (select.value === '__add__') { select.value = ''; fileInput.click(); return; }
            fileInput.value = '';
            const placeholder = document.getElementById('reference-image-placeholder');
            if (!select.value) { preview.style.display = 'none'; placeholder.style.display = 'block'; return; }
            preview.src = '{{ url('/productos/imagen-referencial') }}/' + encodeURIComponent(select.value);
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        function previewUploadedReferenceImage(input, targetId) {
            if (!input.files[0]) return;
            const preview = document.getElementById(targetId);
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
        function clearReferenceImage(selectId, fileId, targetId) {
            document.getElementById(selectId).value = '';
            document.getElementById(fileId).value = '';
            document.getElementById(targetId).style.display = 'none';
            document.getElementById('reference-image-placeholder').style.display = 'block';
        }
        async function deleteCatalogReferenceImage(selectId) {
            if (!confirm('¿Eliminar esta imagen del catálogo?')) return;
            const response = await fetch('{{ route('products.delete-reference-image') }}', { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({filename: selectId}) });
            if (response.ok) location.reload();
        }
        function toggleReferencePicker() { document.getElementById('reference-picker').classList.toggle('open'); }
        function chooseReferenceImage(value, label) { document.getElementById('reference_image').value = value; document.getElementById('reference-picker-label').textContent = label; document.getElementById('reference-picker').classList.remove('open'); document.getElementById('reference-picker').open = false; previewReferenceImage(document.getElementById('reference_image'), 'reference-image-preview'); }
        document.addEventListener('DOMContentLoaded', () => previewReferenceImage(document.getElementById('reference_image'), 'reference-image-preview'));
    </script>
</x-erp-layout>
