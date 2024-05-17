<?php

namespace App\Http\Controllers;

use App\Models\Device;
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

        $access_switches = DevicesInNetwork::all()->where('type', 'accessSwitch')->pluck('id');

        $number_of_AS = $access_switches->count();

        if ($number_of_AS <= 3) {
            $switchInterfaces = $interfaces->where('type', 'accessSwitch')->where('direction', 'uplink');

            $routerInterfaces = $interfaces->where('type', 'router')->where('AN', '!=', 'WAN')->where('connector', $switchInterfaces->first()->connector);

            for ($i = 0; $i < $number_of_AS; ++$i) {
                $AS_firtstUplinkPorts[] = $interfaces->where('type', 'accessSwitch')->where('direction', 'uplink')->where('id', $access_switches[$i])->pluck('interface_id')->first();

                $connectionsArray[] = [
                    'interface_id1' => $routerInterfaces->pluck('interface_id')[$i],
                    'interface_id2' => $AS_firtstUplinkPorts[$i],
                    'device_id1' => $routerInterfaces->pluck('id')[$i],
                    'device_id2' => $access_switches[$i],
                    'name1' => $routerInterfaces->pluck('name')[$i],
                    'name2' => $switchInterfaces->where('interface_id', $AS_firtstUplinkPorts[$i])->pluck('name')->first(),
                ];
            }
        } else {
            $distribution_switches = $devices->where('type', 'distributionSwitch');
            $DS_IDs = $distribution_switches->pluck('id');

            $number_of_DS = $distribution_switches->count();

            $distributionSwitchInterfaces = $interfaces->where('type', 'distributionSwitch');

            // vytvorenie spojenia medzi dvomi distribucnymi switchmi
            // musime ziskat prvy uplink port kazdeho distribucneho switcha
            // iterujeme po 2 prvkoch, pretoze vzdy potrebujeme 2 distribucne switche
            for ($i = 0; $i < $number_of_DS; ++$i) {
                $DS_firstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i])->pluck('interface_id')->first();
                $DS_firstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i + 1])->pluck('interface_id')->first();

                $connectionsArray[] = [
                    'interface_id1' => $DS_firstUplinkPorts[$i],
                    'interface_id2' => $DS_firstUplinkPorts[$i + 1],
                    'device_id1' => $DS_IDs[$i],
                    'device_id2' => $DS_IDs[$i + 1],
                    'name1' => $distributionSwitchInterfaces->where('interface_id', $DS_firstUplinkPorts[$i])->pluck('name')->first(),
                    'name2' => $distributionSwitchInterfaces->where('interface_id', $DS_firstUplinkPorts[$i + 1])->pluck('name')->first(),
                ];
                ++$i;
            }

            if ($devices->where('type', 'coreSwitch')->first()) {
                // vytvaranie spojenia medzi core switchmi, pouziju sa prve
                // dostupne uplink porty kazdeho core switcha
                $core_switches = $devices->where('type', 'coreSwitch');

                $number_of_CS = $core_switches->count();

                $coreSwitchInterfaces = $interfaces->where('type', 'coreSwitch');

                $CS_IDs = $core_switches->pluck('id');
                $CS_firstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $CS_IDs[0])->pluck('interface_id')->first();
                $CS_firstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $CS_IDs[1])->pluck('interface_id')->first();

                $connectionsArray[] = [
                    'interface_id1' => $CS_firstUplinkPorts[0],
                    'interface_id2' => $CS_firstUplinkPorts[1],
                    'device_id1' => $CS_IDs[0],
                    'device_id2' => $CS_IDs[1],
                    'name1' => $coreSwitchInterfaces->where('interface_id', $CS_firstUplinkPorts[0])->pluck('name')->first(),
                    'name2' => $coreSwitchInterfaces->where('interface_id', $CS_firstUplinkPorts[1])->pluck('name')->first(),
                ];

                for ($i = 0; $i < $number_of_CS; ++$i) {
                    $CS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $CS_IDs[$i])->pluck('interface_id');
                }

                // vkladanie routra, spoja sa prve downlink porty kazdeho core
                // switcha s prvnymi uplink portami routra, ktore su rovnakeho
                // typu (SFP+ a SFP28 su kompatibilne)
                $router = $devices->where('type', 'router');
                $R_IDs = $router->pluck('id');

                $routerInterfaces = $interfaces->where('type', 'router')->where('connector', 'SFP+');

                for ($i = 0; $i < $number_of_CS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $routerInterfaces->pluck('interface_id')[$i],
                        'interface_id2' => $CS_DownlinkPorts[$i][0],
                        'device_id1' => $R_IDs[0],
                        'device_id2' => $CS_IDs[$i],
                        'name1' => $routerInterfaces->pluck('name')[$i],
                        'name2' => $coreSwitchInterfaces->where('interface_id', $CS_DownlinkPorts[$i][0])->pluck('name')->first(),
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi core switchmi a distribucnymi switchmi
                // musime ziskat prvy uplink port kazdeho distribution switcha
                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $DS_firtstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i])->pluck('interface_id')->first();
                    $DS_secondUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i])->pluck('interface_id')->skip(1)->first();

                    $connectionsArray[] = [
                        'interface_id1' => $CS_DownlinkPorts[0][$i + 1],
                        'interface_id2' => $DS_firtstUplinkPorts[$i],
                        'device_id1' => $CS_IDs[0],
                        'device_id2' => $DS_IDs[$i],
                        'name1' => $coreSwitchInterfaces->where('interface_id', $CS_DownlinkPorts[0][$i + 1])->pluck('name')->first(),
                        'name2' => $distributionSwitchInterfaces->where('interface_id', $DS_firtstUplinkPorts[$i])->pluck('name')->first(),
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $CS_DownlinkPorts[1][$i + 1],
                        'interface_id2' => $DS_secondUplinkPorts[$i],
                        'device_id1' => $CS_IDs[1],
                        'device_id2' => $DS_IDs[$i],
                        'name1' => $coreSwitchInterfaces->where('interface_id', $CS_DownlinkPorts[1][$i + 1])->pluck('name')->first(),
                        'name2' => $distributionSwitchInterfaces->where('interface_id', $DS_secondUplinkPorts[$i])->pluck('name')->first(),
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi distribucnymi
                // switchmi a access switchmi
                // musime ziskat prvy uplink port kazdeho access switcha, ale
                // nemozeme spojit kazdy access switch s kazdym distribucnym,
                // len urcite skupiny access k urcitym distribucnym

                $access_switches = $devices->where('type', 'accessSwitch');
                $AS_IDs = $access_switches->pluck('id');
                $number_of_AS = $access_switches->count();

                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $DS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $DS_IDs[$i])->pluck('interface_id');
                }

                for ($i = 0; $i < $number_of_AS; ++$i) {
                    $AS_UplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $AS_IDs[$i])->pluck('interface_id');
                }

                $AS_uplink_interfaces = $interfaces->where('type', 'accessSwitch')->where('direction', 'uplink');
                $DS_downlink_interfaces = $interfaces->where('type', 'distributionSwitch')->where('direction', 'downlink');

                $di = 0; // distribution switch index
                $dip = 0; // distribution switch port index

                for ($i = 0; $i < $number_of_AS; ++$i) {
                    if ($dip == 8) {
                        $dip = 0;
                        $di += 2;
                    }

                    $connectionsArray[] = [
                        'interface_id1' => $DS_DownlinkPorts[$di][$dip],
                        'interface_id2' => $AS_UplinkPorts[$i][0],
                        'device_id1' => $DS_IDs[$di],
                        'device_id2' => $AS_IDs[$i],
                        'name1' => $DS_downlink_interfaces->where('interface_id', $DS_DownlinkPorts[$di][$dip])->pluck('name')->first(),
                        'name2' => $AS_uplink_interfaces->where('interface_id', $AS_UplinkPorts[$i][0])->pluck('name')->first(),
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $DS_DownlinkPorts[$di + 1][$dip],
                        'interface_id2' => $AS_UplinkPorts[$i][1],
                        'device_id1' => $DS_IDs[$di + 1],
                        'device_id2' => $AS_IDs[$i],
                        'name1' => $DS_downlink_interfaces->where('interface_id', $DS_DownlinkPorts[$di + 1][$dip])->pluck('name')->first(),
                        'name2' => $AS_uplink_interfaces->where('interface_id', $AS_UplinkPorts[$i][1])->pluck('name')->first(),
                    ];
                    ++$dip;
                }
            } else {
                $access_switches = $devices->where('type', 'accessSwitch');
                $AS_IDs = $access_switches->pluck('id');

                $number_of_AS = $access_switches->count();

                $accessSwitchInterfaces = $interfaces->where('type', 'accessSwitch');

                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $DS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $DS_IDs[$i])->pluck('interface_id');
                }

                // vkladanie routra
                $router = $devices->where('type', 'router');
                $R_IDs = $router->pluck('id');

                $routerInterfaces = $interfaces->where('type', 'router')->where('connector', $distributionSwitchInterfaces->where('direction', 'downlink')->first()->connector);

                for ($i = 0; $i < $number_of_DS; ++$i) {
                    $connectionsArray[] = [
                        'interface_id1' => $routerInterfaces->pluck('interface_id')[$i],
                        'interface_id2' => $DS_DownlinkPorts[$i][0],
                        'device_id1' => $R_IDs[0],
                        'device_id2' => $DS_IDs[$i],
                        'name1' => $routerInterfaces->pluck('name')[$i],
                        'name2' => $distributionSwitchInterfaces->where('interface_id', $DS_DownlinkPorts[$i][0])->pluck('name')->first(),
                    ];
                }

                // dalej potrebujeme vytvorit spojenie medzi distribucnymi switchmi a access switchmi
                // musime ziskat prvy uplink port kazdeho access switcha
                for ($i = 0; $i < $number_of_AS; ++$i) {
                    $AS_firtstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $AS_IDs[$i])->pluck('interface_id')->first();
                    $AS_secondUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $AS_IDs[$i])->pluck('interface_id')->skip(1)->first();

                    $connectionsArray[] = [
                        'interface_id1' => $DS_DownlinkPorts[0][$i + 1],
                        'interface_id2' => $AS_firtstUplinkPorts[$i],
                        'device_id1' => $DS_IDs[0],
                        'device_id2' => $AS_IDs[$i],
                        'name1' => $distributionSwitchInterfaces->where('interface_id', $DS_DownlinkPorts[0][$i + 1])->pluck('name')->first(),
                        'name2' => $accessSwitchInterfaces->where('interface_id', $AS_firtstUplinkPorts[$i])->pluck('name')->first(),
                    ];
                    $connectionsArray[] = [
                        'interface_id1' => $DS_DownlinkPorts[1][$i + 1],
                        'interface_id2' => $AS_secondUplinkPorts[$i],
                        'device_id1' => $DS_IDs[1],
                        'device_id2' => $AS_IDs[$i],
                        'name1' => $distributionSwitchInterfaces->where('interface_id', $DS_DownlinkPorts[1][$i + 1])->pluck('name')->first(),
                        'name2' => $accessSwitchInterfaces->where('interface_id', $AS_secondUplinkPorts[$i])->pluck('name')->first(),
                    ];
                }
            }
        }
        // dalej potrebujeme vytvorit spojenie medzi access switchmi a end
        // devices

        $accessSwitchInterfaces = $interfaces->where('type', 'accessSwitch');

        $AS_DownlinkPorts = $accessSwitchInterfaces->where('direction', 'downlink');

        $EDInterfaces = $interfaces->where('type', 'ED')->where('connector', $AS_DownlinkPorts->first()->connector);
        $ei = $EDInterfaces->keys()->first();
        $lastED = $EDInterfaces->keys()->last();

        foreach ($AS_DownlinkPorts as $key => $value) {
            if ($ei > $lastED) {
                break; // code...
            }

            $connectionsArray[] = [
                'interface_id1' => $value->interface_id,
                'interface_id2' => $EDInterfaces[$ei]->interface_id,
                'device_id1' => $value->id,
                'device_id2' => $EDInterfaces[$ei]->id,
                'name1' => $value->name,
                'name2' => $EDInterfaces[$ei]->name,
            ];
            ++$ei;
        }

        DB::table('connections')->insert($connectionsArray);

        /* $distributionSwitches = DevicesInNetwork::all()->where('type', 'distributionSwitch')->pluck('id');

        $numberOfDistributionSwitches = $distributionSwitches->count();

        $accessSwitchInterfaces = $interfaces->where('type', 'accessSwitch');

        foreach ($distributionSwitches as $key => $value) {
            $dsi[] = $interfaces->where('type', 'distributionSwitch')->where('connector', $accessSwitchInterfaces->first()->connector)->where('id', $value)->keys()->first();
        }

        $distributionSwitchInterfaces = $interfaces->where('type', 'distributionSwitch')->where('connector', $accessSwitchInterfaces->first()->connector);

        $routerInterfaces = $interfaces->where('type', 'router')->where('AN', '!=', 'WAN')->where('connector', $distributionSwitchInterfaces->first()->connector);

        $EDInterfaces = $interfaces->where('type', 'ED')->where('connector', $accessSwitchInterfaces->first()->connector);

        $prev_sw_id = 0;

        // hodnoty $si, $ri, $ei su indexy v poliach $switchInterfaces, $routerInterfaces, $EDInterfaces pre ziskanie prveho interface routru, switchu a end device
        $asi = $accessSwitchInterfaces->keys()->first();
        print_r("$asi|");
        $ri = $routerInterfaces->keys()->first();
        print_r("$ri|");
        $ei = $EDInterfaces->keys()->first();
        print_r("$ei|");

        for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
            $connectionsArray[] = [
                'interface_id1' => $routerInterfaces[$ri + $i]->interface_id,
                'interface_id2' => $distributionSwitchInterfaces[$dsi[$i]]->interface_id,
                'device_id1' => $routerInterfaces[$ri + $i]->id,
                'device_id2' => $distributionSwitchInterfaces[$dsi[$i]]->id,
                'name1' => $routerInterfaces[$ri + $i]->name,
                'name2' => $distributionSwitchInterfaces[$dsi[$i]]->name,
            ];
        }

        for ($i = $asi; $i < (count($EDInterfaces) + $asi + $numberOfaccess_switches * 2); ++$i) {
            if ($accessSwitchInterfaces[$i]->id != $prev_sw_id) {
                $connectionsArray[] = [
                    'interface_id1' => $distributionSwitchInterfaces[$dsi[0] + 1]->interface_id,
                    'interface_id2' => $accessSwitchInterfaces[$i]->interface_id,
                    'device_id1' => $distributionSwitchInterfaces[$dsi[0]]->id,
                    'device_id2' => $accessSwitchInterfaces[$i]->id,
                    'name1' => $distributionSwitchInterfaces[$dsi[0] + 1]->name,
                    'name2' => $accessSwitchInterfaces[$i]->name,
                ];
                $connectionsArray[] = [
                    'interface_id1' => $distributionSwitchInterfaces[$dsi[1] + 1]->interface_id,
                    'interface_id2' => $accessSwitchInterfaces[$i + 1]->interface_id,
                    'device_id1' => $distributionSwitchInterfaces[$dsi[1]]->id,
                    'device_id2' => $accessSwitchInterfaces[$i]->id,
                    'name1' => $distributionSwitchInterfaces[$dsi[1] + 1]->name,
                    'name2' => $accessSwitchInterfaces[$i + 1]->name,
                ];
                ++$i;
                ++$dsi[0];
                ++$dsi[1];
            } else {
                $connectionsArray[] = [
                    'interface_id1' => $accessSwitchInterfaces[$i]->interface_id,
                    'interface_id2' => $EDInterfaces[$ei]->interface_id,
                    'device_id1' => $accessSwitchInterfaces[$i]->id,
                    'device_id2' => $EDInterfaces[$ei]->id,
                    'name1' => $accessSwitchInterfaces[$i]->name,
                    'name2' => $EDInterfaces[$ei]->name,
                ];
                ++$ei;
            }

            $prev_sw_id = $accessSwitchInterfaces[$i]->id;
        } */

        // DB::table('connections')->insert($connectionsArray);

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
     * Selects access switches.
     */
    /* public function accessSwitch(int $users, int $userConnection, $access_switches, $AS_ports, int $numberOfDistributionSwitches)
    {
        $AS_max_downlink_ports = $AS_ports->max('number_of_ports');
        $s = $numberOfDistributionSwitches;

        if ($users <= 150) {
            $connectedPorts = 1;
        } else {
            $connectedPorts = $numberOfDistributionSwitches;
        }
        $usersToConnect = $AS_max_downlink_ports - $connectedPorts;

        do {
            ++$s;
            if ($users - $usersToConnect >= 0) {
                $number_of_ports = $AS_max_downlink_ports;
                $users -= $usersToConnect;
            } elseif ($users - $usersToConnect < 0) {
                if ($users > 24 - $connectedPorts) {
                    $number_of_ports = $AS_max_downlink_ports;
                } elseif ($users > 15 - $connectedPorts) {
                    $number_of_ports = 24;
                } else {
                    $number_of_ports = 16;
                }
                $users -= $usersToConnect;
            }

            $forwarding_rate = 0.001488 * $userConnection * $number_of_ports;
            $switching_capacity = 2 * $userConnection * $number_of_ports / 1000;

            $switchByPorts = $AS_ports->where('number_of_ports', '>=', $number_of_ports)->pluck('device_id');

            $accessSwitch = $access_switches->whereIn('device_id', $switchByPorts)->where('s-forwarding_rate', '>=', $forwarding_rate)->where('s-switching_capacity', '>=', $switching_capacity)->sortBy('price')->first();

            $devicesArray[] = [
                'name' => "S{$s}",
                'type' => 'accessSwitch',
                'device_id' => $accessSwitch->device_id,
            ];
        } while ($users > 0);

        return $devicesArray;
    } */

    /**
     * Selects devices based on user input.
     *
     * @param int    $number_of_downlink_ports number of downlink ports
     * @param int    $number_of_uplink_ports   number of uplink ports
     * @param int    $speed                    speed of the downlink ports
     * @param float  $oversubscription         oversubscription ratio between uplink
     * @param        $switch_ports             Collection of ports of the switch
     * @param        $devices                  Collection of all devices
     * @param string $type                     type of the device
     */
    public function chooseSwitch(int $number_of_downlink_ports, int $number_of_uplink_ports, int $speed, $uplink_connector, float $oversubscription, $switch_ports, $devices, string $type)
    {
        $downlink_forwarding_rate = 0.001488 * $speed * $number_of_downlink_ports;
        $downlink_switching_capacity = 2 * $speed * $number_of_downlink_ports / 1000;

        $downlink_bw = $number_of_downlink_ports * $speed;
        $uplink_bw = $downlink_bw * $oversubscription;

        $uplink_speed = $switch_ports->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->sortBy('speed')->first();

        if ($uplink_speed == null) {
            return json_encode(['error' => 'No access switch with required uplink speed']);
        }

        $uplink_forwarding_rate = 0.001488 * $uplink_speed * $number_of_uplink_ports;
        $uplink_switching_capacity = 2 * $uplink_speed * $number_of_uplink_ports / 1000;

        $forwarding_rate = $downlink_forwarding_rate + $uplink_forwarding_rate;
        $switching_capacity = $downlink_switching_capacity + $uplink_switching_capacity;

        $switch_ID_by_downlink_ports = $switch_ports->where('direction', 'downlink')->where('number_of_ports', '>=', $number_of_downlink_ports)->where('speed', '>=', $speed)->whereIn('connector', $uplink_connector)->pluck('device_id');

        $switch = $devices->where('type', $type)->whereIn('device_id', $switch_ID_by_downlink_ports)->where('s-forwarding_rate', '>=', $forwarding_rate)->where('s-switching_capacity', '>=', $switching_capacity)->sortBy('s-forwarding_rate')->first();

        return [$switch, $uplink_speed];
    }

    /**
     * Selects devices based on user input.
     */
    public function choose(Request $request /* int $users, string $vlans, int $user_connection, string $network_traffic */)
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

        $ED_IDs = collect($ED_Array)->pluck('device_id');

        $ED_connector = $ED_ports->whereIn('device_id', $ED_IDs)->pluck('connector');

        // access swithes
        $access_switches = $devices->where('type', 'accessSwitch')->where('s-L3', 'no')->where('s-vlan', $vlans);

        $AS_ports = $ports->where('type', 'accessSwitch')->where('speed', '>=', $user_connection)->whereIn('device_id', $access_switches->pluck('device_id'));

        $AS_max_downlink_ports = $AS_ports->where('direction', 'downlink')->max('number_of_ports');
        $s = 0;

        /* if ($vlans == 'no') {
            $connectedPorts = 1;
        } else {
            $connectedPorts = 0;
        }
        $usersToConnect = $AS_max_downlink_ports - $connectedPorts; */

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

            /* $switch_ID_by_downlink_ports = $AS_ports->where('number_of_ports', '>=', $number_of_ports)->where('direction', 'downlink')->pluck('device_id'); */

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

        $AS_IDs = collect($AS_Array)->pluck('device_id');

        $AS_uplink_connector = $AS_ports->whereIn('device_id', $AS_IDs)->where('direction', 'uplink')->pluck('connector');

        if (count($AS_Array) <= 3) {
            // router
            $AS_uplink_port = $ports->where('device_id', $access_switch->device_id)->where('direction', 'uplink')->pluck('connector')->first();

            $router_ports = $ports->where('type', 'router')->where('AN', '!=', 'WAN')->where('number_of_ports', '>=', count($AS_Array))->filter(function ($port) use ($AS_uplink_port) {
                return substr($port->connector, 0, 3) === substr($AS_uplink_port, 0, 3);
            });

            $router_device = $devices->whereIn('device_id', $router_ports->pluck('device_id'));

            // taketo nieco by malo byt vratane, pri vsetkych pripadoch ak nenajde zariadenie
            if ($router_device->isEmpty()) {
                return json_encode(['error' => 'No router with required ports']);
            }

            switch ($network_traffic) {
                case 'small':
                    $router_device = $router_device->where('r-throughput', $router_device->min('r-throughput'));
                    break;
                case 'medium':
                    $router_device = $router_device->where('r-throughput', $router_device->min('r-throughput'));
                    break;
                case 'large':
                    $router_device = $router_device->where('r-throughput', $router_device->max('r-throughput'));
                    break;

                default:
                    $router_device = $router_device->where('r-throughput', $router_device->min('r-throughput'));
                    break;
            }

            $R_Array[] = [
                'name' => 'R1',
                'type' => $router_device->first()->type,
                'device_id' => $router_device->first()->device_id,
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

            /* $switch_ID_by_downlink_ports = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->whereIn('connector', $AS_uplink_connector)->pluck('device_id');
 */
            $DS_ports = $ports->where('type', 'distributionSwitch');

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

            $DS_downlink_connector = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->where('device_id', $distribution_switch->device_id)->pluck('connector')->first();

            if (count($AS_Array) <= $AS_per_DS) {
                for ($i = 1; $i <= 2; ++$i) {
                    $DS_Array[] = [
                        'name' => "DS{$i}",
                        'type' => 'distributionSwitch',
                        'device_id' => $distribution_switch->device_id,
                    ];
                }

                // router
                $router_ports = $ports->where('AN', '!=', 'LAN')->where('number_of_ports', '>=', 3)->where('connector', $DS_downlink_connector)->pluck('device_id')->first();
                $router_device = $devices->where('device_id', $router_ports);

                $R_Array[] = [
                    'name' => 'R1',
                    'type' => $router_device->first()->type,
                    'device_id' => $router_device->first()->device_id,
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

                $DS_IDs = collect($DS_Array)->pluck('device_id');

                $DS_uplink_connector = $DS_ports->whereIn('device_id', $DS_IDs)->where('direction', 'uplink')->pluck('connector');

                return $DS_uplink_connector;

                // core switches
                $DS_count = count($DS_Array);

                $CS_ports = $ports->where('type', 'coreSwitch');

                /* $switch_ID_by_downlink_ports = $ports->where('type', 'coreSwitch')->where('direction', 'downlink')->where('number_of_ports', '>=', $DS_count)->where('speed', '>=', $DS_uplink_speed)->pluck('device_id'); */

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

                return $switch_and_uplink_speed;

                /* $downlink_forwarding_rate = 0.001488 * $DS_uplink_speed * count($DS_Array);
                $downlink_switching_capacity = 2 * $DS_uplink_speed * count($DS_Array) / 1000;

                $downlink_bw = count($DS_Array) * $DS_uplink_speed;
                $oversubscription = 1 / 4;
                $uplink_bw = $downlink_bw * $oversubscription; */

                // treba osetrit, ked je uplink_bw vacsi ako uplink port speed core
                // switcha aby vratil error alebo nejako skombinoval viac uplink portov

                /* $CS_uplink_speed = $ports->where('type', 'coreSwitch')->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->sortBy('speed')->first();

                $uplink_forwarding_rate = 0.001488 * $CS_uplink_speed * 1;
                $uplink_switching_capacity = 2 * $CS_uplink_speed * 1 / 1000;

                $forwarding_rate = $downlink_forwarding_rate + $uplink_forwarding_rate;
                $switching_capacity = $downlink_switching_capacity + $uplink_switching_capacity;

                $coreSwitch = $devices->where('type', 'coreSwitch')->whereIn('device_id', $switch_ID_by_downlink_ports)->where('s-forwarding_rate', '>=', $forwarding_rate)->where('s-switching_capacity', '>=', $switching_capacity)->sortBy('s-forwarding_rate')->first(); */

                for ($i = 1; $i <= 2; ++$i) {
                    $CS_Array[] = [
                        'name' => "CS{$i}",
                        'type' => 'coreSwitch',
                        'device_id' => $core_switch->device_id,
                    ];
                }

                // router
                $router_ports = $ports->where('AN', '!=', 'LAN')->where('number_of_ports', '>=', 3)->where('connector', $DS_downlink_connector)->pluck('device_id')->first();
                $router_device = $devices->where('device_id', $router_ports);

                $R_Array[] = [
                    'name' => 'R1',
                    'type' => $router_device->first()->type,
                    'device_id' => $router_device->first()->device_id,
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
            $devicesArray = array_merge($R_Array, $AS_Array);
        } elseif (count($AS_Array) <= $AS_per_DS) {
            $devicesArray = array_merge($R_Array, $DS_Array, $AS_Array);
        } else {
            $devicesArray = array_merge($R_Array, $CS_Array, $DS_Array, $AS_Array);
        }

        // pridavanie end devices
        $EDPorts = $ports->where('type', 'ED');
        $ED = $EDPorts->where('speed', '>=', $user_connection)->first()->device_id;

        for ($i = 1; $i <= $network_users; ++$i) {
            $devicesArray[] = [
                'name' => "ED{$i}",
                'type' => 'ED',
                'device_id' => $ED,
            ];
        }

        return $devicesArray;
    }

    /**
     * Selects devices based on user input. Zakomentovana funkcia povodna.
     */
    /* public function chooseDevices(int $users, string $vlans, int $userConnection, string $networkTraffic)
    {
        $devices = Device::all();
        $ports = Port::all();

        $router_devices = $devices->where('type', 'router');

        // prebieha filtracia routerov podla poctu pouzivatelov
        switch ($users) {
            case $users <= 50:
                $router_devices = $router_devices->where('r-branch', 'small');
                break;

            case $users <= 150:
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

        $devicesArray[] = [
            'name' => 'R1',
            'type' => $router_devices->first()->type,
            'device_id' => $router_devices->first()->device_id,
        ];

        $routerId = $router_devices->first()->device_id;
        $routerConnector = $ports->where('AN', '!=', 'WAN')->where('speed', '>=', $userConnection)->where('device_id', $routerId)->pluck('connector');

        // priprava switchov

        $switchDevices = $devices->where('type', 'switch');
        $access_switches = $switchDevices->where('s-L3', 'no')->where('s-vlan', $vlans);
        $distributionSwitches = $switchDevices->where('s-L3', 'yes');
        $numberOfDistributionSwitches = 0;

        $accessSwitchIds = $access_switches->pluck('device_id');

        if ($users <= 150) {
            $AS_ports = $ports->where('type', 'switch')->whereIn('device_id', $accessSwitchIds)->whereIn('connector', $routerConnector);

            $accessSwitch = $this->accessSwitch($users, $userConnection, $access_switches, $AS_ports, $numberOfDistributionSwitches);

            $devicesArray = array_merge($devicesArray, $accessSwitch);
        } else {
            $distributionSwitchIds = $distributionSwitches->pluck('device_id');
            $distributionSwitchPorts = $ports->where('type', 'switch')->where('direction', 'uplink')->whereIn('device_id', $distributionSwitchIds)->whereIn('connector', $routerConnector);

            $distributionSwitch = $distributionSwitches->whereIn('device_id', $distributionSwitchPorts->pluck('device_id'))->last();

            $DS_downlink_connector = $distributionSwitchPorts->where('device_id', $distributionSwitch->device_id)->pluck('connector');

            for ($i = 1; $i <= 2; ++$i) {
                $devicesArray[] = [
                    'name' => "S{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
                $numberOfDistributionSwitches = $i;
            }

            $AS_ports = $ports->where('type', 'switch')->whereIn('device_id', $accessSwitchIds)->whereIn('connector', $DS_downlink_connector);

            $accessSwitch = $this->accessSwitch($users, $userConnection, $access_switches, $AS_ports, $numberOfDistributionSwitches);

            $devicesArray = array_merge($devicesArray, $accessSwitch);
        }

        // pridavanie end devices
        $EDPorts = $ports->where('type', 'ED');
        $ED = $EDPorts->where('speed', '>=', $userConnection)->first()->device_id;

        for ($i = 1; $i <= $users; ++$i) {
            $devicesArray[] = [
                'name' => "ED{$i}",
                'type' => 'ED',
                'device_id' => $ED,
            ];
        }

        return $devicesArray;
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
