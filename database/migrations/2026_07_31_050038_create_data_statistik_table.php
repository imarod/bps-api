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
        Schema::create('data_statistik', function (Blueprint $table) {
            $table->id();
            $table->string('nama_indikator');
            $table->string('kategori')->nullable();
            $table->unsignedSmallInteger('tahun');
            $table->decimal('nilai', 15, 2);
            $table->string('satuan')->nullable();
            $table->string('sumber')->default('BPS Kota Lubuk Linggau');
            $table->timestamps();


            $table->unique(['nama_indikator' , 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_statistik');
    }
};
