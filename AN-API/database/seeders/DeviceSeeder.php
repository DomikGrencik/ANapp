<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --------------------------------------\\
        // ---------------------------------ROUTERS
        // --------------------------------------\\
        // 1
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C931-4P',
            'type' => 'router',
            'r-throughput' => '250',
            'r-SD-WAN' => 'no',
        ]);
        // 2
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1131(X)-8PW',
            'type' => 'router',
            'r-throughput' => '470',
            'r-SD-WAN' => 'yes',
        ]);
        // 3
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1161(X)-8P',
            'type' => 'router',
            'r-throughput' => '595',
            'r-SD-WAN' => 'yes',
        ]);
        // 4
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-6T',
            'type' => 'router',
            'r-throughput' => '1800',
            'r-SD-WAN' => 'yes',
        ]);
        // 5
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-6T+C-NIM-8T',
            'type' => 'router',
            'r-throughput' => '1800',
            'r-SD-WAN' => 'yes',
        ]);
        // 6
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X',
            'type' => 'router',
            'r-throughput' => '6300',
            'r-SD-WAN' => 'yes',
        ]);
        // 7
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X+C-NIM-8T',
            'type' => 'router',
            'r-throughput' => '6300',
            'r-SD-WAN' => 'yes',
        ]);
        // 8
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X+C-NIM-4X',
            'type' => 'router',
            'r-throughput' => '6300',
            'r-SD-WAN' => 'yes',
        ]);

        // ---------------------------------------\\
        // ---------------------------------SWITCHES
        // ---------------------------------------\\
        // 9
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS110-16T',
            'type' => 'switch',
            's-forwarding_rate' => '23.9',
            's-switching_capacity' => '32',
            's-vlan' => 'no',
            's-L3' => 'no',
            'price' => '130',
        ]);
        // 10
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS110-24T',
            'type' => 'switch',
            's-forwarding_rate' => '35.8',
            's-switching_capacity' => '48',
            's-vlan' => 'no',
            's-L3' => 'no',
            'price' => '180',
        ]);
        // 11
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-16T-2G',
            'type' => 'switch',
            's-forwarding_rate' => '26.78',
            's-switching_capacity' => '36',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '200',
        ]);
        // 12
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-24T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '41.66',
            's-switching_capacity' => '56',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '230',
        ]);
        // 13
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-48T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '74.38',
            's-switching_capacity' => '104',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '380',
        ]);
        // 14
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1300-16T-2G',
            'type' => 'switch',
            's-forwarding_rate' => '26.78',
            's-switching_capacity' => '36',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '380',
        ]);
        // 15
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1300-24T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '41.66',
            's-switching_capacity' => '56',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '500',
        ]);
        // 16
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1300-48T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '77.38',
            's-switching_capacity' => '104',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '800',
        ]);
        // 17
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-24T-4G',
            'type' => 'switch',
        ]);
        // 18
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-48T-4G',
            'type' => 'switch',
        ]);
        // 19
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-24PXG-4X',
            'type' => 'switch',
        ]);
        // 20
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-48PXG-4X',
            'type' => 'switch',
        ]);

        // -----------------------------------------\\
        // ---------------------------------EndDevices
        // -----------------------------------------\\
        // 21
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'desktop',
            'type' => 'ED',
        ]);
        // 22
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'laptop',
            'type' => 'ED',
        ]);
    }
}
