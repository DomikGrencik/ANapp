<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicesInNetwork;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'userConnection' => 'required',
        ]);

        $users = $request->users; //20, 40, 60, ...
        $vlans = $request->vlans;
        $IPaddr = $request->IPaddr;
        $userConnection = $request->userConnection; //FE, 1GE, 10GE


        $name = 'R3';
        $type = 'router';
        $device_id = '1';

        //$this->chooseDevice($type, $users, $vlans, $userConnection);

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
     * Store a newly created resource in storage.
     */
    public function chooseDevice(Request $request)
    {
        $request->validate([
            'users' => 'required',
            'vlans' => 'required',
            'type' => 'required',
            'userConnection' => 'required',
        ]);

        $users = $request->users; //20, 40, 60, ...
        $vlans = $request->vlans;
        $type = $request->type;
        $userConnection = $request->userConnection; //100, 1000, 10000
        //pre router potrebujem dalsi parameter throughput

        $ports = Port::all();
        $routerPorts = $ports->where('type', 'router');
        $switchPorts = $ports->where('type', 'switch');
        $EDPorts = $ports->where('type', 'ED');
        $devices = array();

        $router = $routerPorts->where('AN', '!=', 'WAN')->where('speed', '>=', $userConnection);
        $router_id = null;

        foreach ($router as $key => $value) {
            $router_id = $value->device_id;
        }

        $router = $router->where('device_id', $router_id);
        return $router;

        foreach ($router as $key => $value) {
            $router_id = $value->device_id;
        }

        array_push($devices, $router_id);

        $switch = $switchPorts->where('uplink_downlink', 'UL')->where('speed', '>=', $userConnection);

        return $switch;



        /* switch ($type) {
            case 'router':
                $ports = Port::all()->where('type', $type)->where('AN', '!=', 'WAN')->where('speed', '>=', $userConnection);
                return $ports;
                break;

            case 'switch':
                $ports = Port::all()->where('type', $type);
                return $ports;
                break;

            case 'ED':
                # code...
                break;

            default:
                # code...
                break;
        } */
    }
    /**
     * Store a newly created resource in storage.
     */
    /* public function chooseDevice(string $type, string $users, string $vlans, string $userConnection)
    {
        switch ($type) {
            case 'router':
                //echo Port::all()->where('type', $type)->where('AN', 'LAN')->where('AN', 'LAN_WAN');
                $ports = DB::table('ports')->where('AN', 'LAN')->orWhere('AN', 'LAN_WAN');

                break;

            case 'switch':
                # code...
                break;

            case 'ED':
                # code...
                break;

            default:
                # code...
                break;
        }
    } */

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
