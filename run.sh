#!/bin/bash
set -e

# prepare Laravel storage dirs
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs

# clear caches
php artisan config:clear

# link storage
php artisan storage:link || true

# run specific migrations (optional)
php artisan migrate --env=local --path=database/migrations/2025_10_01_083657_create_info_banners_table.php --force

# run the rest of the migrations
php artisan migrate --force || true

# generate sitemap
php artisan sitemap:generate || true

# start PHP server
php -S 0.0.0.0:8080 -t public
