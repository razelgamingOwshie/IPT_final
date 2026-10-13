<form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" class="card shadow-sm">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="name" class="form-label">Product name</label>
                <input id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Organic whole milk" required maxlength="120">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="sku" class="form-label">SKU</label>
                <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control @error('sku') is-invalid @enderror" placeholder="e.g. DAIRY-001" required maxlength="50">
                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="category" class="form-label">Category</label>
                <input id="category" name="category" value="{{ old('category', $product->category) }}" class="form-control @error('category') is-invalid @enderror" placeholder="e.g. Dairy" required maxlength="80">
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="supplier_id" class="form-label">Supplier <span class="text-body-secondary">(optional)</span></label>
                <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                    <option value="">No supplier assigned</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="unit" class="form-label">Unit / pack size</label>
                <input id="unit" name="unit" value="{{ old('unit', $product->unit ?: 'each') }}" class="form-control @error('unit') is-invalid @enderror" placeholder="e.g. 1 L, kg, each" required maxlength="30">
                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="price" class="form-label">Selling price</label>
                <div class="input-group"><span class="input-group-text">$</span><input id="price" type="number" name="price" value="{{ old('price', $product->price) }}" min="0.01" step="0.01" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" required></div>
                @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="stock" class="form-label">Current stock</label>
                <input id="stock" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" step="1" class="form-control @error('stock') is-invalid @enderror" required>
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="reorder_level" class="form-label">Low stock alert at</label>
                <input id="reorder_level" type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level ?? 5) }}" min="0" step="1" class="form-control @error('reorder_level') is-invalid @enderror" required>
                @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">{{ $product->exists ? 'Save Changes' : 'Add Product' }}</button>
    </div>
</form>
