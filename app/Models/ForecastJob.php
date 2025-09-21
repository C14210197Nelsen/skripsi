<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastJob extends Model
{
    use HasFactory;

    protected $table = 'forecast_jobs';

    protected $fillable = [
        'productID',
        'status',
        'message',
    ];
}
