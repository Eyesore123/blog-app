<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if we're using PostgreSQL and have a DATABASE_URL
        if (config('database.default') === 'pgsql' && env('DATABASE_URL')) {
            $databaseUrl = env('DATABASE_URL');
            $dbParts = parse_url($databaseUrl);
            
            if ($dbParts) {
                Config::set('database.connections.pgsql.host', $dbParts['host'] ?? '');
                Config::set('database.connections.pgsql.port', $dbParts['port'] ?? 5432);
                Config::set('database.connections.pgsql.database', ltrim($dbParts['path'] ?? '', '/'));
                Config::set('database.connections.pgsql.username', $dbParts['user'] ?? '');
                Config::set('database.connections.pgsql.password', $dbParts['pass'] ?? '');
            }
        }
        
        // If we have direct PG variables, use those
        if (env('PGHOST') && env('PGPORT') && env('PGDATABASE') && env('PGUSER') && env('PGPASSWORD')) {
            Config::set('database.connections.pgsql.host', env('PGHOST'));
            Config::set('database.connections.pgsql.port', intval(env('PGPORT')));
            Config::set('database.connections.pgsql.database', env('PGDATABASE'));
            Config::set('database.connections.pgsql.username', env('PGUSER'));
            Config::set('database.connections.pgsql.password', env('PGPASSWORD'));
        }
    }
}
