#!/usr/bin/env bash
set -euo pipefail

log() { printf '\033[0;35m[vite]\033[0m %s\n' "$*" >&2; }

# Фронтенд появляется вместе с Laravel — до этого просто ждём, а не падаем.
while [ ! -f package.json ]; do
  log "package.json не найден, жду установки приложения"
  sleep 5
done

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
  log "ставлю зависимости"
  npm install
fi

log "dev server на 0.0.0.0:${VITE_PORT:-5173}"
exec npm run dev -- --host 0.0.0.0 --port "${VITE_PORT:-5173}"
