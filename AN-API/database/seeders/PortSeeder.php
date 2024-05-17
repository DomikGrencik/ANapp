<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --------------------------------------\\
        // ---------------------------------ROUTERS
        // --------------------------------------\\
        // Cisco C921-4P router
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '1',
        ]);

        // Cisco C931-4P router
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '2',
        ]);

        // Cisco C8200L-1N-4T+NIM-ES2-4 router
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '3',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '3',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'SFP',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '3',
        ]);

        // Cisco C1131(X)-8PW
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 8,
            'type' => 'router',
            'device_id' => '4',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '4',
        ]);

        // Cisco C8200-1N-4T+NIM-ES2-8
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 8,
            'type' => 'router',
            'device_id' => '5',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '5',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'SFP',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '5',
        ]);

        // Cisco C8300-1N1S-6T+C-NIM-4X
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN_WAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '6',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'SFP',
            'AN' => 'LAN_WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '6',
        ]);
        DB::table('ports')->insert([
            'name' => '10GE',
            'connector' => 'SFP+',
            'AN' => 'LAN_WAN',
            'speed' => '10000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '6',
        ]);

        // Cisco C8300-1N1S-4T2X+NIM-ES2-4
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN_WAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '7',
        ]);
        DB::table('ports')->insert([
            'name' => '10GE',
            'connector' => 'SFP+',
            'AN' => 'LAN_WAN',
            'speed' => '10000',
            'number_of_ports' => 6,
            'type' => 'router',
            'device_id' => '7',
        ]);

        // ---------------------------------------\\
        // ---------------------------------SWITCHES
        // ---------------------------------------\\
        // Cisco CBS110-16T
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 16,
            'type' => 'accessSwitch',
            'device_id' => '8',
        ]);

        // Cisco CBS110-24T
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'SFP',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'direction' => 'uplink',
            'type' => 'accessSwitch',
            'device_id' => '9',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 22,
            'direction' => 'downlink',
            'type' => 'accessSwitch',
            'device_id' => '9',
        ]);

        // Cisco CBS220-16T-2G
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'SFP',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'direction' => 'uplink',
            'type' => 'accessSwitch',
            'device_id' => '10',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 16,
            'direction' => 'downlink',
            'type' => 'accessSwitch',
            'device_id' => '10',
        ]);

        // Cisco CBS220-24T-4X
        DB::table('ports')->insert([
            'name' => '10GE',
            'connector' => 'SFP+',
            'AN' => 'LAN',
            'speed' => '10000',
            'number_of_ports' => 4,
            'direction' => 'uplink',
            'type' => 'accessSwitch',
            'device_id' => '11',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 24,
            'direction' => 'downlink',
            'type' => 'accessSwitch',
            'device_id' => '11',
        ]);

        // Cisco CBS220-48T-4X
        DB::table('ports')->insert([
            'name' => '10GE',
            'connector' => 'SFP+',
            'AN' => 'LAN',
            'speed' => '10000',
            'number_of_ports' => 4,
            'direction' => 'uplink',
            'type' => 'accessSwitch',
            'device_id' => '12',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 48,
            'direction' => 'downlink',
            'type' => 'accessSwitch',
            'device_id' => '12',
        ]);

        // Cisco C9300-24UX
        DB::table('ports')->insert([
            'name' => '25GE',
            'connector' => 'SFP28',
            'AN' => 'LAN',
            'speed' => '25000',
            'number_of_ports' => 8,
            'direction' => 'uplink',
            'type' => 'distributionSwitch',
            'device_id' => '13',
        ]);
        DB::table('ports')->insert([
            'name' => '10GE',
            'connector' => 'SFP+',
            'AN' => 'LAN',
            'speed' => '10000',
            'number_of_ports' => 24,
            'direction' => 'downlink',
            'type' => 'distributionSwitch',
            'device_id' => '13',
        ]);

        // Cisco C9300X-24Y
        DB::table('ports')->insert([
            'name' => '100GE',
            'connector' => 'QSFP',
            'AN' => 'LAN',
            'speed' => '100000',
            'number_of_ports' => 4,
            'direction' => 'uplink',
            'type' => 'distributionSwitch',
            'device_id' => '14',
        ]);
        DB::table('ports')->insert([
            'name' => '25GE',
            'connector' => 'SFP28',
            'AN' => 'LAN',
            'speed' => '25000',
            'number_of_ports' => 24,
            'direction' => 'downlink',
            'type' => 'distributionSwitch',
            'device_id' => '14',
        ]);

        // Cisco C9500-24Y4C
        DB::table('ports')->insert([
            'name' => '25GE',
            'connector' => 'SFP28',
            'AN' => 'LAN',
            'speed' => '25000',
            'number_of_ports' => 24,
            'direction' => 'downlink',
            'type' => 'coreSwitch',
            'device_id' => '15',
        ]);
        DB::table('ports')->insert([
            'name' => '100GE',
            'connector' => 'QSFP',
            'AN' => 'LAN',
            'speed' => '100000',
            'number_of_ports' => 4,
            'direction' => 'uplink',
            'type' => 'coreSwitch',
            'device_id' => '15',
        ]);

        // Cisco C9500-48Y4C
        DB::table('ports')->insert([
            'name' => '25GE',
            'connector' => 'SFP28',
            'AN' => 'LAN',
            'speed' => '25000',
            'number_of_ports' => 48,
            'direction' => 'downlink',
            'type' => 'coreSwitch',
            'device_id' => '16',
        ]);
        DB::table('ports')->insert([
            'name' => '100GE',
            'connector' => 'QSFP',
            'AN' => 'LAN',
            'speed' => '100000',
            'number_of_ports' => 4,
            'direction' => 'uplink',
            'type' => 'coreSwitch',
            'device_id' => '16',
        ]);

        /* // Cisco C9200L-24T-4G
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 24,
            'type' => 'switch',
            'device_id' => '16',
        ]);

        // Cisco C9200L-48T-4G
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 48,
            'type' => 'switch',
            'device_id' => '17',
        ]); */
        // -----------------------------------------\\
        // ---------------------------------EndDevices
        // -----------------------------------------\\
        // desktop
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '17',
        ]);

        // laptop
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '18',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'Wireless',
            'AN' => 'LAN',
            'speed' => 'Wireless',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '18',
        ]);
    }
}
