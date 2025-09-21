<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returndetail extends Model {

    protected $table = 'returndetail';

    protected $primaryKey = 'returndetailID'; 

    public $timestamps = true; 

    protected $fillable = [
        'returnID',  
        'productID',     
        'quantity', 
        'price',
        'subtotal'
    ];

    public function returnorder() {
        return $this->belongsTo(Returnorder::class, 'returnID', 'returnID');
    }

  
    public function product() {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }
}
