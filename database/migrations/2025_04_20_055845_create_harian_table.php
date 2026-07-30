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
        Schema::create('harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal');
            $table->integer('berat_ayam');
            $table->float('feed_intake');
            $table->float('fcr_cum');
            $table->integer('deplesi');
            $table->integer('sisa_ayam');
            $table->integer('total_ayam');
            $table->integer('total_pakan');
            $table->dateTime('waktu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harian');
    }
};
