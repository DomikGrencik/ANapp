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
        Schema::create('e_d_s', function (Blueprint $table) {
            $table->id('ED_id');
            $table->enum('type', ['PC', 'Phone', 'Printer']);
            $table->integer('FE_ports');
            $table->integer('GE_ports');
            $table->integer('wireless');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_d_s');
    }
};
