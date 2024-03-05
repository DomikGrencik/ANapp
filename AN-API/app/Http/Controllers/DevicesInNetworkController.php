<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\DevicesInNetwork;
use App\Models\InterfaceOfDevice;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $users = $request->users; // 20, 40, 60, ...
        $vlans = $request->vlans;
        $IPaddr = $request->IPaddr;
        $userConnection = $request->userConnection; // FE, 1GE, 10GE
        // pre router potrebujem dalsi parameter throughput

        // $this->IP($users, $IPaddr);

        $name = 'R3';
        $type = 'router';
        $device_id = '1';

        $r = 0;
        $s = 0;
        $e = 0;

        // najprv je volaná metóda chooseDevice, ktorá vráti pole s id zariadení a ich typom
        $device = $this->chooseDevice($users, $vlans, $userConnection);

        $switch_id = [];

        for ($i = 0; $i < count($device); $i += 2) {
            $device_id = $device[$i];
            $type = $device[$i + 1];

            switch ($type) {
                case 'router':
                    $name = "R{$r}";
                    ++$r;
                    break;
                case 'switch':
                    $name = "S{$s}";
                    $switch_id[$s - 1] = $device[$i];
                    ++$s;
                    break;
                case 'ED':
                    $name = "ED{$e}";
                    ++$e;
                    break;

                default:
                    // code...
                    break;
            }

            $devicesArray[] = [
                'name' => $name,
                'type' => $type,
                'device_id' => $device_id,
            ];
        }

        // ziska najvacsi id v tabulke devices_in_networks pre pripad, ze by uz boli v tabulke nejake zaznamy
        $maxDeviceID = DevicesInNetwork::max('id');

        // vlozi do tabulky devices_in_networks udaje obsiahnute v poli $devicesArray
        // je pouzita metoda insert, pretoze su vkladane naraz viacere zaznamy a insert je rychlejsi ako createMany
        DB::table('devices_in_networks')->insert($devicesArray);

        // ziska vsetky zariadenia, ktore maju id vacsie ako $maxID
        $devices = DevicesInNetwork::all()->where('id', '>', $maxDeviceID);

        // pre kazde vlozene zariadenie zapise do pola $interfacesArray udaje o jeho portoch
        foreach ($devices as $key => $deviceValue) {
            // ziska vsetky porty zariadenia podla device_id
            $ports = Port::all()->where('device_id', $deviceValue->device_id);

            // prejde vsetky porty zariadenia a udaje zapise do pola $interfacesArray
            foreach ($ports as $key => $portValue) {
                for ($i = 0; $i < $portValue->number_of_ports; ++$i) {
                    $interfacesArray[] = [
                        'name' => "{$portValue->name}{$i}",
                        'connector' => $portValue->connector,
                        'AN' => $portValue->AN,
                        'speed' => $portValue->speed,
                        'id' => $deviceValue->id,
                        'type' => $portValue->type,
                    ];
                }
            }
        }

        $maxInterfaceID = InterfaceOfDevice::max('interface_id');

        // vlozi do tabulky interface_of_devices udaje obsiahnute v poli $interfacesArray
        DB::table('interface_of_devices')->insert($interfacesArray);

        $interfaces = InterfaceOfDevice::all()->where('interface_id', '>', $maxInterfaceID);

        // rozdeli interface podla typu zariadenia, pre router a end dvice je potrebne vyfiltrovat aj dalsie parametre, hlavne konektor, aby bol rovnak ako konektor switchu
        $switchInterfaces = $interfaces->where('type', 'switch');
        $routerInterfaces = $interfaces->where('type', 'router')->where('AN', '!=', 'WAN')->where('connector', $switchInterfaces->first()->connector);
        $EDInterfaces = $interfaces->where('type', 'ED')->where('connector', $switchInterfaces->first()->connector);

        $prev_sw_id = 0;
        $si = $switchInterfaces->keys()->first();
        $ri = $routerInterfaces->keys()->first();
        $ei = $EDInterfaces->keys()->first();

        for ($i = $si; $i < (count($EDInterfaces) + $si + $s); ++$i) {
            if ($switchInterfaces[$i]->id != $prev_sw_id) {
                $connectionsArray[] = [
                    'interface_id1' => $routerInterfaces[$ri]->interface_id,
                    'interface_id2' => $switchInterfaces[$i]->interface_id,
                    'device_id1' => $routerInterfaces[$ri]->id,
                    'device_id2' => $switchInterfaces[$i]->id,
                    'name1' => $routerInterfaces[$ri]->name,
                    'name2' => $switchInterfaces[$i]->name,
                ];
                ++$ri;
            } else {
                $connectionsArray[] = [
                    'interface_id1' => $switchInterfaces[$i]->interface_id,
                    'interface_id2' => $EDInterfaces[$ei]->interface_id,
                    'device_id1' => $switchInterfaces[$i]->id,
                    'device_id2' => $EDInterfaces[$ei]->id,
                    'name1' => $switchInterfaces[$i]->name,
                    'name2' => $EDInterfaces[$ei]->name,
                ];
                ++$ei;
            }

            $prev_sw_id = $switchInterfaces[$i]->id;
        }

        DB::table('connections')->insert($connectionsArray);

        /* $r = $r - 1;
         $s = $s - 1;
         $e = $e - 1;

         (new InterfaceOfDeviceController)->connection($s, $switch_id, $IPaddr); */

        return json_encode([]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeDevice(string $name, string $type, string $device_id)
    {
        DevicesInNetwork::create([
            'name' => $name,
            'type' => $type,
            'device_id' => $device_id,
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
        // pre router potrebujem dalsi parameter throughput

        $ports = Port::all();
        $routerPorts = $ports->where('type', 'router');
        $switchPorts = $ports->where('type', 'switch');
        $EDPorts = $ports->where('type', 'ED');
        $devices = [];

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

        ++$users;

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

        --$users;

        $ED = $EDPorts->where('speed', '>=', $userConnection)->first()->device_id;

        for ($i = 0; $i < $users; ++$i) {
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }

    /**
     * Remove all resources from storage.
     */
    public function delete()
    {
        Schema::disableForeignKeyConstraints();
        DevicesInNetwork::truncate();
        //Connection::truncate();
        Schema::enableForeignKeyConstraints();

        return json_encode([]);
    }
}
