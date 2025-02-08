<?php

namespace BangLipai\Utility\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Throwable;

abstract class BaseException extends Exception
{
    protected int $status = 500;

    public function __construct(
        string           $message = "",
        int              $code = 0,
        protected mixed  $data = null,
        protected ?array $meta = null,
        ?int             $status = null,
        ?Throwable       $previous = null,
    ) {
        parent::__construct($message, $code ?: $status ?: $this->status, $previous);

        if ($status !== null) {
            $this->status = $status;
        }
    }

    protected function formatMeta(): array|null
    {
        if (!config('app.debug')) {
            return null;
        }

        return [
            'exception' => get_class($this),
            'file'      => $this->getFile(),
            'line'      => $this->getLine(),
            'trace'     => collect($this->getTrace())->map(fn($trace) => Arr::except($trace, ['args']))->all(),
        ];
    }

    public function render(): JsonResponse
    {
        return Response::error(
            $this->getMessage(),
            $this->data,
            $this->status,
            $this->getCode(),
            $this->formatMeta(),
        );
    }
}
