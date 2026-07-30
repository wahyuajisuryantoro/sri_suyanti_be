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
        Schema::create('penugasan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_penugasan', ['pakan', 'vaksin', 'obat', 'pemeliharaan_ayam']);
            $table->foreignId('karyawan_id')->constrained('users');
            $table->foreignId('admin_id')->constrained('users');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('tanggal');
            $table->time('waktu');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penugasan');
    }
};
