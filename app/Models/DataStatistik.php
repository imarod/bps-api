<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataStatistik extends Model
{
    protected $table = 'data_statistik';

    protected $fillable = [
        'nama_indikator',
        'kategori',
        'wilayah',
        'tahun',
        'nilai',
        'satuan',
        'sumber',
        'is_manual'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'nilai' => 'decimal:2',
        'is_manual' => 'boolean',
    ];
}
