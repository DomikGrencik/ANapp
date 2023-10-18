<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('routers')->insert([
            'manufacturer' => 'Cisco',
            'type' => 'abc123',
            'LAN_ports' => '2',
            'WAN_ports' => '1'
        ]);

        DB::table('routers')->insert([
            'manufacturer' => 'Cisco',
            'type' => 'qwe987',
            'LAN_ports' => '4',
            'WAN_ports' => '2'
        ]);
    }
}
