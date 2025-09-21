<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchaseorder
 * 
 * @property int $purchaseID
 * @property Carbon $created_at
 * @property Carbon $purchaseDate
 * @property int $totalPrice
 * @property bool $status
 * @property int $Supplier_supplierID
 * 
 * @property Supplier $supplier
 * @property Collection|Purchasedetail[] $purchasedetails
 *
 * @package App\Models
 */
class Purchaseorder extends Model {
	protected $table = 'purchaseorder';
	protected $primaryKey = 'purchaseID';
	public $timestamps = true; // Default sudah true, boleh dihapus

	protected $casts = [
		'purchaseDate' => 'datetime',
		'totalPrice' => 'int',
		'status' => 'bool',
		'Supplier_supplierID' => 'int'
	];

	protected $fillable = [
		'purchaseDate',
		'totalPrice',
		'status',
		'Supplier_supplierID'
	];

	public function supplier() {
		return $this->belongsTo(Supplier::class, 'Supplier_supplierID');
	}

	public function purchasedetails() {
		return $this->hasMany(Purchasedetail::class, 'PurchaseOrder_purchaseID');
	}

	
}
