<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchasedetail
 * 
 * @property int $purchaseDetailID
 * @property int $price
 * @property int $quantity
 * @property int $subtotal
 * @property int $status
 * @property int $Product_productID
 * @property int $PurchaseOrder_purchaseID
 * 
 * @property Purchaseorder $purchaseorder
 * @property Product $product
 *
 * @package App\Models
 */
class Purchasedetail extends Model {

	protected $table = 'purchasedetail';
	protected $primaryKey = 'purchasedetailID';
	public $incrementing = true;
	protected $keyType = 'int';  // WAJIB: agar casting ID benar
	public $timestamps = false;

	protected $casts = [
		'purchasedetailID' => 'int',
		'price' => 'int',
		'quantity' => 'int',
		'subtotal' => 'int',
		'status' => 'int',
		'Product_productID' => 'int',
		'PurchaseOrder_purchaseID' => 'int'
	];

	protected $fillable = [
		'price',
		'quantity',
		'returned',
		'subtotal',
		'status',
		'Product_productID',
		'PurchaseOrder_purchaseID'
	];

	public function purchaseorder() {
		return $this->belongsTo(Purchaseorder::class, 'PurchaseOrder_purchaseID');
	}

	public function product() {
		return $this->belongsTo(Product::class, 'Product_productID');
	}
}
