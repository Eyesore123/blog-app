#!/bin/bash
set -e

# prepare Laravel storage dirs
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs

# clear caches
php artisan config:clear

# link storage
php artisan storage:link || true

# run specific migrations (optional)

# php artisan migrate --env=local --path=database/migrations/2025_10_01_083657_create_info_banners_table.php --force
# php artisan migrate --env=local --path=database/migrations/2025_10_03_133614_add_user_name_and_guest_name_to_comments_table.php --force

# php artisan migrate --env=local --path=database/migrations/2025_10_04_072918_create_trivia_table.php --force

php artisan migrate --env=local --path=database/migrations/2025_10_04_104903_create_news_table.php --force

# run the rest of the migrations
php artisan migrate --force || true

# generate sitemap
php artisan sitemap:generate || true

# start PHP server
php -S 0.0.0.0:8080 -t public
