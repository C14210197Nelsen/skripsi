<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Spending
 * 
 * @property int $spendingID
 * @property Carbon $dateSpending
 * @property string $keterangan
 * @property int $amount
 *
 * @package App\Models
 */
class Spending extends Model {
	protected $table = 'spending';
	protected $primaryKey = 'spendingID';
	public $timestamps = false;

	protected $casts = [
		'dateSpending' => 'datetime',
		'amount' => 'int'
	];

	protected $fillable = [
		'dateSpending',
		'keterangan',
		'amount'
	];
}
