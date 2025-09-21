<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ControllerAjax extends Controller {

    public function getProductInfo($productCode) {
        $product = Product::where('productCode', $productCode)->first();
        if (!$product) return response()->json(['error' => 'Not found'], 404);

        return response()->json([
            'productID' => $product->productID,
            'price' => $product->productPrice,
            'cost' => $product->productCost,
            'productName' => $product->productName,
        ]);
    }

}
