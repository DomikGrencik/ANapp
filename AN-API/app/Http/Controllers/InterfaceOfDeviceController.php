<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\RouterController;
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
        $request->validate([
            'device_id' => 'required',
            'type' => 'required',
            'id' => 'required'
        ]);

        switch ($request->type) {
            case 'router':
                $device = (new RouterController)->show($request->id);
                $lan = $device->LAN_ports;
                $wan = $device->WAN_ports;
                $lan_type = $device->LAN_type;
                $wan_type = $device->WAN_type;

                for ($i = 0; $i < $lan; $i++) {
                    InterfaceOfDevice::create([
                        'name' => "{$lan_type}{$i}",
                        'IP_address' => '1',
                        'category' => 'LAN',
                        'type' => $lan_type,
                        'interface_id2' => null,
                        'device_id' => $request->device_id
                    ]);
                }

                for ($i = 0; $i < $wan; $i++) {
                    InterfaceOfDevice::create([
                        'name' => "{$wan_type}{$i}",
                        'IP_address' => '1',
                        'category' => 'WAN',
                        'type' => $wan_type,
                        'interface_id2' => null,
                        'device_id' => $request->device_id
                    ]);
                }

                break;

            default:
                # code...
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInterface(string $device_id, string $type, string $id)
    {
        switch ($type) {
            case 'router':
                $device = (new RouterController)->show($id);
                $lan = $device->LAN_ports;
                $wan = $device->WAN_ports;
                $lan_type = $device->LAN_type;
                $wan_type = $device->WAN_type;

                for ($i = 0; $i < $lan; $i++) {
                    InterfaceOfDevice::create([
                        'name' => "{$lan_type}{$i}",
                        'IP_address' => '1',
                        'category' => 'LAN',
                        'type' => $lan_type,
                        'interface_id2' => null,
                        'device_id' => $device_id
                    ]);
                }
                for ($i = 0; $i < $wan; $i++) {
                    InterfaceOfDevice::create([
                        'name' => "{$wan_type}{$i}",
                        'IP_address' => '1',
                        'category' => 'WAN',
                        'type' => $wan_type,
                        'interface_id2' => null,
                        'device_id' => $device_id
                    ]);
                }

                break;

            default:
                # code...
                break;
        }

        //return $device;
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
