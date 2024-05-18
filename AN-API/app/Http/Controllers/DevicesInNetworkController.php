<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicesInNetwork;
use App\Models\InterfaceOfDevice;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function PHPSTORM_META\type;

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
            'userConnection' => 'required',
            'networkTraffic' => 'required',
        ]);

        $users = $request->users; // 20, 40, 60, ...
        $vlans = $request->vlans; // yes, no
        $user_connection = $request->userConnection; // 100, 1000, 10000
        $network_traffic = $request->networkTraffic; // small, medium, large

        $chosenDevices = $this->choose($users, $vlans, $user_connection, $network_traffic);

        // ziska najvacsi id v tabulke devices_in_networks pre pripad, ze by uz boli v tabulke nejake zaznamy
        $maxDeviceID = DevicesInNetwork::max('id');

        // vlozi do tabulky devices_in_networks udaje obsiahnute v poli $devicesArray
        // je pouzita metoda insert, pretoze su vkladane naraz viacere zaznamy a insert je rychlejsi ako createMany
        DB::table('devices_in_networks')->insert($chosenDevices);

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
                        'direction' => $portValue->direction,
                        'id' => $deviceValue->id,
                        'type' => $deviceValue->type,
                    ];
                }
            }
        }

        $maxInterfaceID = InterfaceOfDevice::max('interface_id');

        // vlozi do tabulky interface_of_devices udaje obsiahnute v poli $interfacesArray
        DB::table('interface_of_devices')->insert($interfacesArray);

        $interfaces = InterfaceOfDevice::all()->where('interface_id', '>', $maxInterfaceID);

        $access_switches = $devices->where('type', 'accessSwitch');

        $number_of_AS = $access_switches->count();

        $AS_uplink_interfaces = $interfaces
            ->where('type', 'accessSwitch')
            ->where('direction', 'uplink')
            ->groupBy('id')
            ->values();

        $AS_downlink_interfaces = $interfaces
            ->where('type', 'accessSwitch')
            ->where('direction', 'downlink')
            ->groupBy('id')
            ->values();

        if ($number_of_AS <= 3) {
            foreach ($access_switches as $index => $access_switch) {
                $router_interfaces = $interfaces
                    ->where('type', 'router')
                    ->where('AN', '!=', 'WAN')
                    ->where('connector', $AS_uplink_interfaces[$index - 1][0]->connector);

                $connectionsArray[] = [
                    'interface_id1' => $router_interfaces->pluck('interface_id')[$index - 1],
                    'interface_id2' => $AS_uplink_interfaces[$index - 1][0]->interface_id,
                    'device_id1' => $router_interfaces->pluck('id')[$index - 1],
                    'device_id2' => $access_switch->id,
                    'name1' => $router_interfaces->pluck('name')[$index - 1],
                    'name2' => $AS_uplink_interfaces[$index - 1][0]->name,
                ];
            }
        } else {
            $distribution_switches = $devices->where('type', 'distributionSwitch');

            $number_of_DS = $distribution_switches->count();

            $DS_uplink_interfaces = $interfaces
                ->where('type', 'distributionSwitch')
                ->where('direction', 'uplink')
                ->groupBy('id')
                ->values();

            $DS_downlink_interfaces = $interfaces
                ->where('type', 'distributionSwitch')
                ->where('direction', 'downlink')
                ->groupBy('id')
                ->values();

            // vytvorenie spojenia medzi dvomi distribucnymi switchmi
            // musime ziskat prvy uplink port kazdeho distribucneho switcha
            // iterujeme po 2 prvkoch, pretoze vzdy potrebujeme 2 distribucne switche
            for ($i = 0; $i < $number_of_DS; ++$i) {
                $connectionsArray[] = [
                    'interface_id1' => $DS_uplink_interfaces[$i][0]->interface_id,
                    'interface_id2' => $DS_uplink_interfaces[$i + 1][0]->interface_id,
                    'device_id1' => $DS_uplink_interfaces[$i][0]->id,
                    'device_id2' => $DS_uplink_interfaces[$i + 1][0]->id,
                    'name1' => $DS_uplink_interfaces[$i][0]->name,
                    'name2' => $DS_uplink_interfaces[$i + 1][0]->name,
                ];
                ++$i;
            }

            if ($devices->where('type', 'coreSwitch')->first()) {
                // vytvaranie spojenia medzi core switchmi, pouziju sa prve
                // dostupne uplink porty kazdeho core switcha
                $core_switches = $devices->where('type', 'coreSwitch');

                $number_of_CS = $core_switches->count();

                $CS_uplink_interfaces = $interfaces
                    ->where('type', 'coreSwitch')
                    ->where('direction', 'uplink')
                    ->groupBy('id')
                    ->values();

                $CS_downlink_interfaces = $interfaces
                    ->where('type', 'coreSwitch')
                    ->where('direction', 'downlink')
                    ->groupBy('id')
                    ->values();

                $connectionsArray[] = [
                    'interface_id1' => $CS_uplink_interfaces[0][0]->interface_id,
                    'interface_id2' => $CS_uplink_interfaces[1][0]->interface_id,
                    'device_id1' => $CS_uplink_interfaces[0][0]->id,
                    'device_id2' => $CS_uplink_interfaces[1][0]->id,
                    'name1' => $CS_uplink_interfaces[0][0]->name,
                    'name2' => $CS_uplink_interfaces[1][0]->name,
                ];

                // vkladanie routra, spoja sa prve downlink porty kazdeho core
                // switcha s prvnymi uplink portami routra, ktore su rovnakeho
                // typu (SFP+ a SFP28 su kompatibilne)
                $router_interfaces = $interfaces->where('type', 'router')->where('connector', 'SFP+');

                for ($i = 0; $i < $number_of_CS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $router_interfaces->pluck('interface_id')[$i],
                        'interface_id2' => $CS_downlink_interfaces[$i][0]->interface_id,
                        'device_id1' => $router_interfaces->pluck('id')[$i],
                        'device_id2' => $CS_downlink_interfaces[$i][0]->id,
                        'name1' => $router_interfaces->pluck('name')[$i],
                        'name2' => $CS_downlink_interfaces[$i][0]->name,
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi core switchmi a distribucnymi switchmi
                // musime ziskat prvy uplink port kazdeho distribution switcha

                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $CS_downlink_interfaces[0][$i + 1]->interface_id,
                        'interface_id2' => $DS_uplink_interfaces[$i][0]->interface_id,
                        'device_id1' => $CS_downlink_interfaces[0][$i + 1]->id,
                        'device_id2' => $DS_uplink_interfaces[$i][0]->id,
                        'name1' => $CS_downlink_interfaces[0][$i + 1]->name,
                        'name2' => $DS_uplink_interfaces[$i][0]->name,
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $CS_downlink_interfaces[1][$i + 1]->interface_id,
                        'interface_id2' => $DS_uplink_interfaces[$i][1]->interface_id,
                        'device_id1' => $CS_downlink_interfaces[1][$i + 1]->id,
                        'device_id2' => $DS_uplink_interfaces[$i][1]->id,
                        'name1' => $CS_downlink_interfaces[1][$i + 1]->name,
                        'name2' => $DS_uplink_interfaces[$i][1]->name,
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi distribucnymi
                // switchmi a access switchmi
                // musime ziskat prvy uplink port kazdeho access switcha, ale
                // nemozeme spojit kazdy access switch s kazdym distribucnym,
                // len urcite skupiny access k urcitym distribucnym

                $di = 0; // distribution switch index
                $dip = 0; // distribution switch port index

                $AS_per_DS = ceil($number_of_AS / $number_of_DS * 2); // determines how many access switches are connected to one distribution switch

                for ($i = 0; $i < $number_of_AS; ++$i) {
                    if ($dip == $AS_per_DS) {
                        $dip = 0;
                        $di += 2;
                    }

                    $connectionsArray[] = [
                        'interface_id1' => $DS_downlink_interfaces[$di][$dip]->interface_id,
                        'interface_id2' => $AS_uplink_interfaces[$i][0]->interface_id,
                        'device_id1' => $DS_downlink_interfaces[$di][$dip]->id,
                        'device_id2' => $AS_uplink_interfaces[$i][0]->id,
                        'name1' => $DS_downlink_interfaces[$di][$dip]->name,
                        'name2' => $AS_uplink_interfaces[$i][0]->name,
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $DS_downlink_interfaces[$di + 1][$dip]->interface_id,
                        'interface_id2' => $AS_uplink_interfaces[$i][1]->interface_id,
                        'device_id1' => $DS_downlink_interfaces[$di + 1][$dip]->id,
                        'device_id2' => $AS_uplink_interfaces[$i][1]->id,
                        'name1' => $DS_downlink_interfaces[$di + 1][$dip]->name,
                        'name2' => $AS_uplink_interfaces[$i][1]->name,
                    ];
                    ++$dip;
                }
            } else {
                // vytvaranie prepojeni medzi routrom a distribucnymi switchmi
                $router_interfaces = $interfaces->where('type', 'router')->where('connector', $DS_downlink_interfaces[0][0]->connector);

                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $router_interfaces->pluck('interface_id')[$i],
                        'interface_id2' => $DS_downlink_interfaces[$i][0]->interface_id,
                        'device_id1' => $router_interfaces->pluck('id')[$i],
                        'device_id2' => $DS_downlink_interfaces[$i][0]->id,
                        'name1' => $router_interfaces->pluck('name')[$i],
                        'name2' => $DS_downlink_interfaces[$i][0]->name,
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi distribucnymi switchmi a access switchmi
                // musime ziskat prvy uplink port kazdeho access switcha
                for ($i = 0; $i < $number_of_AS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $DS_downlink_interfaces[0][$i + 1]->interface_id,
                        'interface_id2' => $AS_uplink_interfaces[$i][0]->interface_id,
                        'device_id1' => $DS_downlink_interfaces[0][$i + 1]->id,
                        'device_id2' => $AS_uplink_interfaces[$i][0]->id,
                        'name1' => $DS_downlink_interfaces[0][$i + 1]->name,
                        'name2' => $AS_uplink_interfaces[$i][0]->name,
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $DS_downlink_interfaces[1][$i + 1]->interface_id,
                        'interface_id2' => $AS_uplink_interfaces[$i][1]->interface_id,
                        'device_id1' => $DS_downlink_interfaces[1][$i + 1]->id,
                        'device_id2' => $AS_uplink_interfaces[$i][1]->id,
                        'name1' => $DS_downlink_interfaces[1][$i + 1]->name,
                        'name2' => $AS_uplink_interfaces[$i][1]->name,
                    ];
                }
            }
        }
        // dalej potrebujeme vytvorit spojenie medzi access switchmi a end
        // devices

        $ED_interfaces = $interfaces->where('type', 'ED')->where('connector', $AS_downlink_interfaces[0][0]->connector);

        $AS_downlink_interfaces = $interfaces->where('type', 'accessSwitch')->where('direction', 'downlink')->values();

        $ei = $ED_interfaces->keys()->first();
        $lastED = $ED_interfaces->keys()->last();

        foreach ($AS_downlink_interfaces as $key => $value) {
            if ($ei > $lastED) {
                break; // code...
            }

            $connectionsArray[] = [
                'interface_id1' => $value->interface_id,
                'interface_id2' => $ED_interfaces[$ei]->interface_id,
                'device_id1' => $value->id,
                'device_id2' => $ED_interfaces[$ei]->id,
                'name1' => $value->name,
                'name2' => $ED_interfaces[$ei]->name,
            ];
            ++$ei;
        }

        DB::table('connections')->insert($connectionsArray);

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
     * Selects switch.
     *
     * @param int    $number_of_downlink_ports number of downlink ports
     * @param int    $number_of_uplink_ports   number of uplink ports
     * @param int    $speed                    speed of the downlink ports
     * @param        $downlink_connector       connector of the downlink ports
     * @param float  $oversubscription         oversubscription ratio between
     *                                         uplink bw and downlink bw
     * @param        $switch_ports             Collection of ports of the switch
     * @param        $devices                  Collection of all devices
     * @param string $type                     type of the device
     */
    public function chooseSwitch(
        int $number_of_downlink_ports,
        int $number_of_uplink_ports,
        int $speed,
        $downlink_connector,
        float $oversubscription,
        $switch_ports,
        $devices,
        string $type
    ) {
        $downlink_forwarding_rate = 0.001488 * $speed * $number_of_downlink_ports;
        $downlink_switching_capacity = 2 * $speed * $number_of_downlink_ports / 1000;

        $downlink_bw = $number_of_downlink_ports * $speed;
        $uplink_bw = $downlink_bw * $oversubscription;

        $switch_by_uplink_ports = $switch_ports
            ->where('direction', 'uplink')
            ->where('speed', '>=', $uplink_bw)
            ->sortBy('speed');

        if ($switch_by_uplink_ports->isEmpty()) {
            return json_encode(['error' => 'No access switch with required uplink speed']);
        }

        $uplink_speed = $switch_by_uplink_ports->first()->speed;

        $uplink_forwarding_rate = 0.001488 * $uplink_speed * $number_of_uplink_ports;
        $uplink_switching_capacity = 2 * $uplink_speed * $number_of_uplink_ports / 1000;

        $forwarding_rate = $downlink_forwarding_rate + $uplink_forwarding_rate;
        $switching_capacity = $downlink_switching_capacity + $uplink_switching_capacity;

        $switch_ID_by_downlink_ports = $switch_ports
            ->where('direction', 'downlink')
            ->where('number_of_ports', '>=', $number_of_downlink_ports)
            ->where('speed', '>=', $speed)
            ->whereIn('connector', $downlink_connector)
            ->pluck('device_id');

        $switch = $devices
            ->where('type', $type)
            ->whereIn('device_id', $switch_ID_by_downlink_ports)
            ->whereIn('device_id', $switch_by_uplink_ports->pluck('device_id'))
            ->where('s-forwarding_rate', '>=', $forwarding_rate)
            ->where('s-switching_capacity', '>=', $switching_capacity)
            ->sortBy('s-forwarding_rate')
            ->first();

        return [$switch, $uplink_speed];
    }

    /**
     * Selects devices based on user input.
     */
    public function choose(/* Request $request */ int $users, string $vlans, int $user_connection, string $network_traffic)
    {
        /* $request->validate([
            'users' => 'required',
            'vlans' => 'required',
            'userConnection' => 'required',
            'networkTraffic' => 'required',
        ]);

        $users = $request->users; // 20, 40, 60, ...
        $vlans = $request->vlans; // yes, no
        $user_connection = $request->userConnection; // 100, 1000, 10000
        $network_traffic = $request->networkTraffic; // small, medium, large */

        $network_users = $users;

        $devices = Device::all();
        $ports = Port::all();

        // end devices
        $ED_ports = $ports->where('type', 'ED');

        $ED = $ED_ports->where('speed', '>=', $user_connection)->first()->device_id;

        for ($i = 1; $i <= $network_users; ++$i) {
            $ED_Array[] = [
                'name' => "ED{$i}",
                'type' => 'ED',
                'device_id' => $ED,
            ];
        }

        $ED_IDs = collect($ED_Array)
            ->pluck('device_id');

        $ED_connector = $ED_ports
            ->whereIn('device_id', $ED_IDs)
            ->pluck('connector');

        // access swithes
        $access_switches = $devices
            ->where('type', 'accessSwitch')
            ->where('s-L3', 'no')
            ->where('s-vlan', $vlans);

        $AS_ports = $ports
            ->where('type', 'accessSwitch')
            ->where('speed', '>=', $user_connection)
            ->whereIn('device_id', $access_switches
            ->pluck('device_id'));

        $AS_max_downlink_ports = $AS_ports
            ->where('direction', 'downlink')
            ->max('number_of_ports');

        $s = 0;

        do {
            ++$s;
            if ($users - $AS_max_downlink_ports >= 0) {
                $number_of_ports = $AS_max_downlink_ports;
                $users -= $AS_max_downlink_ports;
            } elseif ($users - $AS_max_downlink_ports < 0) {
                if ($users > 24) {
                    $number_of_ports = $AS_max_downlink_ports;
                } elseif ($users > 16) {
                    $number_of_ports = 24;
                } else {
                    $number_of_ports = 16;
                }
                $users -= $number_of_ports;
            }

            $switch_and_uplink_speed = $this->chooseSwitch(
                $number_of_ports,
                2,
                $user_connection,
                $ED_connector,
                1 / 20,
                $AS_ports,
                $devices,
                'accessSwitch',
            );

            $access_switch = $switch_and_uplink_speed[0];
            $AS_uplink_speed = $switch_and_uplink_speed[1];

            // este doplnit error, ak nenajde zariadenie

            $AS_Array[] = [
                'name' => "AS{$s}",
                'type' => 'accessSwitch',
                'device_id' => $access_switch->device_id,
            ];
        } while ($users > 0);

        $AS_IDs = collect($AS_Array)
            ->pluck('device_id');

        $AS_uplink_connector = $AS_ports
            ->whereIn('device_id', $AS_IDs)
            ->where('direction', 'uplink')
            ->pluck('connector');

        if (count($AS_Array) <= 3) {
            // router
            $AS_uplink_port = $AS_uplink_connector->first();

            $router_ports = $ports
                ->where('type', 'router')
                ->where('AN', '!=', 'WAN')
                ->where('number_of_ports', '>=', count($AS_Array))
                ->filter(function ($port) use ($AS_uplink_port) {
                    return substr($port->connector, 0, 3) === substr($AS_uplink_port, 0, 3);
                });

            $router_device = $devices
                ->whereIn('device_id', $router_ports->pluck('device_id'))
                ->sortBy('r-throughput')
                ->first();

            // taketo nieco by malo byt vratane, pri vsetkych pripadoch ak nenajde zariadenie
            /* if ($router_device->isEmpty()) {
                return json_encode(['error' => 'No router with required ports']);
            } */

            /* switch ($network_traffic) {
                case 'small':
                    $router_device = $router_device
                        ->where('r-throughput', $router_device->min('r-throughput'))
                        ->first();
                    break;
                case 'medium':
                    $router_device = $router_device
                        ->where('r-throughput', $router_device->min('r-throughput'))
                        ->first();
                    break;
                case 'large':
                    $router_device = $router_device
                        ->where('r-throughput', $router_device->max('r-throughput'))
                        ->first();
                    break;
            } */

            $R_Array[] = [
                'name' => 'R1',
                'type' => $router_device->type,
                'device_id' => $router_device->device_id,
            ];
        } else {
            // distribution switches

            // pouzijeme 8, pretoze jeden distribucny switch moze obsluhovat 8 access
            // switchov, toto je len urceny parameter, nie je to podmienka
            $AS_per_DS = 8;

            if (count($AS_Array) <= $AS_per_DS) {
                $AS_count = count($AS_Array) + 2;
            } else {
                $AS_count = $AS_per_DS;
            }

            $DS_ports = $ports
                ->where('type', 'distributionSwitch');

            if (count($AS_Array) <= $AS_per_DS) {
                $number_of_uplink_ports = 1;
            } else {
                $number_of_uplink_ports = 3;
            }

            $switch_and_uplink_speed = $this->chooseSwitch(
                $AS_count,
                $number_of_uplink_ports,
                $AS_uplink_speed,
                $AS_uplink_connector,
                1 / 4,
                $DS_ports,
                $devices,
                'distributionSwitch',
            );

            $distribution_switch = $switch_and_uplink_speed[0];
            $DS_uplink_speed = $switch_and_uplink_speed[1];

            $DS_downlink_connector = $ports
                ->where('type', 'distributionSwitch')
                ->where('direction', 'downlink')
                ->where('device_id', $distribution_switch->device_id)
                ->pluck('connector')
                ->first();

            if (count($AS_Array) <= $AS_per_DS) {
                for ($i = 1; $i <= 2; ++$i) {
                    $DS_Array[] = [
                        'name' => "DS{$i}",
                        'type' => 'distributionSwitch',
                        'device_id' => $distribution_switch->device_id,
                    ];
                }

                // router
                $router_ports = $ports
                    ->where('type', 'router')
                    ->where('AN', '!=', 'LAN')
                    ->where('number_of_ports', '>=', 3)
                    ->where('connector', $DS_downlink_connector)
                    ->pluck('device_id');

                $router_device = $devices
                    ->whereIn('device_id', $router_ports)
                    ->sortByDesc('r-throughput');

                $router_device = $router_device
                    ->where('r-throughput', $router_device->median('r-throughput'))
                    ->first();

                $R_Array[] = [
                    'name' => 'R1',
                    'type' => $router_device->type,
                    'device_id' => $router_device->device_id,
                ];
            } else {
                // potrebujem zistit kolko distribucnych celkov (celok su 2 DS)
                // potrebujem, urcilo sa, ze jeden celok je pre 8 AS
                $number_of_distributions = ceil(count($AS_Array) / 8);

                for ($i = 1; $i <= $number_of_distributions * 2; ++$i) {
                    $DS_Array[] = [
                        'name' => "DS{$i}",
                        'type' => 'distributionSwitch',
                        'device_id' => $distribution_switch->device_id,
                    ];
                }

                $DS_IDs = collect($DS_Array)
                    ->pluck('device_id');

                $DS_uplink_connector = $DS_ports
                    ->whereIn('device_id', $DS_IDs)
                    ->where('direction', 'uplink')
                    ->pluck('connector');

                // core switches
                $DS_count = count($DS_Array);

                $CS_ports = $ports
                    ->where('type', 'coreSwitch');

                $switch_and_uplink_speed = $this->chooseSwitch(
                    $DS_count,
                    1,
                    $DS_uplink_speed,
                    $DS_uplink_connector,
                    1 / 4,
                    $CS_ports,
                    $devices,
                    'coreSwitch',
                );

                $core_switch = $switch_and_uplink_speed[0];
                $CS_uplink_speed = $switch_and_uplink_speed[1];

                $CS_downlink_connector = $ports
                    ->where('type', 'coreSwitch')
                    ->where('direction', 'downlink')
                    ->where('device_id', $core_switch->device_id)
                    ->pluck('connector')
                    ->first();

                // treba osetrit, ked je uplink_bw vacsi ako uplink port speed core
                // switcha aby vratil error alebo nejako skombinoval viac uplink portov

                for ($i = 1; $i <= 2; ++$i) {
                    $CS_Array[] = [
                        'name' => "CS{$i}",
                        'type' => 'coreSwitch',
                        'device_id' => $core_switch->device_id,
                    ];
                }

                // router
                $router_ports = $ports
                    ->where('type', 'router')
                    ->where('AN', '!=', 'WAN')
                    ->where('number_of_ports', '>=', 3)
                    ->filter(function ($port) use ($CS_downlink_connector) {
                        return substr($port->connector, 0, 3) === substr($CS_downlink_connector, 0, 3);
                    });

                $router_device = $devices
                    ->whereIn('device_id', $router_ports->pluck('device_id'))
                    ->sortByDesc('r-throughput')
                    ->first();

                $R_Array[] = [
                    'name' => 'R1',
                    'type' => $router_device->type,
                    'device_id' => $router_device->device_id,
                ];
            }

            /* // prebieha filtracia routerov podla poctu pouzivatelov
            switch ($network_users) {
                case $network_users <= 50:
                    $router_devices = $router_devices->where('r-branch', 'small');
                    break;

                case $network_users <= 150:
                    $router_devices = $router_devices->where('r-branch', 'medium');
                    break;

                default:
                    $router_devices = $router_devices->where('r-branch', 'large');
                    break;
            }

            // prebieha filtracia routerov podla vyťaženosti siete (prenosovej rychlosti)
            switch ($networkTraffic) {
                case 'small':
                    $router_devices = $router_devices->where('r-throughput', $router_devices->min('r-throughput'));
                    break;
                case 'medium':
                    $router_devices = $router_devices->where('r-throughput', $router_devices->median('r-throughput'));
                    break;
                case 'large':
                    $router_devices = $router_devices->where('r-throughput', $router_devices->max('r-throughput'));
                    break;

                default:
                    // code...
                    break;
            }

            $R_Array[] = [
                'name' => 'R1',
                'type' => $router_devices->first()->type,
                'device_id' => $router_devices->first()->device_id,
            ]; */
        }
        if (count($AS_Array) <= 3) {
            $devicesArray = array_merge($R_Array, $AS_Array, $ED_Array);
        } elseif (count($AS_Array) <= $AS_per_DS) {
            $devicesArray = array_merge($R_Array, $DS_Array, $AS_Array, $ED_Array);
        } else {
            $devicesArray = array_merge($R_Array, $CS_Array, $DS_Array, $AS_Array, $ED_Array);
        }

        return $devicesArray;
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
        // DevicesInNetwork::getQuery()->delete();
        Schema::disableForeignKeyConstraints();
        DevicesInNetwork::truncate();
        Schema::enableForeignKeyConstraints();

        return json_encode([]);
    }
}
