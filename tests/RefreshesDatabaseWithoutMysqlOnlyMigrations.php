<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * RefreshDatabase para sqlite: o histórico de migrations não corre todo em
 * sqlite (algumas só funcionam em MySQL), por isso corre as restantes. Todas
 * as classes de teste com base de dados devem usar este trait, porque o
 * RefreshDatabase só migra uma vez por processo — conjuntos diferentes de
 * migrations entre classes partiam a suite.
 */
trait RefreshesDatabaseWithoutMysqlOnlyMigrations
{
    use RefreshDatabase;

    private const MYSQL_ONLY_MIGRATIONS = [
        '2026_01_12_094536_add_consignment_page.php',
        '2026_05_09_092633_change_rating_to_decimal_in_testimonials_table.php',
        '2026_05_27_000003_create_missing_vehicle_inspection_tables.php',
        '2026_09_05_120000_make_radar_searches_make_nullable.php',
    ];

    protected function migrateFreshUsing()
    {
        $paths = collect(glob(database_path('migrations/*.php')))
            ->reject(fn ($path) => in_array(basename($path), self::MYSQL_ONLY_MIGRATIONS, true))
            ->map(fn ($path) => 'database/migrations/' . basename($path))
            ->values()
            ->all();

        return ['--drop-views' => false, '--path' => $paths];
    }
}
