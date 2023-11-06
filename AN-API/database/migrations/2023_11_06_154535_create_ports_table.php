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
            $table->enum('AN', ['LAN', 'WAN', 'LAN/WAN']);
            $table->enum('speed', ['FE', '1GE', '2,5GE', '10GE', 'Wireless']);
            $table->enum('uplink/downlink', ['UL', 'DL'])->nullable();
            $table->integer('number_of_ports');
            $table->unsignedBigInteger('id');
            $table->timestamps();
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
