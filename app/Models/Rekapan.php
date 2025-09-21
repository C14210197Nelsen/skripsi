<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekapan extends Model {
    protected $table = 'rekapan';

    protected $primaryKey = 'rekapanID';
    public $incrementing = true;      
    protected $keyType = 'int';         

    protected $fillable = [
        'tanggal',
        'kategori',
        'tipe',
        'jumlah',
        'metode',
        'deskripsi',
    ];

}
