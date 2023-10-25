<?php

namespace App\Http\Controllers;

use App\Models\DevicesInNetwork;
use Illuminate\Http\Request;
use App\Http\Controllers\InterfaceOfDeviceController;

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
        ]);

        $name = 'R1';
        $id = '1';
        $type = 'router';

        $this->storeDevice($name, $id, $type);

        return DevicesInNetwork::all()->max('device_id');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeDevice(string $name, string $id, string $type)
    {
        /* $request->validate([
            'name' => 'required',
            'id' => 'nullable'
        ]); */

        $router_id = null;
        $switch_id = null;
        $ED_id = null;

        switch ($type) {
            case 'router':
                $router_id = $id;
                break;
            case 'switch':
                $switch_id = $id;
                break;
            case 'ED':
                $ED_id = $id;
                break;

            default:
                # code...
                break;
        }

        DevicesInNetwork::create([
            'name' => $name,
            'type' => $type,
            'router_id' => $router_id,
            'switch_id' => $switch_id,
            'ED_id' => $ED_id
        ]);

        //(new InterfaceOfDeviceController)->storeInterface($router_id);
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
