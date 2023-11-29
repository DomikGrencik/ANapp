<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InterfaceOfDevice;
use App\Models\Port;

class InterfaceOfDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InterfaceOfDevice::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInterface(string $id, string $device_id)
    {
        $ports = Port::all()->where('device_id', $device_id);

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
     * Store a newly created resource in storage.
     */
    public function createConnection(int $interface_id, int $interface_id2)
    {
        $interface = InterfaceOfDevice::findOrFail($interface_id);
        $interface->update(['interface_id2' => $interface_id2]);

        $interface2 = InterfaceOfDevice::findOrFail($interface_id2);
        $interface2->update(['interface_id2' => $interface_id]);
        return $interface2;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function connection(int $s)
    {
        $interfaces = InterfaceOfDevice::all();

        $switchInterfaces = $interfaces->where('type', 'switch');
        $routerInterfaces = $interfaces->where('type', 'router')->where('AN', '!=', 'WAN')->where('connector', $switchInterfaces->first()->connector);
        $EDInterfaces = $interfaces->where('type', 'ED')->where('connector', $switchInterfaces->first()->connector);

        //return $EDInterfaces->keys()->first();


        $prev_sw = 0;
        $si =  $switchInterfaces->keys()->first();
        $ri = $routerInterfaces->keys()->first();
        $ei = $EDInterfaces->keys()->first();
        //return $ei;
        for ($i = $si; $i < (count($EDInterfaces) + $s + $si); $i++) {
            if (($switchInterfaces[$i]->id) != $prev_sw) {
                $this->createConnection($switchInterfaces[$i]->interface_id, $routerInterfaces[$ri]->interface_id);
                $ri++;
            } else {
                $this->createConnection($switchInterfaces[$i]->interface_id, $EDInterfaces[$ei]->interface_id);
                $ei++;
            }

            $prev_sw = $switchInterfaces[$i]->id;
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
