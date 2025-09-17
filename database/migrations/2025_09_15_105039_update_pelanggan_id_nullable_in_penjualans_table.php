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
        Schema::table('penjualans', function (Blueprint $table) {
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['pelanggan_id']);
            
            // Ubah kolom menjadi nullable
            $table->foreignId('pelanggan_id')->nullable()->change();
            
            // Tambah kembali foreign key constraint dengan nullable
            $table->foreign('pelanggan_id')
                  ->references('id')
                  ->on('pelanggans')
                  ->nullOnDelete()
                  ->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['pelanggan_id']);
            
            // Kembalikan kolom menjadi not nullable
            $table->foreignId('pelanggan_id')->change();
            
            // Tambah kembali foreign key constraint seperti semula
            $table->foreign('pelanggan_id')
                  ->references('id')
                  ->on('pelanggans')
                  ->cascadeOnDelete()
                  ->noActionOnUpdate();
        });
    }
};