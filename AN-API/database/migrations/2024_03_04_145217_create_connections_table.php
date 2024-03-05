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
        Schema::create('connections', function (Blueprint $table) {
            $table->id('connection_id');
            $table->unsignedBigInteger('interface_id1');
            $table->unsignedBigInteger('interface_id2');
            $table->unsignedBigInteger('device_id1');
            $table->unsignedBigInteger('device_id2');
            $table->string('name1');
            $table->string('name2');
            $table->timestamps();
        });

        Schema::table('connections', function (Blueprint $table) {
            $table->foreign('interface_id1')
                ->references('interface_id')
                ->on('interface_of_devices')
                ->onDelete('cascade');
            $table->foreign('interface_id2')
                ->references('interface_id')
                ->on('interface_of_devices')
                ->onDelete('cascade');
            $table->foreign('device_id1')
                ->references('id')
                ->on('devices_in_networks')
                ->onDelete('cascade');
            $table->foreign('device_id2')
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
        Schema::dropIfExists('connections');
    }
};
