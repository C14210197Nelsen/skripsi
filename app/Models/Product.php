<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $productID
 * @property string $productCode
 * @property string $productName
 * @property int $productPrice
 * @property int $productCost
 * @property string $productType
 * @property int|null $stock
 * @property int|null $minStock
 * @property bool|null $status
 * 
 * @property Collection|Forecast[] $forecasts
 * @property Collection|Purchasedetail[] $purchasedetails
 * @property Collection|Returnorderdetail[] $returnorderdetails
 * @property Collection|Salesdetail[] $salesdetails
 * @property Collection|Stockmovement[] $stockmovements
 *
 * @package App\Models
 */
class Product extends Model {
	protected $table = 'product';
	protected $primaryKey = 'productID';
	public $timestamps = false;

	protected $casts = [
		'productPrice' => 'int',
		'productCost' => 'int',
		'status' => 'bool',
		'minStock' => 'int'
	];

	protected $fillable = [
		'productCode',
		'productName',
		'productPrice',
		'productCost',
		'productType',
		'status',
		'minStock',
		'stock'
	];

	//Relation
	public function forecasts() {
		return $this->hasMany(Forecast::class, 'Product_productID');
	}

	public function purchasedetails() {
		return $this->hasMany(Purchasedetail::class, 'Product_productID');
	}

	public function returnorderdetails() {
		return $this->hasMany(Returnorderdetail::class, 'Product_productID');
	}

	public function salesdetails() {
		return $this->hasMany(Salesdetail::class, 'Product_productID');
	}

	public function stockmovements() {
		return $this->hasMany(Stockmovement::class, 'Product_productID');
	}

	public function stockLedgers() {
		return $this->hasMany(StockLedger::class, 'productID', 'productID')
					->orderByDesc('stockledgerID');
	}
	

	// Mengambil Stock Ledger terakhir
	public function latestStockLedger() {
		return $this->hasOne(StockLedger::class, 'productID', 'productID')
					->orderByDesc('stockledgerID');
	}

	// Mengambil data stock dari Stock Ledger terakhir
	public function getStock() {
		$last = $this->stockLedgers()->latest('created_at')->first();
		return $last ? $last->saldo_qty : 0;
	}



	// Mengambil HPP dari Stock Ledger terakhir
	public function getHPP() {
		$last = $this->stockLedgers()->latest('created_at')->first();
		return $last && $last->saldo_qty > 0 ? round($last->saldo_harga / $last->saldo_qty) : 0;
	}


	public function getProduct($code) {
        $product = Product::where('productCode', $code)->first();
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'price' => $product->price,
            'productName' => $product->productName,
        ]);
    }

}
