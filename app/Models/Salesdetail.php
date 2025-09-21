<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Salesdetail
 * 
 * @property int $salesDetailID
 * @property int $price
 * @property int $quantity
 * @property int $SalesOrder_salesID
 * @property int $Product_productID
 * @property int $subtotal
 * @property int $cost
 * 
 * @property Product $product
 * @property Salesorder $salesorder
 *
 * @package App\Models
 */
class Salesdetail extends Model {
	protected $table = 'salesdetail';
	protected $primaryKey = 'salesdetailID';
	public $incrementing = true;
	protected $keyType = 'int';  // ✅ WAJIB: agar casting ID benar
	public $timestamps = false;

	protected $casts = [
		'salesdetailID' => 'int',
		'price' => 'int',
		'quantity' => 'int',
		'SalesOrder_salesID' => 'int',
		'Product_productID' => 'int',
		'subtotal' => 'int',
		'cost' => 'int',
		'original_price' => 'int'
	];

	protected $fillable = [
		'SalesOrder_salesID',
		'Product_productID',
		'original_price',
		'quantity',
		'returned',
		'price',
		'subtotal',
		'cost',
		'status'
	];



	public function product() {
		return $this->belongsTo(Product::class, 'Product_productID', 'productID');
	}


	public function salesorder() {
		return $this->belongsTo(Salesorder::class, 'SalesOrder_salesID');
	}
}
