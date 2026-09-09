<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::orderBy('Name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSupplier($request);
        $data['Suppl_No'] = Supplier::nextNo();

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('status', __('Created supplier :name.', ['name' => $supplier->Name]));
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $this->validateSupplier($request);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('status', __('Updated supplier :name.', ['name' => $supplier->Name]));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->Name;

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('status', __('Deleted supplier :name.', ['name' => $name]));
    }

    protected function validateSupplier(Request $request): array
    {
        return $request->validate([
            'Name' => ['nullable', 'string', 'max:100'],
            'Address' => ['nullable', 'string', 'max:50'],
            'TelNo' => ['nullable', 'string', 'max:15'],
            'FaxNo' => ['nullable', 'string', 'max:15'],
        ]);
    }
}
