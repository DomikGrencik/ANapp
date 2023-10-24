<?php

namespace App\Http\Controllers;

use App\Models\DevicesInNetwork;
use Illuminate\Http\Request;

class DevicesInNetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'router_id' => 'nullable',
            'switch_id' => 'nullable',
            'ED_id' => 'nullable'
        ]);

        DevicesInNetwork::create([
            'name' => $request->name,
            'type' => $request->type,
            'router_id' => $request->router_id,
            'switch_id' => $request->switch_id,
            'ED_id' => $request->ED_id
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return DevicesInNetwork::findOrFail($id);
    }

    /**
     * Display the specified resource.
     */
    public function findDeviceType(string $type)
    {
        return DevicesInNetwork::all()->where('type', $type);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
