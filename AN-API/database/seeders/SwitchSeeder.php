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
            'DL_ports' => '24',
            'UL_ports' => '2',
            'DL_type' => 'FE',
            'UL_type' => 'GE'
        ]);

        DB::table('sws')->insert([
            'manufacturer' => 'Cisco',
            'type' => 'tzru78',
            'DL_ports' => '48',
            'UL_ports' => '4',
            'DL_type' => 'GE',
            'UL_type' => 'Optical'
        ]);
    }
}
