<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Supplier
 * 
 * @property int $supplierID
 * @property string $supplierName
 * @property string $address
 * @property string $telephone
 * @property bool $status
 * 
 * @property Collection|Purchaseorder[] $purchaseorders
 *
 * @package App\Models
 */
class Supplier extends Model {
	protected $table = 'supplier';
	protected $primaryKey = 'supplierID';
	public $timestamps = false;

	protected $casts = [
		'status' => 'bool'
	];

	protected $fillable = [
		'supplierName',
		'address',
		'telephone',
		'status'
	];

	public function purchaseorders() {
		return $this->hasMany(Purchaseorder::class, 'Supplier_supplierID');
	}
}
