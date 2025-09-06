#!/bin/bash

# Configuración
REPO_DIR="/var/repository/luniqo.com"
TARGET_DIR="/var/www/luniqo.com"
BRANCH="main"
BACKUP_DIR="/var/backups/luniqo.com"
NOW=$(date +"%Y-%m-%d_%H%M")

echo "🚀 Deploy iniciado..."

# 1. Ir al repo
cd "$REPO_DIR" || { echo "❌ No se pudo acceder al repositorio"; exit 1; }

echo "📥 Haciendo pull desde la rama $BRANCH..."
git pull origin "$BRANCH" || { echo "❌ Falló el git pull"; exit 1; }

# 2. Crear backup antes de sobrescribir
echo "🔄 Creando backup en $BACKUP_DIR/$NOW"
sudo mkdir -p "$BACKUP_DIR/$NOW"
sudo rsync -az "$TARGET_DIR/" "$BACKUP_DIR/$NOW/" || { echo "❌ Falló el backup"; exit 1; }

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
    echo "⚠️ Rsync finalizó con código $RSYNC_EXIT — revisar posibles advertencias"
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
sudo chown -R www-data:www-data "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"
sudo chmod -R 775 "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"

# 7. Comandos Laravel
cd "$TARGET_DIR" || { echo "❌ No se pudo acceder al directorio de Laravel"; exit 1; }

echo "📦 Ejecutando Composer (sin dev)..."
sudo -u www-data HOME=/tmp composer install --no-dev --optimize-autoloader --no-interaction  || { echo "❌ Composer falló"; exit 1; }

echo "🔧 Artisan commands..."
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
php artisan tenants:migrate --force
php artisan tenants:seed --force
echo "✅ Deploy completado con éxito."
