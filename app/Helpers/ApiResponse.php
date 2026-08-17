<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Get common API metadata.
     */
    private static function meta(): array
    {
        return [
            'api_version' => 'v1',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Standard success response.
     */
    public static function success(
        $data = null,
        string $message = 'Success',
        int $code = 200
    ) {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::meta(),
        ], $code);
    }

    /**
     * Standard error response.
     */
    public static function error(
        string $message = 'Error',
        int $code = 400,
        $errors = null
    ) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => self::meta(),
        ], $code);
    }

    /**
     * Standard validation error response.
     */
    public static function validation(
        $errors,
        string $message = 'Validation Error'
    ) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => self::meta(),
        ], 422);
    }

    /**
     * Standard paginated response.
     */
    public static function paginated(
        LengthAwarePaginator $data,
        string $message = 'Data fetched successfully'
    ) {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
            'meta' => self::meta(),
        ]);
    }
}