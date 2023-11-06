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
            'name' => 'GE0',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => 'GE',
            'id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE1',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => 'GE',
            'id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE2',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => 'GE',
            'id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE3',
            'connector' => 'RJ45',
            'AN' => 'LAN',
            'speed' => 'GE',
            'id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE4',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => 'GE',
            'id' => '1',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE5',
            'connector' => 'RJ45',
            'AN' => 'WAN',
            'speed' => 'GE',
            'id' => '1',
        ]);

        //Cisco C8300-1N1S-4T2X
        DB::table('ports')->insert([
            'name' => 'GE0',
            'connector' => 'RJ45',
            'AN' => 'LAN/WAN',
            'speed' => 'GE',
            'id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE1',
            'connector' => 'RJ45',
            'AN' => 'LAN/WAN',
            'speed' => 'GE',
            'id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE2',
            'connector' => 'RJ45',
            'AN' => 'LAN/WAN',
            'speed' => 'GE',
            'id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'GE3',
            'connector' => 'RJ45',
            'AN' => 'LAN/WAN',
            'speed' => 'GE',
            'id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'SFP+0',
            'connector' => 'SFP+',
            'AN' => 'LAN/WAN',
            'speed' => '10GE',
            'id' => '2',
        ]);
        DB::table('ports')->insert([
            'name' => 'SFP+1',
            'connector' => 'SFP+',
            'AN' => 'LAN/WAN',
            'speed' => '10GE',
            'id' => '2',
        ]);
    }
}
