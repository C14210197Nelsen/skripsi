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
 * @property Carbon $purchaseDate
 * @property int $totalPrice
 * @property string|null $description
 * @property bool $status
 * @property bool $isReceived
 * @property Carbon|null $receivedAt
 * @property bool $isPaid
 * @property Carbon|null $paidAt
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
	public $timestamps = true;

    protected $casts = [
        'purchaseDate'    => 'date',
        'totalPrice'      => 'int',
        'status'          => 'bool',
        'isReceived'      => 'bool',
        'receivedAt'      => 'datetime',
        'isPaid'          => 'bool',
        'paidAt'          => 'datetime',
        'Supplier_supplierID' => 'int',
    ];

    protected $fillable = [
        'purchaseDate',
        'totalPrice',
        'description',
        'status',
        'isReceived',
        'receivedAt',
        'isPaid',
        'paidAt',
        'Supplier_supplierID',
    ];

	protected static function booted() {
		static::saving(function ($purchaseOrder) {
			// Auto isi receivedAt saat isReceived diubah jadi 1
			if ($purchaseOrder->isDirty('isReceived') && $purchaseOrder->isReceived == 1 && !$purchaseOrder->receivedAt) {
				$purchaseOrder->receivedAt = now();
			}

			// Auto isi paidAt saat isPaid diubah jadi 1
			if ($purchaseOrder->isDirty('isPaid') && $purchaseOrder->isPaid == 1 && !$purchaseOrder->paidAt) {
				$purchaseOrder->paidAt = now();
			}
		});
	}



	public function supplier() {
		return $this->belongsTo(Supplier::class, 'Supplier_supplierID');
	}

	public function purchasedetails() {
		return $this->hasMany(Purchasedetail::class, 'PurchaseOrder_purchaseID');
	}

	
}
