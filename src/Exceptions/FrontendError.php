<?php

namespace BangLipai\Utility\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class FrontendError extends BaseException
{
    protected int $status = 200;

    public function render(): JsonResponse
    {
        $meta = array_merge([
            'message' => $this->getMessage(),
        ], $this->formatMeta() ?: []);

        return Response::success(
            $this->data,
            $this->status,
            $this->getCode(),
            $meta,
        );
    }
}
