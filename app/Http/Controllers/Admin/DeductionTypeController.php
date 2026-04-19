<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeductionType;
use Illuminate\Http\Request;

class DeductionTypeController extends Controller
{
    public function index()
    {
        $types = DeductionType::all();
        return view('admin.settings.deductions.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:deduction_types,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DeductionType::create($request->all());
        return back()->with('success', 'Deduction type added successfully.');
    }

    public function update(Request $request, DeductionType $deductionType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $deductionType->update($request->all());
        return back()->with('success', 'Deduction type updated successfully.');
    }

    public function destroy(DeductionType $deductionType)
    {
        $deductionType->delete();
        return back()->with('success', 'Deduction type deleted successfully.');
    }
}
