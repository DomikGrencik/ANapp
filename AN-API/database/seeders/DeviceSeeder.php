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
            'model' => 'C921-4P',
            'type' => 'router',
            'r-throughput' => '120',
            'r-branch' => 'small',
            'price' => '300',
        ]);
        // 2
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C931-4P',
            'type' => 'router',
            'r-throughput' => '200',
            'r-branch' => 'small',
            'price' => '500',
        ]);
        // 3
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8200L-1N-4T+NIM-ES2-4',
            'type' => 'router',
            'r-throughput' => '430',
            'r-branch' => 'small',
            'price' => '1000',
        ]);
        // 4
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1131X-8P',
            'type' => 'router',
            'r-throughput' => '680',
            'r-branch' => 'medium',
            'price' => '1500',
        ]);
        // 5
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8200-1N-4T+NIM-ES2-8',
            'type' => 'router',
            'r-throughput' => '900',
            'r-branch' => 'medium',
            'price' => '2000',
        ]);
        // 6
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-6T+NIM-ES2-4',
            'type' => 'router',
            'r-throughput' => '1800',
            'r-branch' => 'medium',
            'price' => '5000',
        ]);
        // 7
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X+NIM-ES2-4',
            'type' => 'router',
            'r-throughput' => '6300',
            'r-branch' => 'large',
            'price' => '10000',
        ]);

        // ---------------------------------------\\
        // ---------------------------------SWITCHES
        // ---------------------------------------\\
        // 8
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS110-16T',
            'type' => 'accessSwitch',
            's-forwarding_rate' => '23.9',
            's-switching_capacity' => '32',
            's-vlan' => 'no',
            's-L3' => 'no',
            'price' => '130',
        ]);
        // 9
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS110-24T',
            'type' => 'accessSwitch',
            's-forwarding_rate' => '35.8',
            's-switching_capacity' => '48',
            's-vlan' => 'no',
            's-L3' => 'no',
            'price' => '180',
        ]);
        // 10
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-16T-2G',
            'type' => 'accessSwitch',
            's-forwarding_rate' => '26.78',
            's-switching_capacity' => '36',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '200',
        ]);
        // 11
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-24T-4X',
            'type' => 'accessSwitch',
            's-forwarding_rate' => '95.24',
            's-switching_capacity' => '128',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '230',
        ]);
        // 12
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'CBS220-48T-4X',
            'type' => 'accessSwitch',
            's-forwarding_rate' => '130.95',
            's-switching_capacity' => '176',
            's-vlan' => 'yes',
            's-L3' => 'no',
            'price' => '380',
        ]);
        // 13
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9300-24UX',
            'type' => 'distributionSwitch',
            's-forwarding_rate' => '476.2',
            's-switching_capacity' => '640',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '10000',
        ]);
        // 14
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9300X-24Y',
            'type' => 'distributionSwitch',
            's-forwarding_rate' => '1488',
            's-switching_capacity' => '2000',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '20000',
        ]);
        // 15
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9500-24Y4C',
            'type' => 'coreSwitch',
            's-forwarding_rate' => '1488',
            's-switching_capacity' => '2000',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '50000',
        ]);
        // 16
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9500-48Y4C',
            'type' => 'coreSwitch',
            's-forwarding_rate' => '2380.8',
            's-switching_capacity' => '3200',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '50000',
        ]);

        /* // 16
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-24T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '41.66',
            's-switching_capacity' => '56',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '1000',
        ]);
        // 17
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-48T-4G',
            'type' => 'switch',
            's-forwarding_rate' => '77.38',
            's-switching_capacity' => '104',
            's-vlan' => 'yes',
            's-L3' => 'yes',
            'price' => '1500',
        ]); */
        // -----------------------------------------\\
        // ---------------------------------EndDevices
        // -----------------------------------------\\
        // 18
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'desktop',
            'type' => 'ED',
        ]);
        // 19
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'laptop',
            'type' => 'ED',
        ]);
    }
}
