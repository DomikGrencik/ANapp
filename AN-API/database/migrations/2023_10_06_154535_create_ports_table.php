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
        Schema::create('ports', function (Blueprint $table) {
            $table->id('port_id');
            $table->string('name');
            $table->enum('connector', ['RJ45', 'SFP', 'SFP+', 'Wireless']);
            $table->enum('AN', ['LAN', 'WAN', 'LAN_WAN']);
            $table->enum('speed', ['100', '1000', '2500', '10000', 'Wireless']);
            $table->integer('number_of_ports');
            $table->enum('type', ['router', 'switch', 'ED']);
            $table->unsignedBigInteger('device_id');
            $table->timestamps();
        });

        Schema::table('ports', function (Blueprint $table) {
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
        Schema::dropIfExists('ports');
    }
};
