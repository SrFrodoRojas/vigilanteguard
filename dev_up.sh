#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

export APP_ENV=local
export APP_DEBUG=true

[ -f .env.local ] && cp .env.local .env

RUTA="/var/www/html/vigilante"
RUTA_BACKUP="/var/www/html"
DB_NAME="vigilante"
DB_USER="root"
DB_PASS="@info2016!"
BACKUP_DIR="$RUTA_BACKUP/backups/vigilante"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
PROYECTO="backup_tienda_$TIMESTAMP"
ZIP_NAME="$PROYECTO.zip"
SQL_NAME="$PROYECTO.sql"

echo "🔐 Aplicando permisos a $RUTA (excluyendo node_modules, vendor, .git)..."

# Cambiar propietario general
sudo chown -R www-data:www-data "$RUTA"

# Permisos a carpetas y archivos (excepto excluidos)
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type d -exec chmod 755 {} \;
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type f -exec chmod 644 {} \;

# Permisos especiales
sudo chmod -R 775 "$RUTA/storage" "$RUTA/bootstrap/cache"
sudo chown -R www-data:www-data "$RUTA/storage" "$RUTA/bootstrap/cache"

# Permisos para imágenes públicas
sudo chmod -R 775 "$RUTA/public/images"
sudo chown -R $USER:www-data "$RUTA/public/images"

echo "✅ Permisos aplicados."

# Verificar artisan
cd "$RUTA"
if [ ! -f artisan ]; then
    echo "❌ Error: No se encontró 'artisan' en $RUTA."
    exit 1
fi

# Limpiar cachés
echo "🧹 Limpiando cachés Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "✅ Cachés limpiadas."

# Generar claves y caches
echo "⚙️ Generando claves y cachés..."

php artisan route:cache
php artisan view:cache

# Symlink
echo "🔗 Verificando symlink de storage..."
php artisan storage:link 2>/dev/null && echo "✔️ Symlink creado" || echo "⚠️ Symlink ya existe o falló"

# Crear carpeta de backups
sudo mkdir -p "$BACKUP_DIR"
sudo chown -R $USER:www-data "$BACKUP_DIR"

# Backup base de datos
echo "🛢️ Generando backup de base de datos..."
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" >"$BACKUP_DIR/$SQL_NAME"

if [ $? -eq 0 ]; then
    echo "✅ Backup de base de datos generado: $SQL_NAME"
else
    echo "❌ Error al generar el dump SQL"
fi

# Backup de archivos
echo "📦 Generando backup comprimido del proyecto..."
zip -r "$BACKUP_DIR/$ZIP_NAME" "$RUTA" \
    -x "$RUTA/node_modules/*" \
    -x "$RUTA/vendor/*" \
    -x "$RUTA/.git/*" \
    -x "$RUTA/storage/logs/*" \
    -x "$RUTA/public/storage/*" \
    -x "$BACKUP_DIR/*" \
    -x "*.zip" \
    -x "*.sql"

if [ -f "$BACKUP_DIR/$ZIP_NAME" ]; then
    echo "✅ Backup comprimido generado: $ZIP_NAME"
else
    echo "❌ Error al generar el archivo ZIP"
fi

# Mantener solo 5 backups más recientes
echo "🧹 Eliminando backups antiguos, dejando solo los 5 más recientes..."
cd "$BACKUP_DIR"
ls -tp | grep -E '\.zip$|\.sql$' | tail -n +11 | xargs -I {} rm -- {}

echo "✅ Limpieza de backups completada."

# Volver a la raíz del proyecto
cd "$RUTA"
sudo chown sf:sf ** -Rf
# Servidor de desarrollo
echo "🚀 Iniciando servidor local en http://127.0.0.1:8000 ..."
php artisan serve
