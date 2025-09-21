<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Stockmovement
 * 
 * @property int $movementID
 * @property Carbon $created_at
 * @property Carbon $dateMovement
 * @property bool $movementType
 * @property int $quantity
 * @property int|null $sourceReff
 * @property int $Product_productID
 * 
 * @property Product $product
 *
 * @package App\Models
 */
class Stockmovement extends Model {
	protected $table = 'stockmovement';
	protected $primaryKey = 'movementID';
	public $timestamps = false;

	protected $casts = [
		'dateMovement' => 'datetime',
		'movementType' => 'bool',
		'quantity' => 'int',
		'sourceReff' => 'int',
		'Product_productID' => 'int'
	];

	protected $fillable = [
		'dateMovement',
		'movementType',
		'quantity',
		'sourceReff',
		'Product_productID'
	];

	public function product() {
		return $this->belongsTo(Product::class, 'Product_productID');
	}
}
