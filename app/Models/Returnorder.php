<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returnorder extends Model {

    protected $table = 'returnorder';

    protected $primaryKey = 'returnID'; 

    public $timestamps = true; 

    protected $fillable = [
        'type',            // 'sales' atau 'purchase'
        'partnerID',       // FK ke customer / supplier
        'sourceID',        // ID dari salesID atau purchaseID
        'returnDate',      
        'status',          
    ];

    public function returndetail() {
        return $this->hasMany(Returndetail::class, 'returnID', 'returnID');
    }
}
