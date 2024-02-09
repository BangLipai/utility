<?php

namespace BangLipai\Utility\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class Transaksi
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): mixed
    {
        DB::beginTransaction();

        try {
            $result = $next($request);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }

        return $result;
    }
}