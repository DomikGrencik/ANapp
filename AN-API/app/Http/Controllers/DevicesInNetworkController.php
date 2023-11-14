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

        $router_id = $router->last()->device_id;

        array_push($devices, $router_id);

        $switch = $switchPorts->where('uplink_downlink', 'UL')->where('speed', '>=', $userConnection)->whereIn('connector', $router->pluck('connector')->toArray());

        $switchDL = $switchPorts->where('uplink_downlink', 'DL')->whereIn('device_id', $switch->pluck('device_id')->toArray())->where('speed', '>=', $userConnection);

        // Initialize an empty array to store the counts for each device_id
        $portCounts = [];

        // Iterate through the array and calculate the counts
        foreach ($switchDL as $item) {
            $deviceId = $item['device_id'];

            // Check if the device_id is already in the portCounts array
            if (isset($portCounts[$deviceId])) {
                // If it exists, increment the count
                $portCounts[$deviceId] += $item['number_of_ports'];
            } else {
                // If it doesn't exist, initialize the count
                $portCounts[$deviceId] = $item['number_of_ports'];
            }
        }


        if ($users <= min($portCounts)) {
            asort($portCounts);
            array_push($devices, array_search(min($portCounts), $portCounts));
        } else {

            arsort($portCounts);

            $sum = 0;
            foreach ($portCounts as $key => $value) {
                do {
                    $sum += $value;
                    array_push($devices, $key);
                } while (($sum + $value) <= $users);

                if ($sum >= $users) {
                    break;
                }
            }
        }

        $ED = $EDPorts->where('speed', '>=' ,$userConnection)->first()->device_id;

        for ($i=0; $i < $users; $i++) {
            array_push($devices, $ED);
        }
        return $devices;
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
