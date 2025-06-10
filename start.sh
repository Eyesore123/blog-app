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
php artisan migrate --env=local --path=database/migrations/2025_06_10_084327_add_notify_comments_to_users_table.php

# 8. Start PHP server
php -S 0.0.0.0:8080 -t public