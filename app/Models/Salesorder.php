<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Salesorder
 * 
 * @property int $salesID
 * @property Carbon $created_at
 * @property Carbon $salesDate
 * @property int $totalPrice
 * @property int $totalHPP
 * @property int $totalProfit
 * @property bool $status
 * @property int $Customer_customerID
 * 
 * @property Customer $customer
 * @property Collection|Returnorder[] $returnorders
 * @property Collection|Salesdetail[] $salesdetails
 *
 * @package App\Models
 */
class Salesorder extends Model {
	protected $table = 'salesorder';
	protected $primaryKey = 'salesID';
	public $timestamps = true; // Default sudah true, boleh dihapus

	protected $casts = [
		'salesDate' => 'datetime',
		'totalPrice' => 'int',
		'totalHPP' => 'int',
		'totalProfit' => 'int',
		'discount_order' => 'int',
		'status' => 'bool',
		'isDelivered' => 'boolean', 
		'deliveredAt' => 'datetime',
		'isPaid' => 'boolean',
		'paidAt' => 'datetime',
		'Customer_customerID' => 'int',
		'description' => 'string',
		'Reference' => 'string',
		'amount_paid' => 'decimal:2',
    	'change_amount' => 'decimal:2',
	];

	protected $fillable = [
		'salesDate',
		'Customer_customerID',
		'status',
	    'isDelivered',
		'deliveredAt',
		'isPaid',
		'paidAt',
		'discount_order',
		'description',
		'totalPrice',
		'totalHPP',
		'totalProfit',
		'Reference',
		'payment_type',
    	'amount_paid',
    	'change_amount',
		
	];

	protected static function booted() {
        static::saving(function ($salesOrder) {
            // Auto isi deliveredAt saat isDelivered diubah jadi 1
			if ($salesOrder->isDirty('isDelivered') && $salesOrder->isDelivered == 1 && !$salesOrder->deliveredAt) {
				$salesOrder->deliveredAt = now();
			}

			if ($salesOrder->isDirty('isPaid') && $salesOrder->isPaid == 1 && !$salesOrder->paidAt) {
				$salesOrder->paidAt = now();
			}
        });
    }


	public function customer() {
		return $this->belongsTo(Customer::class, 'Customer_customerID', 'customerID');
	}

	public function returnorders() {
		return $this->hasMany(Returnorder::class, 'SalesOrder_salesID');
	}

	public function details() {
		return $this->hasMany(Salesdetail::class, 'SalesOrder_salesID', 'salesID');
	}
}
