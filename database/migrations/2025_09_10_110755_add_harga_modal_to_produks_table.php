<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('produks', function (Blueprint $table) {
        $table->unsignedInteger('harga_modal')->after('nama_produk');
    });
}

public function down(): void
{
    Schema::table('produks', function (Blueprint $table) {
        $table->dropColumn('harga_modal');
    });
}

};
