#!/bin/bash

# Configuración
REPO_DIR="/home/netz8452/repositories/FitTrack"
TARGET_DIR="/home/netz8452/fittrack.com.ar"
BRANCH="main"

echo "🚀 Deploy iniciado..."

# 1. Ir al repo
cd "$REPO_DIR" || { echo "❌ No se pudo acceder al repositorio"; exit 1; }

echo "📥 Haciendo pull desde la rama $BRANCH..."
git pull origin "$BRANCH" || { echo "❌ Falló el git pull"; exit 1; }

# 2. Crear backup antes de sobrescribir (usando comando Laravel)
echo "🔄 Creando backup..."
cd "$TARGET_DIR" || { echo "❌ No se pudo acceder al directorio de Laravel"; exit 1; }
sudo -u www-data php artisan app:backup || { echo "⚠️ Advertencia: el backup no se completó, pero continuamos el deploy"; }

# Volver al repositorio
cd "$REPO_DIR" || { echo "❌ No se pudo volver al repositorio"; exit 1; }

# 3. Sincronizar archivos, excluyendo carpetas dinámicas
echo "📂 Sincronizando hacia $TARGET_DIR preservando media y descargas..."
rsync -az --delete \
  --exclude ".git" \
  --exclude "vendor" \
  --exclude "storage" \
  --exclude ".env" \
  "$REPO_DIR/" "$TARGET_DIR/"
RSYNC_EXIT=$?

if [ $RSYNC_EXIT -ne 0 ]; then
    echo "⚠️ Rsync finalizó con código $RSYNC_EXIT - revisar posibles advertencias"
else
    echo "✅ Rsync finalizado correctamente"
fi

# 4. Crear carpetas dinámicas si no existen
mkdir -p "$TARGET_DIR/storage/app/public"
mkdir -p "$TARGET_DIR/storage/app/descargas"

# 5. Generar .env si no existe
if [ ! -f "$TARGET_DIR/.env" ]; then
    if [ -f "$TARGET_DIR/.env.deploy" ]; then
        echo "📄 Creando .env desde .env.deploy"
        cp "$TARGET_DIR/.env.deploy" "$TARGET_DIR/.env"
    elif [ -f "$TARGET_DIR/.env.example" ]; then
        echo "📄 Creando .env desde .env.example"
        cp "$TARGET_DIR/.env.example" "$TARGET_DIR/.env"
    else
        echo "⚠️ No se encontró .env.deploy ni .env.example. Abortando."
        exit 1
    fi
    echo "⚠️ Recordá editar la configuración del .env en $TARGET_DIR/.env"
fi

echo "🔧 Ajustando permisos..."
#sudo chown -R www-data:www-data "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"
#sudo chmod -R 775 "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"
chown -R www-data:www-data "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"
chmod -R 775 "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"

# Refuerza permisos correctos en los logs para evitar archivos creados por root
if [ -d "$TARGET_DIR/storage/logs" ]; then
    chown -R www-data:www-data "$TARGET_DIR/storage/logs"
    find "$TARGET_DIR/storage/logs" -type d -exec chmod 775 {} +
    find "$TARGET_DIR/storage/logs" -type f -exec chmod 664 {} +
fi

# 7. Comandos Laravel
cd "$TARGET_DIR" || { echo "❌ No se pudo acceder al directorio de Laravel"; exit 1; }

mkdir -p "$TARGET_DIR/vendor"
chown -R www-data:www-data "$TARGET_DIR/vendor"
chown -R www-data:www-data "$TARGET_DIR"

mkdir -p "$TARGET_DIR/storage/framework/cache"
mkdir -p "$TARGET_DIR/storage/framework/sessions"
mkdir -p "$TARGET_DIR/storage/framework/views"
chown -R www-data:www-data "$TARGET_DIR/storage"

echo "📦 Ejecutando Composer (sin dev)..."
# Limpiar cache de Composer para evitar problemas
runuser -u www-data -- env HOME=/tmp composer clear-cache

# Instalar dependencias con opciones seguras para producción
echo "  → Instalando dependencias..."
runuser -u www-data -- env HOME=/tmp COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction \
    --no-progress || { echo "❌ Composer falló"; exit 1; }

echo "  ✓ Dependencias instaladas correctamente"

echo "🔧 Artisan commands..."
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan tenants:migrate --force
sudo -u www-data php artisan tenants:seed --class=TenantUpdateSeeder --force
sudo -u www-data php artisan queue:restart
echo "✅ Deploy completado con éxito."
