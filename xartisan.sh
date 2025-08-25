#!/usr/bin/env bash
# =============================================================================
#  Script de mantenimiento + backup + (opcional) servidor de desarrollo
#  Proyecto: VIGILANTE (Laravel)
#  Mejores prácticas:
#   - set -Eeuo pipefail, trap de errores, validaciones de binarios y rutas
#   - rotación configurable de backups, compresión .sql.gz opcional
#   - verificación segura de APP_KEY antes de key:generate
#   - servidor dev por defecto SOLO localhost (EXPOSE_DEV=1 para exponer)
#   - detección correcta de OWNER cuando se usa sudo
#   - exclusiones robustas en zip (zip relativo para evitar rutas absolutas)
# =============================================================================

set -Eeuo pipefail
IFS=$'\n\t'
umask 022

# ---- Configuración base (tus valores) ----------------------------------------
RUTA="/var/www/html/vigilante"
RUTA_BACKUP="/var/www/html"
DB_NAME="vigilante"
DB_USER="root"
DB_PASS="@info2016!"
BACKUP_DIR="$RUTA_BACKUP/backups/vigilante"
TIMESTAMP="$(date +"%Y-%m-%d_%H-%M-%S")"
PROYECTO="backup_vigilante_${TIMESTAMP}"
ZIP_NAME="${PROYECTO}.zip"
SQL_NAME="${PROYECTO}.sql"

# ---- Configuraciones extra ----------------------------------------------------
# Cuántos backups mantener (se cuentan .zip, .sql y .sql.gz)
BACKUP_RETAIN=10

# Comprimir además el dump SQL en .gz (true/false)
GZIP_SQL=true

# Servidor dev:
#   - Por defecto solo localhost (127.0.0.1)
#   - EXPOSE_DEV=1 -> 0.0.0.0 (LAN)
#   - RUN_DEV_SERVER=0 para NO levantar el server
#   - RUN_DEV_BG=1 para correrlo en background
DEV_HOST="${EXPOSE_DEV:+0.0.0.0}"
DEV_HOST="${DEV_HOST:-127.0.0.1}"
DEV_PORT="8000"
RUN_DEV_SERVER="${RUN_DEV_SERVER:-1}"
RUN_DEV_BG="${RUN_DEV_BG:-0}"

# Log de ejecución (opcional). Comenta si no lo querés.
LOG_FILE="$BACKUP_DIR/run_${TIMESTAMP}.log"

# ---- Funciones utilitarias ----------------------------------------------------
fail() { echo "❌ $*" >&2; exit 1; }

need_bin() { command -v "$1" >/dev/null 2>&1 || fail "No se encontró el binario requerido: $1"; }

log() {
  if [ -n "${LOG_FILE:-}" ]; then
    mkdir -p "$BACKUP_DIR" 2>/dev/null || true
    echo -e "$*" | tee -a "$LOG_FILE"
  else
    echo -e "$*"
  fi
}

trap 'log "⚠️  Error en línea $LINENO. Abortando."; exit 1' ERR

# ---- Chequeos previos ---------------------------------------------------------
need_bin php
need_bin find
need_bin zip
need_bin mysqldump
need_bin grep
$GZIP_SQL && need_bin gzip || true

[ -d "$RUTA" ] || fail "La ruta de proyecto no existe: $RUTA"

# sudo si corresponde
if [ "$(id -u)" -ne 0 ]; then SUDO="sudo"; else SUDO=""; fi
OWNER="${SUDO_USER:-$USER}"

# Crear carpeta de backups y fijar permisos
$SUDO mkdir -p "$BACKUP_DIR"
$SUDO chown -R "$OWNER:www-data" "$BACKUP_DIR"

# ---- INICIO -------------------------------------------------------------------
log "🔐 Aplicando permisos a $RUTA (excluyendo node_modules, vendor, .git)..."

# Un (1) chown global consistente
$SUDO chown -R "$OWNER:www-data" "$RUTA"

# Permisos a carpetas y archivos (excepto excluidos)
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type d -exec chmod 755 {} \;
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type f -exec chmod 644 {} \;

# Permisos especiales (solo chmod; chown ya lo cubre el global)
$SUDO chmod -R 775 "$RUTA/storage" "$RUTA/bootstrap/cache"
$SUDO chmod -R 775 "$RUTA/public/images" 2>/dev/null || true

log "✅ Permisos aplicados."

# Verificar artisan
cd "$RUTA"
[ -f artisan ] || fail "No se encontró 'artisan' en $RUTA."

# Limpiar cachés
log "🧹 Limpiando cachés Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
log "✅ Cachés limpiadas."

# Generar APP_KEY solo si falta
log "⚙️ Verificando APP_KEY..."
if ! grep -qE '^APP_KEY=.+$' .env 2>/dev/null; then
  log "🔑 APP_KEY vacío: generando nueva clave..."
  php artisan key:generate
else
  log "🔑 APP_KEY presente."
fi

# Recompilar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Symlink de storage
log "🔗 Verificando symlink de storage..."
if php artisan storage:link 2>/dev/null; then
  log "✔️ Symlink creado."
else
  log "⚠️ Symlink ya existe o no fue necesario."
fi

# ---- Backup de base de datos --------------------------------------------------
log "🛢️ Generando backup de base de datos..."
if mysqldump --single-transaction --quick --routines --triggers --events -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/$SQL_NAME"; then
  log "✅ Dump SQL generado: $SQL_NAME"
  if [ "${GZIP_SQL}" = true ]; then
    if gzip -c "$BACKUP_DIR/$SQL_NAME" > "$BACKUP_DIR/$SQL_NAME.gz"; then
      log "🗜️  Comprimido: $SQL_NAME.gz"
    else
      log "❌ Falló compresión .gz (se mantiene .sql)."
    fi
  fi
else
  log "❌ Error al generar el dump SQL."
fi

# ---- Backup de archivos del proyecto -----------------------------------------
log "📦 Generando backup comprimido del proyecto..."
PROJECT_DIR_NAME="$(basename "$RUTA")"
(
  cd "$RUTA_BACKUP"
  zip -q -9 -r "$BACKUP_DIR/$ZIP_NAME" "$PROJECT_DIR_NAME" \
    -x "$PROJECT_DIR_NAME/node_modules/*" \
    -x "$PROJECT_DIR_NAME/vendor/*" \
    -x "$PROJECT_DIR_NAME/.git/*" \
    -x "$PROJECT_DIR_NAME/storage/logs/*" \
    -x "$PROJECT_DIR_NAME/public/storage/*"
) || true

if [ -f "$BACKUP_DIR/$ZIP_NAME" ]; then
  log "✅ Backup comprimido generado: $ZIP_NAME"
else
  log "❌ Error al generar el archivo ZIP"
fi

# ---- Rotación de backups ------------------------------------------------------
log "🧹 Rotando backups: dejando solo los ${BACKUP_RETAIN} más recientes..."
cd "$BACKUP_DIR"
# Mantener por fecha de modificación (archivos que nos interesan)
ls -tp | grep -E '\.zip$|\.sql$|\.sql\.gz$' | tail -n +$((BACKUP_RETAIN+1)) | xargs -r -I {} rm -- "{}"
log "✅ Limpieza de backups completada."

# ---- Info final ---------------------------------------------------------------
cd "$RUTA"
log "🎉 Proceso de permisos + backups completado."
log "📂 Guardados en: $BACKUP_DIR/$ZIP_NAME y $BACKUP_DIR/$SQL_NAME"
log "ℹ️  Host público (si aplica): https://vigilante.rosaamara.online"

# ---- Servidor de desarrollo ---------------------------------------------------
if [ "$RUN_DEV_SERVER" = "1" ]; then
  log "⏳ Iniciando servidor local en http://${DEV_HOST}:${DEV_PORT} ..."
  if [ "$RUN_DEV_BG" = "1" ]; then
    APP_ENV=local APP_DEBUG=true php artisan serve --host="${DEV_HOST}" --port="${DEV_PORT}" &
    SVR_PID=$!
    log "✅ Servidor en background (PID: $SVR_PID)."
  else
    # Bloqueante (hasta Ctrl+C)
    APP_ENV=local APP_DEBUG=true php artisan serve --host="${DEV_HOST}" --port="${DEV_PORT}"
  fi
else
  log "⏭️  RUN_DEV_SERVER=0 → no se inicia servidor dev."
fi
