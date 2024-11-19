<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class Controller
{
    /**
     * success response method.
     *
     * @param array|LengthAwarePaginator|Model $result
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function sendResponse(array|LengthAwarePaginator|Model $result, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError(string $error, array $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
