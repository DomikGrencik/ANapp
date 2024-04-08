<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id('device_id');
            $table->string('manufacturer');
            $table->string('model');
            $table->enum('type', ['router', 'switch', 'ED']);
            $table->integer('r-throughput')->nullable();
            $table->enum('r-SD-WAN', ['yes', 'no', '-'])->default('-');
            $table->float('s-forwarding_rate')->nullable();
            $table->float('s-switching_capacity')->nullable();
            $table->enum('s-vlan', ['yes', 'no', '-'])->default('-');
            $table->enum('s-L3', ['yes', 'no', '-'])->default('-');
            $table->integer('price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
