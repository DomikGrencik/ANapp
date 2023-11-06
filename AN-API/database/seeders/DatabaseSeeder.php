<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RouterSeeder::class);
        $this->call(SwitchSeeder::class);
        $this->call(EDSeeder::class);
        $this->call(DeviceSeeder::class);
        $this->call(PortSeeder::class);
    }
}
