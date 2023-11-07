<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //---------------------------------ROUTERS
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C931-4P',
            'type' => 'router'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C1131(X)-8PW',
            'type' => 'router'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X',
            'type' => 'router'
        ]);

        //---------------------------------SWITCHES
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-24T-4G',
            'type' => 'switch'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-48T-4G',
            'type' => 'switch'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-24PXG-4X',
            'type' => 'switch'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C9200L-48PXG-4X',
            'type' => 'switch'
        ]);

        //---------------------------------EndDevices
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'desktop',
            'type' => 'ED'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'PC',
            'model' => 'laptop',
            'type' => 'ED'
        ]);
    }
}
