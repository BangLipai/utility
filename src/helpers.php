<?php


use Illuminate\Http\JsonResponse;

if (!function_exists('success')) {
    function success($data = null, $status = 200, $code = null, $meta = null): JsonResponse
    {
        return Response::success($data, $status, $code, $meta);
    }
}
