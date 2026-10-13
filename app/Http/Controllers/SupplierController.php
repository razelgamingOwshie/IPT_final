<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(12);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validatedSupplier($request));

        return to_route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', [
            'supplier' => $supplier->load(['products' => fn ($query) => $query->orderBy('name')]),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validatedSupplier($request));

        return to_route('suppliers.show', $supplier)->with('success', 'Supplier details updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return to_route('suppliers.index')->with('success', 'Supplier removed. Its products remain in inventory without an assigned supplier.');
    }

    private function validatedSupplier(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
