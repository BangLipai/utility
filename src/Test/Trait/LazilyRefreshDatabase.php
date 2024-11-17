<?php

namespace BangLipai\Utility\Test\Trait;

use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseTransactionsManager;
use Illuminate\Foundation\Testing\RefreshDatabase as BaseTrait;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

trait LazilyRefreshDatabase
{
    use BaseTrait {
        refreshDatabase as baseRefreshDatabase;
    }

    protected function connections(): array
    {
        return property_exists($this, 'connections')
            ? $this->connections
            : [
                config('database.default') => [
                    'migration' => 'database\migrations',
                    'seeder'    => 'DatabaseSeeder',
                    'files'     => [
                        'migrations/*.php',
                        'seeders/DatabaseSeeder.php',
                        'seeders/MasterLoader.php',
                        'seeders/master/*.json',
                    ],
                ],
            ];
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact(): array
    {
        return array_keys($this->connections());
    }

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     * @throws Throwable
     */
    public function beginDatabaseTransaction(): void
    {
        // override untuk menghilangkan suppress event agar info begin, commit dan rollback tetap terlog

        $database = $this->app->make('db');

        $this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager);

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);
            $connection->setTransactionManager($transactionsManager);

            $connection->beginTransaction();
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach (array_reverse($this->connectionsToTransact()) as $name) {
                $connection = $database->connection($name);

                $connection->rollBack();
                $connection->disconnect();
            }
        });
    }

    /**
     * Refresh a conventional test database.
     *
     * @return void
     * @throws Throwable
     */
    protected function refreshTestDatabase(): void
    {
        if (!RefreshDatabaseState::$migrated) {
            $migrates = Arr::map($this->connections(), function ($connection, $key) {
                $args = [
                    '--database' => $key,
                    '--path'     => $connection['migration'],
                ];

                if (isset($connection['seeder'])) {
                    $args['--seeder'] = $connection['seeder'];
                }

                return $args;
            });

            foreach ($migrates as $name => $migrate) {
                $checksumPath = database_path('db.checksum');
                $checksums    = json_decode(file_exists($checksumPath) ? file_get_contents($checksumPath) : '', true);
                if (!is_array($checksums)) {
                    $checksums = [];
                }

                $currSourceHash = $this->calculateSourceHash($name);
                $prevSourceHash = $checksums[$name]['source'] ?? null;
                if ($prevSourceHash == $currSourceHash) {
                    $currSourceHash = null;
                } else {
                    Log::debug("Hash source $name berbeda. Sebelumnya $prevSourceHash dan sekarang $currSourceHash ");
                }

                $currTargetHash = $this->calculateTargetHash($name);
                $prevTargetHash = $checksums[$name]['target'] ?? null;
                if ($prevTargetHash == $currTargetHash) {
                    $currTargetHash = null;
                } else {
                    Log::debug("Hash target $name berbeda. Sebelumnya $prevTargetHash dan sekarang $currTargetHash ");
                }

                if ($currSourceHash || $currTargetHash) {
                    $this->artisan('migrate:fresh', $migrate);
                    $this->app[Kernel::class]->setArtisan(null);

                    $class = 'Seeder\\' . Str::studly($name) . 'Seeder';
                    if (file_exists(base_path(str_replace('\\', '/', "tests/$class.php")))) {
                        $this->artisan('db:seed', [
                            '--database' => $name,
                            '--class'    => "Tests\\$class",
                        ]);
                        $this->app[Kernel::class]->setArtisan(null);
                    }
                }

                if ($currSourceHash) {
                    $checksums[$name]['source'] = $currSourceHash;
                }

                if ($currTargetHash) {
                    // hitung ulang hash target karena bisa jadi ada perubahan / alter
                    $checksums[$name]['target'] = $this->calculateTargetHash($name);
                }

                file_put_contents($checksumPath, json_encode($checksums, JSON_PRETTY_PRINT));
            }

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    protected function calculateSourceHash(string $name): string
    {
        $files = [
            base_path('tests/Seeder/' . Str::studly($name) . 'Seeder.php'),
            base_path('tests/Seeder/Master/' . Str::studly($name) . '/*'),
        ];

        $paths = [];
        foreach ($files as $file) {
            $paths = array_merge($paths, glob($file));
        }

        $base = database_path();
        foreach ($this->connections[$name]['files'] ?? [] as $file) {
            $file  = $base . '/' . $file;
            $paths = array_merge($paths, glob($file));
        }

        $files = [];
        while ($path = array_shift($paths)) {
            if (is_file($path)) {
                $files[] = $path;
                continue;
            }

            if (!is_dir($path)) {
                continue;
            }

            $dir = dir($path);
            while (false != ($subfile = $dir->read())) {
                if ($subfile != '.' && $subfile != '..') {
                    $paths[] = $path . DIRECTORY_SEPARATOR . $subfile;
                }
            }
        }

        sort($files);

        $hash = [];
        foreach ($files as $file) {
            $hash[] = md5_file($file);
        }

        return md5(implode('-', $hash));
    }

    protected function calculateTargetHash(string $name): string
    {
        $database   = $this->app->make('db');
        $connection = $database->connection($name);

        return $connection->scalar("
SELECT MD5(GROUP_CONCAT(MD5(CONCAT(
    TABLE_NAME,
    COALESCE(COLUMN_NAME, ''),
    ORDINAL_POSITION,
    COALESCE(COLUMN_DEFAULT, ''),
    IS_NULLABLE,
    COALESCE(DATA_TYPE, ''),
    COALESCE(CHARACTER_MAXIMUM_LENGTH, ''),
    COALESCE(CHARACTER_OCTET_LENGTH, ''),
    COALESCE(NUMERIC_PRECISION, ''),
    COALESCE(NUMERIC_SCALE, ''),
    COALESCE(DATETIME_PRECISION, ''),
    COALESCE(CHARACTER_SET_NAME, ''),
    COALESCE(COLLATION_NAME, ''),
    COLUMN_TYPE,
    COLUMN_KEY
)))) AS 'hash'
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = '{$connection->getDatabaseName()}';
            ",
        ) ?: md5(Carbon::now()->toString());
    }

    public function refreshDatabase(): void
    {
        $database = $this->app->make('db');

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);

            $connection->beforeExecuting(function () {
                if (!RefreshDatabaseState::$lazilyRefreshed) {
                    RefreshDatabaseState::$lazilyRefreshed = true;
                    $this->baseRefreshDatabase();
                }
            });
        }

        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$lazilyRefreshed = false;
        });
    }
}
