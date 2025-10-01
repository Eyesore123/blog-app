#!/bin/bash
set -e

# 1. Build assets
npm run build

# 2. Move/copy assets, copy manifest, create symlinks
npm run move-vite-assets
npm run copy-manifest
npm run create-symlinks

# 3. Prepare storage
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

# 4. (Optional) Set permissions if needed
# chmod -R 777 storage

# 5. Clear config cache
php artisan config:clear

# 6. Fix Laravel storage symlink
php artisan storage:link

# 7. Run migrations
# php artisan migrate:reset --force
# php artisan migrate --force
# Uncomment the following line to run a specific migration
# php artisan migrate --env=local --path=database/migrations/2025_06_11_054839_add_profile_photo_path_to_users_table.php
# php artisan migrate --env=local --path=database/migrations/2025_06_29_053854_update_tags_sequence.php --force
php artisan migrate --env=local --path=database/migrations/2025_10_01_083657_create_info_banners_table.php --force

# php artisan migrate --env=local --path=database/migrations/2025_09_19_083656_create_hugs_table.php --force


# 8. Generate sitemap
php artisan sitemap:generate

# 9. Start PHP server
# php -S 0.0.0.0:8080 -t public