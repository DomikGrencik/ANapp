<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interface_of_devices', function (Blueprint $table) {
            $table->id('interface_id');
            $table->string('name');
            $table->enum('connector', ['RJ45', 'SFP', 'SFP+', 'SFP28', 'QSFP', 'Wireless']);
            $table->enum('AN', ['LAN', 'WAN', 'LAN_WAN']);
            $table->enum('speed', ['100', '1000', '2500', '10000', '25000', '40000', '100000', '200000', '400000', 'Wireless']);
            $table->enum('direction', ['downlink', 'uplink'])->nullable();
            $table->unsignedBigInteger('id');
            $table->enum('type', ['router', 'accessSwitch', 'distributionSwitch', 'coreSwitch', 'ED'])->nullable();
            $table->timestamps();
        });

        Schema::table('interface_of_devices', function (Blueprint $table) {
            $table->foreign('id')
                ->references('id')
                ->on('devices_in_networks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_of_devices');
    }
};
