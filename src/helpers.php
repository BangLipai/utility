<?php


use Illuminate\Http\JsonResponse;

if (!function_exists('success')) {
    function success($data = null, $status = 200, $code = null, $meta = null): JsonResponse
    {
        return Response::success($data, $status, $code, $meta);
    }
}

if (!function_exists('error')) {
    function error($message = null, $data = null, int $status = 500, int $code = null, array $meta = null): JsonResponse
    {
        return Response::error($message, $data, $status, $code, $meta);
    }
}
