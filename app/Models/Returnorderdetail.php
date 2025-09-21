<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Returnorderdetail
 * 
 * @property int $returnDetailID
 * @property int $quantity
 * @property bool $cond
 * @property int $ReturnOrder_returnID
 * @property int $Product_productID
 * 
 * @property Product $product
 * @property Returnorder $returnorder
 *
 * @package App\Models
 */
class Returnorderdetail extends Model {
	protected $table = 'returnorderdetail';
	protected $primaryKey = 'returnDetailID';
	public $timestamps = false;

	protected $casts = [
		'quantity' => 'int',
		'cond' => 'bool',
		'ReturnOrder_returnID' => 'int',
		'Product_productID' => 'int'
	];

	protected $fillable = [
		'quantity',
		'cond',
		'ReturnOrder_returnID',
		'Product_productID'
	];

	public function product() {
		return $this->belongsTo(Product::class, 'Product_productID');
	}

	public function returnorder() {
		return $this->belongsTo(Returnorder::class, 'ReturnOrder_returnID');
	}
}
