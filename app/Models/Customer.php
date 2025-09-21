<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer
 * 
 * @property int $customerID
 * @property string $customerName
 * @property string $address
 * @property string $telephone
 * @property bool $status
 * 
 * @property Collection|Salesorder[] $salesorders
 *
 * @package App\Models
 */
class Customer extends Model {
	protected $table = 'customer';
	protected $primaryKey = 'customerID';
	public $timestamps = false;

	protected $casts = [
		'status' => 'bool'
	];

	protected $fillable = [
		'customerName',
		'address',
		'telephone',
		'status'
	];

	public function salesorders() {
		return $this->hasMany(Salesorder::class, 'Customer_customerID', 'customerID');
	}

}
