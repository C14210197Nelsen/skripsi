<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stockledger;

class ControllerAjax extends Controller {

    public function getProductInfo($productCode) {
        $product = Product::where('productCode', $productCode)->first();
        if (!$product) return response()->json(['error' => 'Not found'], 404);

        $lastLedger = StockLedger::where('productID', $product->productID)
            ->orderBy('stockledgerID', 'desc')
            ->first();

        if ($lastLedger && $lastLedger->saldo_qty > 0 && $lastLedger->saldo_harga > 0) {
            $cost = $lastLedger->saldo_harga / $lastLedger->saldo_qty;
        } else {
            $cost = $product->productCost;
        }

        return response()->json([
            'productID' => $product->productID,
            'price' => $product->productPrice,
            'cost' => round($cost, 2),
            'productName' => $product->productName,
        ]);
    }

}
