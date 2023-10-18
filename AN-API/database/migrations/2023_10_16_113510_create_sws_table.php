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
        Schema::create('sws', function (Blueprint $table) {
            $table->id('switch_id');
            $table->string('manufacturer');
            $table->string('type');
            $table->integer('FE_ports');
            $table->integer('GE_ports');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sws');
    }
};
