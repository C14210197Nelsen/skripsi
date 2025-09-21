<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Forecast
 * 
 * @property int $forecastID
 * @property int $month
 * @property int $year
 * @property int $forecast
 * @property Carbon $created_at
 * @property int $Product_productID
 * 
 * @property Product $product
 *
 * @package App\Models
 */
class Forecast extends Model
{
	protected $table = 'forecast';
	protected $primaryKey = 'forecastID';
	public $timestamps = false;

	protected $casts = [
		'month' => 'int',
		'year' => 'int',
		'forecast' => 'int',
		'Product_productID' => 'int'
	];

	protected $fillable = [
		'month',
		'year',
		'forecast',
		'Product_productID'
	];

	public function product()
	{
		return $this->belongsTo(Product::class, 'Product_productID');
	}
}
