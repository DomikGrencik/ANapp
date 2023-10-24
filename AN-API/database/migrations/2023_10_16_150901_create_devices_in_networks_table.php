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
        Schema::create('devices_in_networks', function (Blueprint $table) {
            $table->id('device_id');
            $table->string('name');
            $table->enum('type', ['router', 'switch', 'ED']);
            $table->unsignedBigInteger('router_id')->nullable();
            $table->unsignedBigInteger('switch_id')->nullable();
            $table->unsignedBigInteger('ED_id')->nullable();
            $table->timestamps();
        });

        Schema::table('devices_in_networks', function (Blueprint $table) {
            $table->foreign('router_id')
                ->references('router_id')
                ->on('routers')
                ->onDelete('cascade');
            $table->foreign('switch_id')
                ->references('switch_id')
                ->on('sws')
                ->onDelete('cascade');
            $table->foreign('ED_id')
                ->references('ED_id')
                ->on('e_d_s')
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
