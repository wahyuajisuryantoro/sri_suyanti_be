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
        Schema::create('vaksin', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_vaksin', 255);
            $table->string('nama_vaksin', 255);
            $table->integer('stok');
            $table->string('satuan', 50);
            $table->text('deskripsi_vaksin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaksin');
    }
};
