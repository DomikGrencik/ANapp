<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //---------------------------------ROUTERS
        //Cisco C921-4P router
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

        //Cisco C1131(X)-8PW
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 8,
            'type' => 'router',
            'device_id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'SFP',
            'connector' => 'SFP',
            'AN' => 'WAN',
            'speed' => '1000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '2',
        ]);

        //Cisco C8300-1N1S-4T2X
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN_WAN',
            'speed' => '1000',
            'number_of_ports' => 4,
            'type' => 'router',
            'device_id' => '3',
        ]);
        DB::table('ports')->insert([
            'name' => 'SFP+',
            'connector' => 'SFP+',
            'AN' => 'LAN_WAN',
            'speed' => '10000',
            'number_of_ports' => 2,
            'type' => 'router',
            'device_id' => '3',
        ]);

        //---------------------------------SWITCHES
        //Cisco C9200L-24T-4G
        DB::table('ports')->insert([
            'name' => 'SFP',
            'connector' => 'SFP',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'UL',
            'number_of_ports' => 4,
            'type' => 'switch',
            'device_id' => '4',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 24,
            'type' => 'switch',
            'device_id' => '4',
        ]);

        //Cisco C9200L-48T-4G
        DB::table('ports')->insert([
            'name' => 'SFP',
            'connector' => 'SFP',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'UL',
            'number_of_ports' => 4,
            'type' => 'switch',
            'device_id' => '5',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 48,
            'type' => 'switch',
            'device_id' => '5',
        ]);

        //Cisco C9200L-24PXG-4X
        DB::table('ports')->insert([
            'name' => 'SFP+',
            'connector' => 'SFP+',
            'AN' => 'LAN',
            'speed' => '10000',
            'uplink_downlink' => 'UL',
            'number_of_ports' => 4,
            'type' => 'switch',
            'device_id' => '6',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '10000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 8,
            'type' => 'switch',
            'device_id' => '6',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 16,
            'type' => 'switch',
            'device_id' => '6',
        ]);

        //Cisco C9200L-48PXG-4X
        DB::table('ports')->insert([
            'name' => 'SFP+',
            'connector' => 'SFP+',
            'AN' => 'LAN',
            'speed' => '10000',
            'uplink_downlink' => 'UL',
            'number_of_ports' => 4,
            'type' => 'switch',
            'device_id' => '7',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '10000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 8,
            'type' => 'switch',
            'device_id' => '7',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'uplink_downlink' => 'DL',
            'number_of_ports' => 40,
            'type' => 'switch',
            'device_id' => '7',
        ]);

        //---------------------------------EndDevices
        //desktop
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '8',
        ]);

        //laptop
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => '1000',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '9',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE',
            'connector' => 'Wireless',
            'AN' => 'LAN',
            'speed' => 'Wireless',
            'number_of_ports' => 1,
            'type' => 'ED',
            'device_id' => '9',
        ]);
    }
}
