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
            'users' => 'required',
            'vlans' => 'required',
            'IPaddr' => 'required',
        ]);

        $users = $request->users;
        $vlans = $request->vlans;
        $IPaddr = $request->IPaddr;


        $name = 'R3';
        $type = 'router';
        $device_id = '1';

        $this->storeDevice($name, $type, $device_id);

        $id = DevicesInNetwork::all()->max('id');

        (new InterfaceOfDeviceController)->storeInterface($id, $type, $device_id);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeDevice(string $name, string $type, string $device_id)
    {
        DevicesInNetwork::create([
            'name' => $name,
            'type' => $type,
            'device_id' => $device_id
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
