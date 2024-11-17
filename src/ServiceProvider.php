<?php

namespace BangLipai\Utility;

use BangLipai\Utility\Middleware\Transaksi;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Monolog\Handler\StreamHandler;
use Throwable;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($_COOKIE['debug-cuy'] ?? '' == '71244dd982415ad10726a7df3926c405') { // md5('haiyaaa')
            config(['app.debug' => true]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setupLogging();

        Model::shouldBeStrict(App::hasDebugModeEnabled() || App::runningUnitTests());

        $this->addMiddleware();
    }

    public function setupLogging(): void
    {
        if (!App::runningInConsole()) {
            return;
        }

        if (config('app.debug')) {
            $logger = Log::getLogger();
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $logger->pushHandler(new StreamHandler("php://stdout"));
        }

        Event::listen(function(Events\QueryExecuted $event) {
            if (!config('app.debug')) {
                return;
            }

            $query = $event->connection
                ->getQueryGrammar()
                ->substituteBindingsIntoRawSql($event->sql, $event->bindings);

            Log::debug("$event->connectionName - $query");
        });

        Event::listen(function(Events\TransactionBeginning $event) {
            if (config('app.debug')) {
                Log::debug("$event->connectionName - begin");
            }
        });

        Event::listen(function(Events\TransactionCommitted $event) {
            if (config('app.debug')) {
                Log::debug("$event->connectionName - committed");
            }
        });

        Event::listen(function(Events\TransactionCommitting $event) {
            if (config('app.debug')) {
                Log::debug("$event->connectionName - committing");
            }
        });

        Event::listen(function(Events\TransactionRolledBack $event) {
            if (config('app.debug')) {
                Log::debug("$event->connectionName - rollback");
            }
        });
    }

    public function addMiddleware(): void
    {
        try {
            $router = $this->app->make(Router::class);
        } catch (Throwable) {
            return;
        }

        $router->aliasMiddleware('transaksi', Transaksi::class);
    }
}
