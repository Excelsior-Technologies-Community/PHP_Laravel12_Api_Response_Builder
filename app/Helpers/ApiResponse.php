<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiResponse
{
    /**
     * Get common API metadata.
     */
    private static function meta(Request $request = null): array
    {
        $request = $request ?: request();

        return [
            'api_version' => 'v1',
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID'),
        ];
    }

    /**
     * Get rate limit metadata.
     */
    private static function rateLimitMeta(Request $request = null): array
    {
        $request = $request ?: request();

        return [
            'limit' => $request->attributes->get('rate_limit_limit'),
            'remaining' => $request->attributes->get('rate_limit_remaining'),
            'reset_at' => $request->attributes->get('rate_limit_reset_at'),
        ];
    }

    /**
     * Standard success response.
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200,
        ?Request $request = null
    ): JsonResponse {
        $request = $request ?: request();

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::meta($request),
            'rate_limit' => self::rateLimitMeta($request),
        ], $code);
    }

    /**
     * Standard error response.
     */
    public static function error(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null,
        ?Request $request = null
    ): JsonResponse {
        $request = $request ?: request();

        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => self::meta($request),
            'rate_limit' => self::rateLimitMeta($request),
        ], $code);
    }

    /**
     * Validation error response.
     */
    public static function validation(
        mixed $errors,
        string $message = 'Validation Error',
        ?Request $request = null
    ): JsonResponse {
        $request = $request ?: request();

        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => self::meta($request),
            'rate_limit' => self::rateLimitMeta($request),
        ], 422);
    }

    /**
     * Standard paginated response.
     */
    public static function paginated(
        LengthAwarePaginator $data,
        string $message = 'Data fetched successfully',
        ?Request $request = null
    ): JsonResponse {
        $request = $request ?: request();

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

            'meta' => self::meta($request),

            'rate_limit' => self::rateLimitMeta($request),

        ]);
    }
}
