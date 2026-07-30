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
        Schema::create('pakan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_pakan', 255);
            $table->string('nama_pakan', 255);
            $table->integer('stok');
            $table->string('satuan', 50);
            $table->text('deskripsi_pakan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakan');
    }
};
