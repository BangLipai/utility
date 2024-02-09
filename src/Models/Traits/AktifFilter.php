<?php

namespace BangLipai\Utility\Models\Traits;

use BangLipai\Utility\Models\Scopes\AktifFilterScope;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;

/**
 * @mixin Eloquent\Model
 *
 * @property int $is_aktif
 *
 * @method static Eloquent\Builder|Query\Builder withNonAktif(bool $withNonAktif = true)
 * @method static Eloquent\Builder|Query\Builder onlyNonAktif()
 */
trait AktifFilter
{
    public static function bootAktifFilter(): void
    {
        static::addGlobalScope(new AktifFilterScope());
    }

    public function isAktif(): bool
    {
        return (bool)$this->is_aktif;
    }
}
