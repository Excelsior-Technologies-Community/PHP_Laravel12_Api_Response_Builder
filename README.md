# PHP_Laravel12_Api_Response_Builder
Project Name: laravel-api-response-builder

This project creates a standardized API response structure in Laravel 12 using a reusable Response Builder.

---

PROJECT GOAL

Create a simple REST API with:

* Standard Success Response
* Standard Error Response
* Validation Error Response
* Pagination Response
* Clean JSON structure
* Reusable API Response Helper

---

STEP 1: Create New Laravel 12 Project

```bash
composer create-project laravel/laravel laravel-api-response-builder
cd laravel-api-response-builder
php artisan serve
```

---

STEP 2: Setup Database

Update .env file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_response_builder
DB_USERNAME=root
DB_PASSWORD=
```

Create database:

```sql
CREATE DATABASE api_response_builder;
```

---

STEP 3: Create Sample Model (Product)

```bash
php artisan make:model Product -mcr
```

Update migration file:

```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->timestamps();
    });
}
```

Run migration:

```bash
php artisan migrate
```

---

STEP 4: Create API Response Builder

Create folder:

app/Helpers

Create file:

app/Helpers/ApiResponse.php

ApiResponse.php

```php
<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = "Success", $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = "Error", $code = 400, $errors = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public static function validation($errors, $message = "Validation Error")
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    public static function paginated($data, $message = "Data fetched successfully")
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ]
        ]);
    }
}
```

---

STEP 5: Use Response Builder in Controller

Update ProductController:

```php
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
```

---

STEP 6: Enable Mass Assignment

Update Product model:

```php
protected $fillable = ['name', 'description', 'price'];
```

---

STEP 7: Define API Routes

Update routes/api.php:

```php
use App\Http\Controllers\ProductController;

Route::apiResource('products', ProductController::class);
```

<img width="1384" height="945" alt="image" src="https://github.com/user-attachments/assets/e19e7657-7203-438b-af2d-035ccdd03c54" />

---

STEP 8: Test API

Use Postman, Thunder Client, or curl.

Example Success Response

```json
{
  "status": true,
  "message": "Product Created",
  "data": {
    "id": 1,
    "name": "Gaming Mouse",
    "description": "RGB Mouse",
    "price": "1999.00"
  }
}
```

Example Validation Error

```json
{
  "status": false,
  "message": "Validation Error",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

FINAL PROJECT STRUCTURE

app/
├── Helpers/
│     └── ApiResponse.php
├── Http/Controllers/
│     └── ProductController.php
├── Models/
│     └── Product.php
routes/
└── api.php

---

WHAT YOU LEARNED

* Create reusable API Response Builder
* Standardize JSON structure
* Handle validation properly
* Handle pagination
* Clean REST API structure
* Better scalable backend design
