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
        Schema::create('tbl_catatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('jenis_item', ['pakan', 'obat', 'vaksin']);
            $table->unsignedBigInteger('item_id');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->integer('jumlah_perubahan');
            $table->enum('jenis_perubahan', ['penambahan', 'pengurangan']);
            $table->text('catatan');
            $table->enum('status', ['pending', 'dilihat', 'disetujui'])->default('pending');
            $table->timestamp('tanggal_perubahan');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['jenis_item', 'item_id']);
            $table->index('user_id');
            $table->index('status');
            $table->index('tanggal_perubahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_catatan');
    }
};
