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
        Schema::create('routers', function (Blueprint $table) {
            $table->id('router_id');
            $table->string('manufacturer');
            $table->string('type');
            $table->integer('LAN_ports');
            $table->integer('WAN_ports');
            $table->enum('LAN_type', ['FE', 'GE', 'Optical', 'Wireless']);
            $table->enum('WAN_type', ['FE', 'GE', 'Optical', 'Wireless']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
