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
            for ($i = 0; $i < $numberOfDistributionSwitches; $i = +2) {
                $DS_firtstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i])->pluck('interface_id')->first();
                $DS_firtstUplinkPorts[] = $interfaces->where('direction', 'uplink')->where('id', $DS_IDs[$i + 1])->pluck('interface_id')->first();

                $connectionsArray[] = [
                    'interface_id1' => $DS_firtstUplinkPorts[$i],
                    'interface_id2' => $DS_firtstUplinkPorts[$i + 1],
                    'device_id1' => $DS_IDs[$i],
                    'device_id2' => $DS_IDs[$i + 1],
                    'name1' => $distributionSwitchInterfaces->where('interface_id', $DS_firtstUplinkPorts[$i])->pluck('name')->first(),
                    'name2' => $distributionSwitchInterfaces->where('interface_id', $DS_firtstUplinkPorts[$i + 1])->pluck('name')->first(),
                ];
            }

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

            // dalej potrebujeme vytvorit spojenie medzi access switchmi a end
            // devices

            $prev_sw_id = 0;
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
    public function choose(Request $request /* int $users, string $vlans, int $userConnection, string $networkTraffic */)
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

        $total_forwardingRate = 0;
        $total_switchingCapacity = 0;
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
            $oversubscription = 0.05;
            $uplink_bw = $downlink_bw * $oversubscription;

            $uplink_speed = $accessSwitchPorts->where('direction', 'uplink')->where('speed', '>=', $uplink_bw)->pluck('speed')->first();
            $uplink_numberOfPorts = $accessSwitchPorts->where('direction', 'uplink')->where('speed', '>=', $uplink_speed)->whereIn('device_id', $downlink_switchByPorts)->pluck('number_of_ports')->first();

            $uplink_fowardingRate = 0.001488 * $uplink_speed * $uplink_numberOfPorts;
            $uplink_switchingCapacity = 2 * $uplink_speed * $uplink_numberOfPorts / 1000;

            $forwardingRate = $downlink_forwardingRate + $uplink_fowardingRate;
            $switchingCapacity = $downlink_switchingCapacity + $uplink_switchingCapacity;

            $total_forwardingRate += $forwardingRate;
            $total_switchingCapacity += $switchingCapacity;

            $accessSwitch = $accessSwitches->whereIn('device_id', $downlink_switchByPorts)->where('s-forwarding_rate', '>=', $forwardingRate)->where('s-switching_capacity', '>=', $switchingCapacity)->sortBy('s-forwarding_rate')->first();

            $AS_Array[] = [
                'name' => "AS{$s}",
                'type' => 'accessSwitch',
                'device_id' => $accessSwitch->device_id,
            ];
        } while ($users > 0);

        // distribution switches

        $max_DS_forwardingRate = $devices->where('type', 'distributionSwitch')->max('s-forwarding_rate');
        $max_DS_switchingCapacity = $devices->where('type', 'distributionSwitch')->max('s-switching_capacity');

        $deviceIds = collect($AS_Array)->pluck('device_id');

        $accessSwitches_uplinkConnector = $accessSwitchPorts->whereIn('device_id', $deviceIds)->where('direction', 'uplink')->pluck('connector');

        $distributionSwitchPorts = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->whereIn('connector', $accessSwitches_uplinkConnector)->pluck('device_id');

        if ($total_forwardingRate <= $max_DS_forwardingRate || $total_switchingCapacity <= $max_DS_switchingCapacity) {
            $distributionSwitch = $devices->where('type', 'distributionSwitch')->where('s-L3', 'yes')->where('s-vlan', 'yes')->where('s-forwarding_rate', '>=', $total_forwardingRate)->where('s-switching_capacity', '>=', $total_switchingCapacity)->whereIn('device_id', $distributionSwitchPorts)->sortBy('s-forwarding_rate')->first();

            $distributionSwitchConnector = $ports->where('type', 'distributionSwitch')->where('direction', 'downlink')->where('device_id', $distributionSwitch->device_id)->pluck('connector')->first();

            for ($i = 1; $i <= 2; ++$i) {
                $DS_Array[] = [
                    'name' => "DS{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
                $numberOfDistributionSwitches = $i;
            }
        } else {
            // vyberia sa distribucny switch podla maximalnej hodnoty spomedzi vsetkych distribucnych switchov, ktore su pripojene na access switche
            $distributionSwitch = $devices->where('type', 'distributionSwitch')->where('s-L3', 'yes')->where('s-vlan', 'yes')->where('s-forwarding_rate', '>=', $max_DS_forwardingRate)->where('s-switching_capacity', '>=', $max_DS_switchingCapacity)->whereIn('device_id', $distributionSwitchPorts)->sortBy('s-forwarding_rate')->first();

            $numberOfAccessSwitches = collect($AS_Array)->count();
            // maximalny pocet access switchov, ktore moze obsluhovat jeden distribucny switch
            $max_ASperDS = $max_DS_forwardingRate / $accessSwitches->max('s-forwarding_rate');

            // podla poctu access switchov a maximalneho poctu access switchov,
            // ktore moze obsluhovat jeden distribucny switch sa vytvoria
            // dvojice distribucnych switchov
            for ($i = 1; $i <= 2 * ceil($numberOfAccessSwitches / $max_ASperDS); ++$i) {
                $DS_Array[] = [
                    'name' => "DS{$i}",
                    'type' => 'distributionSwitch',
                    'device_id' => $distributionSwitch->device_id,
                ];
            }

            // core swithce

            $coreSwitches = $devices->where('type', 'coreSwitch');
        }



        // router
        $routerPorts = $ports->where('AN', '!=', 'LAN')->where('connector', $distributionSwitchConnector)->pluck('device_id')->first();
        $routerDevices = $devices->where('device_id', $routerPorts);

        // prebieha filtracia routerov podla poctu pouzivatelov
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
        ];

        $devicesArray = array_merge($R_Array, $DS_Array, $AS_Array);

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
        Schema::disableForeignKeyConstraints();
        DevicesInNetwork::truncate();
        // Connection::truncate();
        Schema::enableForeignKeyConstraints();

        return json_encode([]);
    }
}
