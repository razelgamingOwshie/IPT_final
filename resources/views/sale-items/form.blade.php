<form method="POST" action="{{ $saleItem->exists ? route('sale-items.update', $saleItem) : route('sale-items.store') }}" class="card shadow-sm">
    @csrf
    @if ($saleItem->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="sale_id" class="form-label">Sale</label>
                <select id="sale_id" name="sale_id" class="form-select @error('sale_id') is-invalid @enderror" required>
                    <option value="">Choose a sale</option>
                    @foreach ($sales as $sale)
                        <option value="{{ $sale->id }}" data-customer="{{ $sale->customer_name ?: 'Walk-in customer' }}" @selected((string) old('sale_id', $saleItem->sale_id) === (string) $sale->id)>
                            #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }} — {{ $sale->sale_date->format('M j, Y') }}
                        </option>
                    @endforeach
                </select>
                @error('sale_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="product_id" class="form-label">Product</label>
                <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                    <option value="">Choose a product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-unit="{{ $product->unit }}" @selected((string) old('product_id', $saleItem->product_id) === (string) $product->id)>
                            {{ $product->name }} — {{ $product->category }} (${{ number_format((float) $product->price, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-6">
                <label for="product_name" class="form-label">Line description</label>
                <input id="product_name" name="product_name" value="{{ old('product_name', $saleItem->product_name) }}" class="form-control @error('product_name') is-invalid @enderror" maxlength="120" placeholder="Product name on the receipt">
                @error('product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="unit_price" class="form-label">Unit price</label>
                <div class="input-group"><span class="input-group-text">$</span><input id="unit_price" type="number" name="unit_price" value="{{ old('unit_price', $saleItem->unit_price) }}" min="0" step="0.01" class="form-control @error('unit_price') is-invalid @enderror" placeholder="0.00" required></div>
                @error('unit_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input id="quantity" type="number" name="quantity" value="{{ old('quantity', $saleItem->quantity) }}" min="1" step="1" class="form-control @error('quantity') is-invalid @enderror" required>
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end">
                <div class="form-control-plaintext fw-semibold" id="line_total_display">
                    ${{ number_format((float) ($saleItem->line_total ?? 0), 2) }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="{{ route('sale-items.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $saleItem->exists ? 'Save Changes' : 'Add Line Item' }}</button>
    </div>
</form>

@push('scripts')
<script>
    const productEl = document.getElementById('product_id');
    const qtyEl = document.getElementById('quantity');
    const priceEl = document.getElementById('unit_price');
    const nameEl = document.getElementById('product_name');
    const totalEl = document.getElementById('line_total_display');

    function updateTotal() {
        const price = parseFloat(priceEl.value) || 0;
        const qty = parseInt(qtyEl.value, 10) || 0;
        totalEl.textContent = '$' + (price * qty).toFixed(2);
    }

    function pickProduct() {
        const opt = productEl.options[productEl.selectedIndex];
        if (opt && opt.dataset.name) {
            if (!nameEl.value) { nameEl.value = opt.dataset.name; }
            if (!priceEl.value) { priceEl.value = opt.dataset.price; }
        }
        updateTotal();
    }

    [productEl, qtyEl, priceEl].forEach(el => el.addEventListener('change', updateTotal));
    qtyEl.addEventListener('input', updateTotal);
    priceEl.addEventListener('input', updateTotal);
    productEl.addEventListener('change', pickProduct);
    updateTotal();
</script>
@endpush
