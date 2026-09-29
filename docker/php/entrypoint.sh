#!/usr/bin/env bash
set -euo pipefail

APP_USER=www-data

# Именованный том mesh-storage монтируется поверх storage/app/meshes и создаётся
# с владельцем root:root 755 — без chown www-data не сможет записать загрузку.
# Остальные каталоги — те, куда Laravel пишет сам.
mkdir -p storage/app/meshes storage/framework/{cache/data,sessions,views} \
         storage/logs bootstrap/cache
chown -R "$APP_USER:$APP_USER" storage bootstrap/cache 2>/dev/null || true

# php-fpm стартует мастером от root и сам опускает воркеров до www-data
# (user/group в zz-app.conf). Всё остальное запускается сразу от www-data.
if [ "${1:-}" = "php-fpm" ]; then
  exec "$@"
fi
exec gosu "$APP_USER" "$@"
