<?php

namespace BangLipai\Utility;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class MigrationServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Blueprint::macro('masterColumn', function (int $singkat = 9, int $keterangan = 50, bool $nullable = true) {
            /** @var Blueprint $this */
            $this->string('singkat', $singkat)->nullable($nullable);
            $this->string('keterangan', $keterangan)->nullable();
        });

        Blueprint::macro('master', function (string $name): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->unsignedTinyInteger("k_$name");
        });

        Blueprint::macro('wkt', function (string $name): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->dateTime("wkt_$name");
        });

        Blueprint::macro('tgl', function (string $name): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->date("tgl_$name");
        });

        Blueprint::macro('is', function (string $name): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->char("is_$name", 1);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
