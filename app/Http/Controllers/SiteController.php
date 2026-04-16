<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::all();
        return view('sites.index', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:sites,name']);
        Site::create($request->only('name', 'location'));
        return redirect()->route('sites.index')->with('success', 'Site created successfully.');
    }

    public function edit(Site $site)
    {
        $sites = Site::all();
        return view('sites.index', compact('site', 'sites'));
    }

    public function update(Request $request, Site $site)
    {
        $request->validate(['name' => 'required|string|max:255|unique:sites,name,' . $site->id]);
        $site->update($request->only('name', 'location'));
        return redirect()->route('sites.index')->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('sites.index')->with('success', 'Site deleted successfully.');
    }
}
