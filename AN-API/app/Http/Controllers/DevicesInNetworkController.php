<?php

namespace App\Http\Controllers;

use App\Models\DevicesInNetwork;
use App\Models\Port;
use Illuminate\Http\Request;

class DevicesInNetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DevicesInNetwork::all();
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
        //pre router potrebujem dalsi parameter throughput

        //$this->IP($users, $IPaddr);


        $name = 'R3';
        $type = 'router';
        $device_id = '1';

        $r = 1;
        $s = 1;
        $e = 1;

        $device = $this->chooseDevice($users, $vlans, $userConnection);

        $switch_id = array();

        for ($i = 0; $i < count($device); $i += 2) {
            $device_id = $device[$i];
            $type = $device[$i + 1];

            switch ($type) {
                case 'router':
                    $name = "R{$r}";
                    $r++;
                    break;
                case 'switch':
                    $name = "S{$s}";
                    $switch_id[$s-1] = $device[$i];
                    $s++;
                    break;
                case 'ED':
                    $name = "ED{$e}";
                    $e++;
                    break;

                default:
                    # code...
                    break;
            }

            $this->storeDevice($name, $type, $device_id);

            $id = DevicesInNetwork::all()->max('id');

            (new InterfaceOfDeviceController)->storeInterface($id, $device_id);
        }

        $r = $r - 1;
        $s = $s - 1;
        $e = $e - 1;

        (new InterfaceOfDeviceController)->connection($s, $switch_id, $IPaddr);

        //return $switch_id;
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
    public function chooseDevice(int $users, int $vlans, int $userConnection)
    {
        /* $request->validate([
            'users' => 'required',
            'vlans' => 'required',
            'userConnection' => 'required',
        ]);

        $users = $request->users; //20, 40, 60, ...
        $vlans = $request->vlans;
        $userConnection = $request->userConnection; //100, 1000, 10000 */
        //pre router potrebujem dalsi parameter throughput

        $ports = Port::all();
        $routerPorts = $ports->where('type', 'router');
        $switchPorts = $ports->where('type', 'switch');
        $EDPorts = $ports->where('type', 'ED');
        $devices = array();

        $router = $routerPorts->where('AN', '!=', 'WAN')->where('speed', '>=', $userConnection);

        $router_id = $router->last()->device_id;

        array_push($devices, $router_id, 'router');


        $switch = $switchPorts->where('speed', '>=', $userConnection)->whereIn('connector', $router->pluck('connector')->toArray());

        // Initialize an empty array to store the counts for each device_id
        $portCounts = [];

        // Iterate through the array and calculate the counts
        foreach ($switch as $item) {
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

        $users = $users + 1;


        if ($users <= min($portCounts)) {
            asort($portCounts);
            array_push($devices, array_search(min($portCounts), $portCounts), 'switch');
        } else {

            arsort($portCounts);

            $sum = 0;
            $prev = 100;
            foreach ($portCounts as $key => $value) {
                if ($value < $prev) {
                    do {
                        $sum += $value;
                        array_push($devices, $key, 'switch');
                    } while (($sum + $value) <= $users);
                }

                $prev = $value;

                if ($sum >= $users) {
                    break;
                }
            }
        }

        $users = $users - 1;

        $ED = $EDPorts->where('speed', '>=', $userConnection)->first()->device_id;

        for ($i = 0; $i < $users; $i++) {
            array_push($devices, $ED, 'ED');
        }

        return $devices;
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
