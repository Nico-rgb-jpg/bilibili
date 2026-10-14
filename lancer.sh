#!/usr/bin/env bash
# Lance Bilibili sur http://localhost:8000 (macOS / Linux)
cd "$(dirname "$0")" || exit 1

if ! command -v php >/dev/null 2>&1; then
    echo "PHP est introuvable. macOS : brew install php · Linux : sudo apt install php-cli php-sqlite3"
    exit 1
fi

echo "Bilibili démarre sur http://localhost:8000 (Ctrl+C pour arrêter)"
( sleep 1; (open http://localhost:8000 || xdg-open http://localhost:8000) >/dev/null 2>&1 ) &
php -S localhost:8000 -t public
