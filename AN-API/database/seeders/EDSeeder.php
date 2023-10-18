<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('e_d_s')->insert([
            'type' => 'PC',
            'FE_ports' => '1',
            'GE_ports' => '1',
            'wireless' => '0'
        ]);

        DB::table('e_d_s')->insert([
            'type' => 'Phone',
            'FE_ports' => '0',
            'GE_ports' => '0',
            'wireless' => '1'
        ]);

        DB::table('e_d_s')->insert([
            'type' => 'Printer',
            'FE_ports' => '1',
            'GE_ports' => '0',
            'wireless' => '1'
        ]);
    }
}
