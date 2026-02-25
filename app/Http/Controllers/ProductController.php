<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(5);
        return ApiResponse::paginated($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation($validator->errors());
        }

        $product = Product::create($request->all());

        return ApiResponse::success($product, "Product Created", 201);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error("Product Not Found", 404);
        }

        return ApiResponse::success($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error("Product Not Found", 404);
        }

        $product->update($request->all());

        return ApiResponse::success($product, "Product Updated");
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error("Product Not Found", 404);
        }

        $product->delete();

        return ApiResponse::success(null, "Product Deleted");
    }
}