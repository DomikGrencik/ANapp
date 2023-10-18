<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SwitchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sws')->insert([
            'manufacturer' => 'Cisco',
            'type' => 'dfh45',
            'FE_ports' => '24',
            'GE_ports' => '2'
        ]);

        DB::table('sws')->insert([
            'manufacturer' => 'Cisco',
            'type' => 'tzru78',
            'FE_ports' => '48',
            'GE_ports' => '4'
        ]);
    }
}
