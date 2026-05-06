<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Classification;
use App\Models\Level;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('name')->get();
        $classifications = Classification::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();

        return view('admin.designations.index', compact('positions', 'classifications', 'levels'));
    }

    // Positions
    public function storePosition(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:positions,name']);
        Position::create($request->only('name'));
        return back()->with('success', 'Position added successfully.');
    }

    public function destroyPosition(Position $position)
    {
        $position->delete();
        return back()->with('success', 'Position removed.');
    }

    // Classifications
    public function storeClassification(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:classifications,name']);
        Classification::create($request->only('name'));
        return back()->with('success', 'Classification added successfully.');
    }

    public function destroyClassification(Classification $classification)
    {
        $classification->delete();
        return back()->with('success', 'Classification removed.');
    }

    // Levels
    public function storeLevel(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:levels,name']);
        Level::create($request->only('name'));
        return back()->with('success', 'Level added successfully.');
    }

    public function destroyLevel(Level $level)
    {
        $level->delete();
        return back()->with('success', 'Level removed.');
    }
}
