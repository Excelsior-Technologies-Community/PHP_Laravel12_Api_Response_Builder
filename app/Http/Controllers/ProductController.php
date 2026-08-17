<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display products with search, filters,
     * sorting and pagination.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:255'],

            'min_price' => ['nullable', 'numeric', 'min:0'],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'sort_by' => [
                'nullable',
                'in:id,name,price,created_at',
            ],

            'sort_direction' => [
                'nullable',
                'in:asc,desc',
            ],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors(),
                'Invalid filter parameters',
                $request
            );
        }

        $query = Product::query();

        /**
         * Search
         *
         * Searches name and description.
         */
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /**
         * Minimum price filter.
         */
        if ($request->filled('min_price')) {

            $query->where(
                'price',
                '>=',
                $request->min_price
            );
        }

        /**
         * Maximum price filter.
         */
        if ($request->filled('max_price')) {

            $query->where(
                'price',
                '<=',
                $request->max_price
            );
        }

        /**
         * Sorting.
         */
        $sortBy = $request->input(
            'sort_by',
            'created_at'
        );

        $sortDirection = $request->input(
            'sort_direction',
            'asc'
        );

        $query->orderBy(
            $sortBy,
            $sortDirection
        );

        /**
         * Pagination.
         */
        $perPage = (int) $request->input(
            'per_page',
            5
        );

        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::paginated(
            $products,
            'Products retrieved successfully',
            $request
        );
    }

    /**
     * Export filtered products as CSV.
     */
    public function exportCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:255'],

            'min_price' => ['nullable', 'numeric', 'min:0'],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'sort_by' => [
                'nullable',
                'in:id,name,price,created_at',
            ],

            'sort_direction' => [
                'nullable',
                'in:asc,desc',
            ],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors(),
                'Invalid export parameters',
                $request
            );
        }

        $query = Product::query();

        /**
         * Search.
         */
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'description',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /**
         * Minimum price.
         */
        if ($request->filled('min_price')) {

            $query->where(
                'price',
                '>=',
                $request->min_price
            );
        }

        /**
         * Maximum price.
         */
        if ($request->filled('max_price')) {

            $query->where(
                'price',
                '<=',
                $request->max_price
            );
        }

        /**
         * Sorting.
         */
        $sortBy = $request->input(
            'sort_by',
            'created_at'
        );

        $sortDirection = $request->input(
            'sort_direction',
            'desc'
        );

        $query->orderBy(
            $sortBy,
            $sortDirection
        );

        $products = $query->get();

        $filename =
            'products_' .
            now()->format('Y-m-d_H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($products) {

                $handle = fopen('php://output', 'w');

                /**
                 * CSV header.
                 */
                fputcsv($handle, [
                    'ID',
                    'Name',
                    'Description',
                    'Price',
                    'Created At',
                ]);

                /**
                 * CSV rows.
                 */
                foreach ($products as $product) {

                    fputcsv($handle, [
                        $product->id,
                        $product->name,
                        $product->description,
                        $product->price,
                        $product->created_at,
                    ]);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors(),
                'Validation Error',
                $request
            );
        }

        $product = Product::create(
            $validator->validated()
        );

        return ApiResponse::success(
            $product,
            'Product Created',
            201,
            $request
        );
    }

    /**
     * Display a single product.
     */
    public function show(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404,
                null,
                $request
            );
        }

        return ApiResponse::success(
            $product,
            'Product retrieved successfully',
            200,
            $request
        );
    }

    /**
     * Update a product.
     */
    public function update(
        Request $request,
        $id
    ) {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404,
                null,
                $request
            );
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'price' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                ],
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors(),
                'Validation Error',
                $request
            );
        }

        $product->update(
            $validator->validated()
        );

        return ApiResponse::success(
            $product->fresh(),
            'Product Updated',
            200,
            $request
        );
    }

    /**
     * Delete a product.
     */
    public function destroy(
        Request $request,
        $id
    ) {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product Not Found',
                404,
                null,
                $request
            );
        }

        $product->delete();

        return ApiResponse::success(
            null,
            'Product Deleted',
            200,
            $request
        );
    }
}
