<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a paginated list of products.
     */
    public function index()
    {
        $products = Product::paginate(5);

        return ApiResponse::paginated($products);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors()
            );
        }

        $product = Product::create(
            $validator->validated()
        );

        return ApiResponse::success(
            $product,
            'Product Created',
            201
        );
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404
            );
        }

        return ApiResponse::success($product);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors()
            );
        }

        $product->update(
            $validator->validated()
        );

        return ApiResponse::success(
            $product,
            'Product Updated'
        );
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404
            );
        }

        $product->delete();

        return ApiResponse::success(
            null,
            'Product Deleted'
        );
    }
}