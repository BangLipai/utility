<?php

namespace BangLipai\Test\Trait;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Routing\Events\Routing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin TestCase
 */
trait LogRequest
{
    public function setUpLogRequest(): void
    {
        Event::listen(function (Routing $event) {
            if (config('app.debug')) {
                Log::debug('req >> ' . $event->request->method() . ' ' . $event->request->fullUrl());
            }
        });

        Event::listen(function (RequestHandled $event) {
            if (config('app.debug')) {
                Log::debug('resp << ' . $event->response->getStatusCode() . ' ' . (Response::$statusTexts[$event->response->getStatusCode()] ?? ''));
            }
        });
    }
}
