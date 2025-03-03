<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send a successful response with a message and optional data.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 200)
    {
        $response = ['message' => $message];

        if ($data !== null) {
            if (is_string($data)) {
                $response['data'] = $data;
            } else {
                $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
            }
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $errors = [])
    {
        $response = ['message' => $message];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Generate pagination metadata for collections.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @return array
     */
    protected function getPaginationData($paginator)
    {
        return [
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ];
    }

    /**
     * Format a validation error array into a standardized structure.
     *
     * @param array $errors
     * @return array
     */
    protected function formatValidationErrors(array $errors)
    {
        $formatted = [];
        foreach ($errors as $field => $messages) {
            $formatted[] = [
                'field' => $field,
                'messages' => $messages
            ];
        }

        return $formatted;
    }
}
