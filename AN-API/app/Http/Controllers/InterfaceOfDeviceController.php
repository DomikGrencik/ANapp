<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SwController;
use App\Http\Controllers\EDController;
use App\Models\InterfaceOfDevice;

class InterfaceOfDeviceController extends Controller
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
        /* $request->validate([
            'id' => 'required',
            'type' => 'required',
            'device_id' => 'required'
        ]);

        $id = $request->id;
        $type = $request->type;
        $device_id = $request->device_id;

        $ports = (new PortController)->devicesPorts($device_id);

        foreach ($ports as $key => $value) {
            for ($i = 0; $i < $value->number_of_ports; $i++) {
                InterfaceOfDevice::create([
                    'name' => $value->connector,
                    'connector' => $value->connector,
                    'AN' => $value->AN,
                    'speed' => $value->speed,
                    'uplink_downlink' => $value->uplink_downlink,
                    'id' => $id
                ]);
            }
        } */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInterface(string $id, string $device_id)
    {
        $ports = (new PortController)->devicesPorts($device_id);

        foreach ($ports as $key => $value) {
            for ($i = 0; $i < $value->number_of_ports; $i++) {
                InterfaceOfDevice::create([
                    'name' => $value->connector,
                    'connector' => $value->connector,
                    'AN' => $value->AN,
                    'speed' => $value->speed,
                    'id' => $id,
                    'type' => $value->type
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
