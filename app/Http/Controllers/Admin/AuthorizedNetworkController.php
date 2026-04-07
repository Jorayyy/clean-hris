<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedNetwork;
use Illuminate\Http\Request;

class AuthorizedNetworkController extends Controller
{
    public function index()
    {
        $networks = AuthorizedNetwork::all();
        return view('admin.authorized-networks.index', compact('networks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|unique:authorized_networks,ip_address',
        ]);

        AuthorizedNetwork::create($request->all());

        return back()->with('success', 'Authorized IP added successfully.');
    }

    public function update(Request $request, AuthorizedNetwork $authorizedNetwork)
    {
        $authorizedNetwork->update($request->only('is_active'));
        return back()->with('success', 'Status updated.');
    }

    public function destroy(AuthorizedNetwork $authorizedNetwork)
    {
        $authorizedNetwork->delete();
        return back()->with('success', 'Authorized IP removed.');
    }
}
