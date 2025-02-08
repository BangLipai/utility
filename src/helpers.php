<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

if (!function_exists('success')) {
    function success(mixed $data = null, $status = 200, mixed $code = null, mixed $meta = null): JsonResponse
    {
        return Response::success($data, $status, $code, $meta);
    }
}

if (!function_exists('error')) {
    function error(mixed $message = null, mixed $data = null, int $status = 500, ?int $code = null, ?array $meta = null): JsonResponse
    {
        return Response::error($message, $data, $status, $code, $meta);
    }
}

if (!function_exists('number_pagination')) {
    function number_pagination(LengthAwarePaginator $collection, object $loop): string
    {
        return ($collection->currentpage() - 1) * $collection->perpage() + $loop->index + 1;
    }
}

if (!function_exists('parse_no_wa')) {
    function parse_no_wa(string $no_telp): string
    {
        return preg_replace('/^0/', '62', $no_telp);
    }
}

if (!function_exists('prefixActive')) {
    function prefixActive(string $prefixName): string
    {
        return request()->route()->getPrefix() == $prefixName ? 'active' : '';
    }
}

if (!function_exists('prefixBlock')) {
    function prefixBlock(string $prefixName): string
    {
        return request()->route()->getPrefix() == $prefixName ? 'block' : 'none';
    }
}

if (!function_exists('routeActive')) {
    function routeActive(string $routeName): string
    {
        return request()->routeIs($routeName) ? 'active' : '';
    }
}

if (!function_exists('merge')) {
    function merge(...$arrays): array
    {
        return array_merge(...$arrays);
    }
}
