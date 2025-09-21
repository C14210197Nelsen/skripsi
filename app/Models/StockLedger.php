<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model {
    protected $table = 'stock_ledger';
    protected $primaryKey = 'stockledgerID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $casts = [
        'qty' => 'integer',
        'saldo_qty' => 'integer',
        'saldo_harga' => 'float',
        'price' => 'float',
        'total_price' => 'float',
        'hpp' => 'float',
        'source_id' => 'integer',
    ];



    protected $fillable = [
        'productID', 'qty', 'saldo_qty', 'saldo_harga',
        'price', 'total_price', 'hpp', 'type',
        'source_type', 'source_id'
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }
}
    
