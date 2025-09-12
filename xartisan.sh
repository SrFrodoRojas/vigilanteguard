#!/usr/bin/env bash
# =============================================================================
#  Script de mantenimiento + backup + (opcional) servidor de desarrollo
#  Proyecto: VIGILANTE (Laravel) - Manjaro/Arch edition
#  - set -Eeuo pipefail, trap de errores
#  - backups rotativos (.zip + .sql/.sql.gz)
#  - verificación de APP_KEY segura
#  - storage:link, caches, permisos
#  - servidor dev por defecto en 0.0.0.0:8000 (LAN)
#  - usa mariadb-dump si existe, o mysqldump si no
#
# Requisitos sugeridos (una sola vez):
#   sudo pacman -S --needed php zip mariadb-clients grep findutils coreutils gzip
# =============================================================================

set -Eeuo pipefail
IFS=$'\n\t'
umask 022

# ---- Configuración base -------------------------------------------------------
RUTA="/srv/http/vigilante"
RUTA_BACKUP="/srv/http"

DB_NAME="vigilante"
DB_USER="root"
DB_PASS="@info2016!"    # Sugerido: usar ~/.my.cnf para no exponerla (ver más abajo)

BACKUP_DIR="$RUTA_BACKUP/backups/vigilante"
TIMESTAMP="$(date +"%Y-%m-%d_%H-%M-%S")"
PROYECTO="backup_vigilante_${TIMESTAMP}"
ZIP_NAME="${PROYECTO}.zip"
SQL_NAME="${PROYECTO}.sql"

# Grupo del servidor web en Manjaro/Arch
WEB_GROUP="${WEB_GROUP:-http}"

# ---- Config extra -------------------------------------------------------------
BACKUP_RETAIN=10         # cuántos backups mantener
GZIP_SQL=true            # comprimir el dump .sql en .gz (true/false)

# Servidor dev (por defecto LAN)
DEV_HOST="${DEV_HOST:-0.0.0.0}"
DEV_PORT="${DEV_PORT:-8000}"
RUN_DEV_SERVER="${RUN_DEV_SERVER:-1}"   # 1=levantar server dev, 0=no
RUN_DEV_BG="${RUN_DEV_BG:-0}"           # 1=background, 0=bloqueante

# Log de ejecución (opcional)
LOG_FILE="$BACKUP_DIR/run_${TIMESTAMP}.log"

# ---- Utilitarios --------------------------------------------------------------
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
trap 'log "⚠  Error en línea $LINENO. Abortando."; exit 1' ERR

# ---- Chequeos previos ---------------------------------------------------------
need_bin php
need_bin find
need_bin zip
need_bin grep
$GZIP_SQL && need_bin gzip || true

# mariadb-dump preferido; fallback a mysqldump
DUMP_BIN="$(command -v mariadb-dump || true)"
if [ -z "$DUMP_BIN" ]; then
  need_bin mysqldump
  DUMP_BIN="$(command -v mysqldump)"
fi

[ -d "$RUTA" ] || fail "La ruta de proyecto no existe: $RUTA"

# sudo si corresponde
if [ "$(id -u)" -ne 0 ]; then SUDO="sudo"; else SUDO=""; fi
OWNER="${SUDO_USER:-$USER}"

# Asegurar grupo válido (si http no existe, usar grupo del OWNER)
if ! getent group "$WEB_GROUP" >/dev/null 2>&1; then
  WEB_GROUP="$(id -gn "$OWNER")"
fi

# Crear carpeta de backups y permisos
$SUDO mkdir -p "$BACKUP_DIR"
$SUDO chown -R "$OWNER:$WEB_GROUP" "$BACKUP_DIR"

# ---- INICIO -------------------------------------------------------------------
log "🔐 Aplicando permisos a $RUTA (excluyendo node_modules, vendor, .git)..."

# Chown global consistente
$SUDO chown -R "$OWNER:$WEB_GROUP" "$RUTA"

# Permisos a carpetas y archivos (excepto excluidos)
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type d -exec chmod 755 {} \;
find "$RUTA" \( -path "$RUTA/node_modules" -o -path "$RUTA/vendor" -o -path "$RUTA/.git" \) -prune -o -type f -exec chmod 644 {} \;

# Permisos especiales para Laravel
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

# APP_KEY
log "⚙ Verificando APP_KEY..."
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
  log "✔ Symlink creado."
else
  log "⚠ Symlink ya existe o no fue necesario."
fi

# ---- Backup de base de datos --------------------------------------------------
log "🛢 Generando backup de base de datos..."

# Si existe ~/.my.cnf, no exponemos user/pass en la línea de comandos
MYSQL_AUTH_ARGS=()
if [ -f "$HOME/.my.cnf" ]; then
  MYSQL_AUTH_ARGS=()  # mariadb-dump/ mysqldump leerán user/pass del archivo
else
  MYSQL_AUTH_ARGS=(-u"$DB_USER" -p"$DB_PASS")
fi

if "$DUMP_BIN" --single-transaction --quick --routines --triggers --events \
  "${MYSQL_AUTH_ARGS[@]}" "$DB_NAME" > "$BACKUP_DIR/$SQL_NAME"; then
  log "✅ Dump SQL generado: $SQL_NAME"
  if [ "${GZIP_SQL}" = true ]; then
    if gzip -c "$BACKUP_DIR/$SQL_NAME" > "$BACKUP_DIR/$SQL_NAME.gz"; then
      log "🗜  Comprimido: $SQL_NAME.gz"
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
ls -tp | grep -E '\.zip$|\.sql$|\.sql\.gz$' | tail -n +$((BACKUP_RETAIN+1)) | xargs -r -I {} rm -- "{}"
log "✅ Limpieza de backups completada."

# ---- Info final ---------------------------------------------------------------
cd "$RUTA"
log "🎉 Proceso de permisos + backups completado."
log "📂 Guardados en: $BACKUP_DIR/$ZIP_NAME y $BACKUP_DIR/$SQL_NAME"
log "ℹ  Servidor dev (si aplica): http://${DEV_HOST}:${DEV_PORT}"

# ---- Servidor de desarrollo ---------------------------------------------------
if [ "$RUN_DEV_SERVER" = "1" ]; then
  log "⏳ Iniciando servidor dev en http://${DEV_HOST}:${DEV_PORT} ..."
  if [ "$RUN_DEV_BG" = "1" ]; then
    APP_ENV=local APP_DEBUG=true php artisan serve --host="${DEV_HOST}" --port="${DEV_PORT}" &
    SVR_PID=$!
    log "✅ Servidor en background (PID: $SVR_PID)."
  else
    APP_ENV=local APP_DEBUG=true php artisan serve --host="${DEV_HOST}" --port="${DEV_PORT}"
  fi
else
  log "⏭  RUN_DEV_SERVER=0 → no se inicia servidor dev."
fi
