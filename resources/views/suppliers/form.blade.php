<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="card shadow-sm">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="name" class="form-label">Supplier name</label>
                <input id="name" name="name" value="{{ old('name', $supplier->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="120" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="contact_name" class="form-label">Contact person <span class="text-body-secondary">(optional)</span></label>
                <input id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="form-control @error('contact_name') is-invalid @enderror" maxlength="120">
                @error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="email" class="form-label">Email <span class="text-body-secondary">(optional)</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="phone" class="form-label">Phone <span class="text-body-secondary">(optional)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="30" autocomplete="tel">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="address" class="form-label">Address <span class="text-body-secondary">(optional)</span></label>
                <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror" maxlength="2000">{{ old('address', $supplier->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $supplier->exists ? 'Save Changes' : 'Add Supplier' }}</button>
    </div>
</form>
