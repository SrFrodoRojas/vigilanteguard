#!/usr/bin/env bash
set -euo pipefail

PROJ="vigilante"
DEV_DIR="/var/www/html/vigilante-dev"
PROD_DIR="/var/www/html/vigilante"
DOMAIN="vigilante.rosaamara.online"

DB_NAME="vigilante"
DB_USER="root"
DB_PASS="@info2016!"
BACKUP_DIR="/var/www/html/backups/$PROJ"
TS=$(date +%F_%H-%M-%S)

command -v rsync >/dev/null || { echo "❌ Falta rsync"; exit 1; }
sudo mkdir -p "$BACKUP_DIR"

echo "🛢️ Backup DB PROD…"
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/${PROJ}_db_${TS}.sql" || { echo "❌ Dump falló"; exit 1; }

echo "📦 Backup código PROD (zip liviano)…"
sudo zip -qr "$BACKUP_DIR/${PROJ}_code_${TS}.zip" "$PROD_DIR" \
  -x "$PROD_DIR/vendor/*" "$PROD_DIR/node_modules/*" "$PROD_DIR/storage/logs/*" "$PROD_DIR/public/storage/*" "$PROD_DIR/.git/*" || true

echo "🧰 Modo mantenimiento…"
cd "$PROD_DIR" && php artisan down || true

echo "🔁 Sync DEV → PROD (sin .env, sin vendor/node_modules)…"
sudo rsync -a --delete \
  --exclude ".env" \
  --exclude "vendor/" \
  --exclude "node_modules/" \
  --exclude ".git/" \
  --exclude "storage/logs/" \
  --exclude "public/storage/" \
  "$DEV_DIR"/ "$PROD_DIR"/

echo "📚 Composer (prod)…"
cd "$PROD_DIR"
composer install --no-dev --optimize-autoloader

# npm ci && npm run build     # si aplica

echo "🗃️ Migraciones…"
php artisan migrate --force

echo "🧹 Limpiar y cachear…"
php artisan storage:link || true
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache

echo "🔐 Permisos…"
sudo chown -R www-data:www-data "$PROD_DIR/storage" "$PROD_DIR/bootstrap/cache"
sudo find "$PROD_DIR" -type d -name node_modules -prune -o -type d -exec chmod 755 {} \;
sudo find "$PROD_DIR" -type d -name node_modules -prune -o -type f -exec chmod 644 {} \;

php artisan up

echo "🧽 Rotando backups (dejo 10 más recientes)…"
ls -tp "$BACKUP_DIR" | grep -E '\.zip$|\.sql$' | tail -n +11 | xargs -r -I {} rm -f "$BACKUP_DIR/{}"

echo "✅ Deploy OK → https://$DOMAIN"
