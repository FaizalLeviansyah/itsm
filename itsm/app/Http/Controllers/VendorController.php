<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(10);
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Vendor::create($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $vendor->update($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Data Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->tickets()->count() > 0) {
            return back()->with('error', 'Vendor tidak dapat dihapus karena masih terikat dengan data tiket.');
        }

        $vendor->delete();

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor berhasil dihapus.');
    }
}