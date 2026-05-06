<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use Illuminate\Http\Request;

class AllowanceTypeController extends Controller
{
    public function index()
    {
        $types = AllowanceType::all();
        return view('admin.settings.allowances.index', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:allowance_types,code',
            'name' => 'required|string|max:255',
            'default_amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,daily',
            'is_taxable' => 'nullable|boolean',
        ]);

        $validated['is_taxable'] = $request->has('is_taxable');

        AllowanceType::create($validated);

        return redirect()->back()->with('success', 'Allowance type added successfully.');
    }

    public function update(Request $request, AllowanceType $allowance)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,daily',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'required|boolean',
        ]);

        $validated['is_taxable'] = $request->has('is_taxable');

        $allowance->update($validated);

        return redirect()->back()->with('success', 'Allowance type updated successfully.');
    }

    public function destroy(AllowanceType $allowance)
    {
        $allowance->delete();
        return redirect()->back()->with('success', 'Allowance type deleted successfully.');
    }
}
