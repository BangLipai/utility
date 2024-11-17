<?php

namespace BangLipai\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Testing\TestResponse;

class ResponseServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        Response::macro('format', function($data, int|null $code, array|null $meta): array {
            if ($data instanceof AbstractPaginator) {
                $data = $data->toArray();

                $meta = array_merge($data, $meta ?: []);
                $data = $meta['data'];
                unset($meta['data']);
            }

            $res = array_filter([
                'data' => $data,
                'code' => $code,
            ], fn($v) => $v !== null);

            if ($meta) {
                foreach ($meta as $key => $value) {
                    if (!isset($res[$key])) {
                        $res[$key] = $value;
                    }
                }
            }

            return $res;
        });

        Response::macro('formatSuccess', function($data, int|null $code, array|null $meta): array {
            return array_merge([
                'success' => true,
            ], Response::format($data, $code, $meta));
        });

        Response::macro('formatError', function(string|null $message, $data, int|null $code, array|null $meta): array {
            return array_merge([
                'success' => false,
            ], array_filter([
                'message' => $message,
            ]), Response::format($data, $code, $meta));
        });

        Response::macro('success', function($data = null, int $status = 200, int $code = null, array $meta = null): JsonResponse {
            $res = Response::formatSuccess($data, $code, $meta);
            return Response::json($res, $status);
        });

        Response::macro('error', function($message = null, $data = null, int $status = 500, int $code = null, array $meta = null): JsonResponse {
            $res = Response::formatError($message, $data, $code, $meta);
            return Response::json($res, $status);
        });

        TestResponse::macro('assertSuccess', function($data = null, int $status = 200, int $code = null, array $meta = null): TestResponse {
            $res = Response::formatSuccess($data, $code, $meta);

            /** @var $this TestResponse */
            $this->assertStatus($status)
                ->assertJson($res);

            return $this;
        });

        TestResponse::macro('assertError', function($message = null, $data = null, int $status = 500, int $code = null, array $meta = null): TestResponse {
            $res = Response::formatError($message, $data, $code, $meta);

            /** @var $this TestResponse */
            $this->assertStatus($status)
                ->assertJson($res);

            return $this;
        });
    }
}
