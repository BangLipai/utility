<?php

namespace BangLipai\Utility\Database;

use DB;
use Schema;

trait SqlLoader
{
    protected function loadSql(string $table): void
    {
        $directory = property_exists($this, 'sqlPath') ? $this->sqlPath : 'sql';
        $path = implode(DIRECTORY_SEPARATOR, array_filter([database_path("Seeders\\$directory"), "$table.json"]));

        Schema::disableForeignKeyConstraints();
        DB::unprepared(file_get_contents($path));
        Schema::enableForeignKeyConstraints();
    }
}
