<x-erp-layout title="Editar precio por cliente" subtitle="Modifica los precios y ofertas especiales asignados a este cliente.">
    
    <form method="POST" action="/precios/{{ $price->id }}">
        @csrf
        @method('PUT')

        {{-- Datos principales --}}
        <div class="card p-4 mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="customer_id">Cliente</label>
                        <select id="customer_id" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $price->customer_id) == $customer->id)>
                                    {{ $customer->trade_name ?? $customer->business_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="product_id">Producto</label>
                        <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccione un producto</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $price->product_id) == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="price_box">Precio por caja</label>
                        <input id="price_box" type="number" step="1" min="0" name="price_box" class="form-control @error('price_box') is-invalid @enderror" value="{{ old('price_box', $price->price_box) }}" required>
                        @error('price_box')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="offer_price">Precio de oferta (opcional)</label>
                        <input id="offer_price" type="number" step="1" min="0" name="offer_price" class="form-control @error('offer_price') is-invalid @enderror" value="{{ old('offer_price', $price->offer_price) }}">
                        @error('offer_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="offer_until">Oferta vigente hasta (opcional)</label>
                        <input id="offer_until" type="date" name="offer_until" class="form-control @error('offer_until') is-invalid @enderror" value="{{ old('offer_until', $price->offer_until?->format('Y-m-d')) }}">
                        @error('offer_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de Control --}}
        <div class="form-actions d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>
        </div>
    </form>
</x-erp-layout>
