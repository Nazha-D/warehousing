<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

trait ApiResponseTrait
{
//    protected function successResponse($data = [], $message = '', $status = 200)
//    {
//        return response()->json([
//            'success' => true,
//            'message' => $message,
//            'data'    => $data,
//        ], $status);
//    }
    protected function successResponse($data = [], $message = '', $status = 200)
    {
        if ($data instanceof AnonymousResourceCollection
            && $data->resource instanceof AbstractPaginator) {

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $data->collection,
                'meta'    => [
                    'current_page' => $data->resource->currentPage(),
                    'last_page'    => $data->resource->lastPage(),
                    'per_page'     => $data->resource->perPage(),
                    'total'        => $data->resource->total(),
                ],
                'links' => [
                    'first' => $data->resource->url(1),
                    'last'  => $data->resource->url($data->resource->lastPage()),
                    'prev'  => $data->resource->previousPageUrl(),
                    'next'  => $data->resource->nextPageUrl(),
                ],
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
//    protected function errorResponse($message = '', $status = 500, $data = [])
//    {
//        return response()->json([
//            'success' => false,
//            'message' => $message,
//            'data'    => $data,
//        ], $status);
//    }
//
    protected function errorResponse(
        string $message = '',
        int $status = 500,
        array $errors = []
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
