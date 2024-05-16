<?php

namespace App\Http\Controllers;

use App\Models\Connection;
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
        $userConnection = $request->userConnection; // 100, 1000, 10000
        $networkTraffic = $request->networkTraffic; // small, medium, large

        $chosenDevices = $this->choose($users, $vlans, $userConnection, $networkTraffic);

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

        $accessSwitches = DevicesInNetwork::all()->where('type', 'accessSwitch')->pluck('id');

        $numberOfAccessSwitches = $accessSwitches->count();

        if ($users <= 150) {
            $switchInterfaces = $interfaces->where('type', 'accessSwitch');

            $routerInterfaces = $interfaces->where('type', 'router')->where('AN', '!=', 'WAN')->where('connector', $switchInterfaces->first()->connector);

            $EDInterfaces = $interfaces->where('type', 'ED')->where('connector', $switchInterfaces->first()->connector);

            $prev_sw_id = 0;

            // hodnoty $si, $ri, $ei su indexy v poliach $switchInterfaces, $routerInterfaces, $EDInterfaces pre ziskanie prveho interface routru, switchu a end device
            $si = $switchInterfaces->keys()->first();
            print_r("$si|");
            $ri = $routerInterfaces->keys()->first();
            print_r("$ri|");
            $ei = $EDInterfaces->keys()->first();
            print_r("$ei|");

            for ($i = $si; $i < (count($EDInterfaces) + $si + $numberOfAccessSwitches); ++$i) {
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
        } else {
            $distributionSwitches = $devices->where('type', 'distributionSwitch');
            $DS_IDs = $distributionSwitches->pluck('id');

            $numberOfDistributionSwitches = $distributionSwitches->count();

            $distributionSwitchInterfaces = $interfaces->where('type', 'distributionSwitch');

            // vytvorenie spojenia medzi dvomi distribucnymi switchmi
            // musime ziskat prvy uplink port kazdeho distribucneho switcha
            // iterujeme po 2 prvkoch, pretoze vzdy potrebujeme 2 distribucne switche
            for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
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
                $coreSwitches = $devices->where('type', 'coreSwitch');

                $numberOfCoreSwitches = $coreSwitches->count();

                $coreSwitchInterfaces = $interfaces->where('type', 'coreSwitch');

                $CS_IDs = $coreSwitches->pluck('id');
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

                for ($i = 0; $i < $numberOfCoreSwitches; ++$i) {
                    $CS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $CS_IDs[$i])->pluck('interface_id');
                }

                // vkladanie routra, spoja sa prve downlink porty kazdeho core
                // switcha s prvnymi uplink portami routra, ktore su rovnakeho
                // typu (SFP+ a SFP28 su kompatibilne)
                $router = $devices->where('type', 'router');
                $R_IDs = $router->pluck('id');

                $routerInterfaces = $interfaces->where('type', 'router')->where('connector', 'SFP+');

                for ($i = 0; $i < $numberOfCoreSwitches; ++$i) {
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
                for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
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

                $accessSwitches = $devices->where('type', 'accessSwitch');
                $AS_IDs = $accessSwitches->pluck('id');
                $numberOfAccessSwitches = $accessSwitches->count();

                for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
                    $DS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $DS_IDs[$i])->pluck('interface_id');
                }

                for ($i = 0; $i < $numberOfAccessSwitches; ++$i) {
                    $AS_UplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $AS_IDs[$i])->pluck('interface_id');
                }

                $AS_uplink_interfaces = $interfaces->where('type', 'accessSwitch')->where('direction', 'uplink');
                $DS_downlink_interfaces = $interfaces->where('type', 'distributionSwitch')->where('direction', 'downlink');

                $di = 0; // distribution switch index
                $dip = 0; // distribution switch port index

                for ($i = 0; $i < $numberOfAccessSwitches; ++$i) {
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
                $accessSwitches = $devices->where('type', 'accessSwitch');
                $AS_IDs = $accessSwitches->pluck('id');

                $numberOfAccessSwitches = $accessSwitches->count();

                $accessSwitchInterfaces = $interfaces->where('type', 'accessSwitch');

                for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
                    $DS_DownlinkPorts[] = $interfaces->where('direction', 'downlink')->where('id', $DS_IDs[$i])->pluck('interface_id');
                }

                // vkladanie routra
                $router = $devices->where('type', 'router');
                $R_IDs = $router->pluck('id');

                $routerInterfaces = $interfaces->where('type', 'router')->where('connector', $distributionSwitchInterfaces->where('direction', 'downlink')->first()->connector);

                for ($i = 0; $i < $numberOfDistributionSwitches; ++$i) {
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
                for ($i = 0; $i < $numberOfAccessSwitches; ++$i) {
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

            for ($i = $asi; $i < (count($EDInterfaces) + $asi + $numberOfAccessSwitches * 2); ++$i) {
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
        }

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
    public function accessSwitch(int $users, int $userConnection, $accessSwitches, $accessSwitchPorts, int $numberOfDistributionSwitches)
    {
        $maxPorts = $accessSwitchPorts->max('number_of_ports');
        $s = $numberOfDistributionSwitches;

        if ($users <= 150) {
            $connectedPorts = 1;
        } else {
            $connectedPorts = $numberOfDistributionSwitches;
        }
        $usersToConnect = $maxPorts - $connectedPorts;

        do {
            ++$s;
            if ($users - $usersToConnect >= 0) {
                $numberOfPorts = $maxPorts;
                $users -= $usersToConnect;
            } elseif ($users - $usersToConnect < 0) {
                if ($users > 24 - $connectedPorts) {
                    $numberOfPorts = $maxPorts;
                } elseif ($users > 15 - $connectedPorts) {
                    $numberOfPorts = 24;
                } else {
                    $numberOfPorts = 16;
                }
                $users -= $usersToConnect;
            }

            $forwardingRate = 0.001488 * $userConnection * $numberOfPorts;
            $switchingCapacity = 2 * $userConnection * $numberOfPorts / 1000;

            $switchByPorts = $accessSwitchPorts->where('number_of_ports', '>=', $numberOfPorts)->pluck('device_id');

            $accessSwitch = $accessSwitches->whereIn('device_id', $switchByPorts)->where('s-forwarding_rate', '>=', $forwardingRate)->where('s-switching_capacity', '>=', $switchingCapacity)->sortBy('price')->first();

            $devicesArray[] = [
                'name' => "S{$s}",
                'type' => 'accessSwitch',
                'device_id' => $accessSwitch->device_id,
            ];
        } while ($users > 0);

        return $devicesArray;
    }

    /**
     * Selects devices based on user input.
     */
    public function choose(/* Request $request */ int $users, string $vlans, int $userConnection, string $networkTraffic)
    {
        /* $request->validate([
            'users' => 'required',
            'vlans' => 'required',
            'userConnection' => 'required',
            'networkTraffic' => 'required',
        ]);

        $users = $request->users; // 20, 40, 60, ...
        $vlans = $request->vlans; // yes, no
        $userConnection = $request->userConnection; // 100, 1000, 10000
        $networkTraffic = $request->networkTraffic; // small, medium, large */

        $network_users = $users;

        $devices = Device::all();
        $ports = Port::all();

        // access swithes
        $accessSwitches = $devices->where('type', 'accessSwitch')->where('s-L3', 'no')->where('s-vlan', $vlans);

        $accessSwitchPorts = $ports->where('type', 'accessSwitch')->where('speed', '>=', $userConnection)->whereIn('device_id', $accessSwitches->pluck('device_id'));

        $maxPorts = $accessSwitchPorts->where('direction', 'downlink')->max('number_of_ports');
        $s = 0;

        if ($vlans == 'no') {
            $connectedPorts = 1;
        } else {
            $connectedPorts = 0;
        }
        $usersToConnect = $maxPorts - $connectedPorts;

        do {
            ++$s;
            if ($users - $usersToConnect >= 0) {
                $numberOfPorts = $maxPorts;
                $users -= $usersToConnect;
            } elseif ($users - $usersToConnect < 0) {
                if ($users > 24 - $connectedPorts) {
                    $numberOfPorts = $maxPorts;
                } elseif ($users > 15 - $connectedPorts) {
                    $numberOfPorts = 24;
                } else {
                    $numberOfPorts = 16;
                }
                $users -= $usersToConnect;
            }

            $downlink_switchByPorts = $accessSwitchPorts->where('number_of_ports', '>=', $numberOfPorts)->where('direction', 'downlink')->pluck('device_id');

            $downlink_forwardingRate = 0.001488 * $userConnection * $numberOfPorts;
            $downlink_switchingCapacity = 2 * $userConnection * $numberOfPorts / 1000;

            $downlink_bw = $numberOfPorts * $userConnection;
            $oversubscription = 1 / 20;
            $uplink_bw = $downlink_bw * $oversubscription;

            $AS_uplink_speed = $accessSwitchPorts->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->first();
            /* $uplink_numberOfPorts = $accessSwitchPorts->where('direction', 'uplink')->where('speed', '>=', $uplink_speed)->whereIn('device_id', $downlink_switchByPorts)->pluck('number_of_ports')->first(); */

            $uplink_fowardingRate = 0.001488 * $AS_uplink_speed * 2;
            $uplink_switchingCapacity = 2 * $AS_uplink_speed * 2 / 1000;

            $forwardingRate = $downlink_forwardingRate + $uplink_fowardingRate;
            $switchingCapacity = $downlink_switchingCapacity + $uplink_switchingCapacity;

            $accessSwitch = $accessSwitches->whereIn('device_id', $downlink_switchByPorts)->where('s-forwarding_rate', '>=', $forwardingRate)->where('s-switching_capacity', '>=', $switchingCapacity)->sortBy('s-forwarding_rate')->first();

            $AS_Array[] = [
                'name' => "AS{$s}",
                'type' => 'accessSwitch',
                'device_id' => $accessSwitch->device_id,
            ];
        } while ($users > 0);

        // distribution switches

        $AS_per_DS = 8;

        if (count($AS_Array) <= $AS_per_DS) {
            $AS_count = count($AS_Array) + 2;
        } else {
            $AS_count = $AS_per_DS;
        }

        $deviceIds = collect($AS_Array)->pluck('device_id');

        $accessSwitches_uplinkConnector = $accessSwitchPorts->whereIn('device_id', $deviceIds)->where('direction', 'uplink')->pluck('connector');

        $distributionSwitchPorts = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->whereIn('connector', $accessSwitches_uplinkConnector)->pluck('device_id');

        // pouzijeme 8, pretoze jeden distribucny switch moze obsluhovat 8 access
        // switchov, toto je len urceny parameter, nie je to podmienka
        $downlink_forwardingRate = 0.001488 * $AS_uplink_speed * $AS_count;
        $downlink_switchingCapacity = 2 * $AS_uplink_speed * $AS_count / 1000;

        $downlink_bw = $AS_count * $AS_uplink_speed;
        $oversubscription = 1 / 4;
        $uplink_bw = $downlink_bw * $oversubscription;

        $DS_uplink_speed = $ports->where('type', 'distributionSwitch')->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->sortBy('speed')->first();

        if (count($AS_Array) <= $AS_per_DS) {
            $uplink_fowardingRate = 0.001488 * $DS_uplink_speed * 1;
            $uplink_switchingCapacity = 2 * $DS_uplink_speed * 1 / 1000;
        } else {
            $uplink_fowardingRate = 0.001488 * $DS_uplink_speed * 3;
            $uplink_switchingCapacity = 2 * $DS_uplink_speed * 3 / 1000;
        }

        $forwardingRate = $downlink_forwardingRate + $uplink_fowardingRate;
        $switchingCapacity = $downlink_switchingCapacity + $uplink_switchingCapacity;

        $distributionSwitch = $devices->where('type', 'distributionSwitch')->where('s-forwarding_rate', '>=', $forwardingRate)->where('s-switching_capacity', '>=', $switchingCapacity)->whereIn('device_id', $distributionSwitchPorts)->sortBy('s-forwarding_rate')->first();

        $distributionSwitchConnector = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->where('device_id', $distributionSwitch->device_id)->pluck('connector')->first();

        if (count($AS_Array) <= $AS_per_DS) {
            for ($i = 1; $i <= 2; ++$i) {
                $DS_Array[] = [
                    'name' => "DS{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
            }

            // router
            $routerPorts = $ports->where('AN', '!=', 'LAN')->where('number_of_ports', '>=', 3)->where('connector', $distributionSwitchConnector)->pluck('device_id')->first();
            $routerDevice = $devices->where('device_id', $routerPorts);

            $R_Array[] = [
                'name' => 'R1',
                'type' => $routerDevice->first()->type,
                'device_id' => $routerDevice->first()->device_id,
            ];
        } else {
            // potrebujem zistit kolko distribucnych celkov (celok su 2 DS)
            // potrebujem, urcilo sa, ze jeden celok je pre 8 AS
            $numberOfDistributions = ceil(count($AS_Array) / 8);

            for ($i = 1; $i <= $numberOfDistributions * 2; ++$i) {
                $DS_Array[] = [
                    'name' => "DS{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
            }

            // core switches

            $downlink_forwardingRate = 0.001488 * $DS_uplink_speed * count($DS_Array);
            $downlink_switchingCapacity = 2 * $DS_uplink_speed * count($DS_Array) / 1000;

            $downlink_bw = count($DS_Array) * $DS_uplink_speed;
            $oversubscription = 1 / 4;
            $uplink_bw = $downlink_bw * $oversubscription;

            // treba osetrit, ked je uplink_bw vacsi ako uplink port speed core
            // switcha aby vratil error alebo nejako skombinoval viac uplink portov

            $CS_uplink_speed = $ports->where('type', 'coreSwitch')->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->sortBy('speed')->first();

            $uplink_forwardingRate = 0.001488 * $CS_uplink_speed * 1;
            $uplink_switchingCapacity = 2 * $CS_uplink_speed * 1 / 1000;

            $forwardingRate = $downlink_forwardingRate + $uplink_forwardingRate;
            $switchingCapacity = $downlink_switchingCapacity + $uplink_switchingCapacity;

            $downlink_switchByPorts = $ports->where('type', 'coreSwitch')->where('direction', 'downlink')->where('number_of_ports', '>=', count($DS_Array))->where('speed', '>=', $DS_uplink_speed)->pluck('device_id');

            $coreSwitch = $devices->where('type', 'coreSwitch')->whereIn('device_id', $downlink_switchByPorts)->where('s-forwarding_rate', '>=', $forwardingRate)->where('s-switching_capacity', '>=', $switchingCapacity)->sortBy('s-forwarding_rate')->first();

            for ($i = 1; $i <= 2; ++$i) {
                $CS_Array[] = [
                    'name' => "CS{$i}",
                    'type' => 'coreSwitch',
                    'device_id' => $coreSwitch->device_id,
                ];
            }

            // router
            $routerPorts = $ports->where('AN', '!=', 'LAN')->where('number_of_ports', '>=', 3)->where('connector', $distributionSwitchConnector)->pluck('device_id')->first();
            $routerDevice = $devices->where('device_id', $routerPorts);

            $R_Array[] = [
                'name' => 'R1',
                'type' => $routerDevice->first()->type,
                'device_id' => $routerDevice->first()->device_id,
            ];
        }

        /* // prebieha filtracia routerov podla poctu pouzivatelov
        switch ($network_users) {
            case $network_users <= 50:
                $routerDevices = $routerDevices->where('r-branch', 'small');
                break;

            case $network_users <= 150:
                $routerDevices = $routerDevices->where('r-branch', 'medium');
                break;

            default:
                $routerDevices = $routerDevices->where('r-branch', 'large');
                break;
        }

        // prebieha filtracia routerov podla vyťaženosti siete (prenosovej rychlosti)
        switch ($networkTraffic) {
            case 'small':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->min('r-throughput'));
                break;
            case 'medium':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->median('r-throughput'));
                break;
            case 'large':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->max('r-throughput'));
                break;

            default:
                // code...
                break;
        }

        $R_Array[] = [
            'name' => 'R1',
            'type' => $routerDevices->first()->type,
            'device_id' => $routerDevices->first()->device_id,
        ]; */
        if (count($AS_Array) <= $AS_per_DS) {
            $devicesArray = array_merge($R_Array, $DS_Array, $AS_Array);
        } else {
            $devicesArray = array_merge($R_Array, $CS_Array, $DS_Array, $AS_Array);
        }

        // pridavanie end devices
        $EDPorts = $ports->where('type', 'ED');
        $ED = $EDPorts->where('speed', '>=', $userConnection)->first()->device_id;

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
    public function chooseDevices(int $users, string $vlans, int $userConnection, string $networkTraffic)
    {
        $devices = Device::all();
        $ports = Port::all();

        $routerDevices = $devices->where('type', 'router');

        // prebieha filtracia routerov podla poctu pouzivatelov
        switch ($users) {
            case $users <= 50:
                $routerDevices = $routerDevices->where('r-branch', 'small');
                break;

            case $users <= 150:
                $routerDevices = $routerDevices->where('r-branch', 'medium');
                break;

            default:
                $routerDevices = $routerDevices->where('r-branch', 'large');
                break;
        }

        // prebieha filtracia routerov podla vyťaženosti siete (prenosovej rychlosti)
        switch ($networkTraffic) {
            case 'small':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->min('r-throughput'));
                break;
            case 'medium':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->median('r-throughput'));
                break;
            case 'large':
                $routerDevices = $routerDevices->where('r-throughput', $routerDevices->max('r-throughput'));
                break;

            default:
                // code...
                break;
        }

        $devicesArray[] = [
            'name' => 'R1',
            'type' => $routerDevices->first()->type,
            'device_id' => $routerDevices->first()->device_id,
        ];

        $routerId = $routerDevices->first()->device_id;
        $routerConnector = $ports->where('AN', '!=', 'WAN')->where('speed', '>=', $userConnection)->where('device_id', $routerId)->pluck('connector');

        // priprava switchov

        $switchDevices = $devices->where('type', 'switch');
        $accessSwitches = $switchDevices->where('s-L3', 'no')->where('s-vlan', $vlans);
        $distributionSwitches = $switchDevices->where('s-L3', 'yes');
        $numberOfDistributionSwitches = 0;

        $accessSwitchIds = $accessSwitches->pluck('device_id');

        if ($users <= 150) {
            $accessSwitchPorts = $ports->where('type', 'switch')->whereIn('device_id', $accessSwitchIds)->whereIn('connector', $routerConnector);

            $accessSwitch = $this->accessSwitch($users, $userConnection, $accessSwitches, $accessSwitchPorts, $numberOfDistributionSwitches);

            $devicesArray = array_merge($devicesArray, $accessSwitch);
        } else {
            $distributionSwitchIds = $distributionSwitches->pluck('device_id');
            $distributionSwitchPorts = $ports->where('type', 'switch')->where('direction', 'uplink')->whereIn('device_id', $distributionSwitchIds)->whereIn('connector', $routerConnector);

            $distributionSwitch = $distributionSwitches->whereIn('device_id', $distributionSwitchPorts->pluck('device_id'))->last();

            $distributionSwitchConnector = $distributionSwitchPorts->where('device_id', $distributionSwitch->device_id)->pluck('connector');

            for ($i = 1; $i <= 2; ++$i) {
                $devicesArray[] = [
                    'name' => "S{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
                $numberOfDistributionSwitches = $i;
            }

            $accessSwitchPorts = $ports->where('type', 'switch')->whereIn('device_id', $accessSwitchIds)->whereIn('connector', $distributionSwitchConnector);

            $accessSwitch = $this->accessSwitch($users, $userConnection, $accessSwitches, $accessSwitchPorts, $numberOfDistributionSwitches);

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
        DevicesInNetwork::getQuery()->delete();

        return json_encode([]);
    }
}
