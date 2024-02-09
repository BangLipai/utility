<?php

namespace BangLipai\Utility\Database;

use Arr;
use DB;
use Schema;

trait MasterLoader
{
    protected function load(string $table): void
    {
        $directory = property_exists($this, 'masterPath') ? $this->masterPath : 'master';

        $path = implode(DIRECTORY_SEPARATOR, array_filter([database_path("Seeders\\$directory"), "$table.json"]));
        $data = file_get_contents($path);
        $json = json_decode($data, true);

        $columns = Schema::getColumnListing($table);

        foreach (array_chunk($json ?: [], 1000) as $data) {
            $value = Arr::map($data, fn($item) => Arr::only($item, $columns));
            DB::table($table)
                ->insertOrIgnore($value);
        }
    }
}
