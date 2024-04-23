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
        Schema::create('devices_in_networks', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->enum('type', ['router', 'switch', 'accessSwitch', 'distributionSwitch', 'ED']);
            $table->unsignedBigInteger('device_id')->nullable();
            $table->timestamps();
        });

        Schema::table('devices_in_networks', function (Blueprint $table) {
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices_in_networks');
    }
};
