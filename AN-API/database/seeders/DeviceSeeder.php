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
            'type' => 'R'
        ]);
        DB::table('devices')->insert([
            'manufacturer' => 'Cisco',
            'model' => 'C8300-1N1S-4T2X',
            'type' => 'R'
        ]);
    }
}
