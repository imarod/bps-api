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
        Schema::table('data_statistik', function (Blueprint $table) {
            $table->string('wilayah') -> nullable() ->after('kategori');

            $table->dropUnique((['nama_indikator', 'tahun']));

            $table->unique(['nama_indikator', 'tahun', 'wilayah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_statistik', function (Blueprint $table) {
            $table->dropUnique(['nama_indikator', 'tahun', 'wilayah']);
            $table->dropColumn('wilayah');
            $table->unique(['nama_indikator', 'tahun']);
        });
    }
};
