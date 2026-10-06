#!/usr/bin/env bash
set -e

echo "=========================================================="
echo " Starting Deployment: Shibaura Plant Feedback Application"
echo " Domain: http://shibaura.amoebatronix.com"
echo "=========================================================="

APP_DIR="/home/john/shibaura/feedback_management"
BRANCH="develop_santhosh" # Change to "main" if deploying from main branch

cd "$APP_DIR"

# 1. Put application into maintenance mode
echo "==> Enabling Maintenance Mode..."
php artisan down --render="errors::503" --secret="shibaura-bypass-token" || true

# 2. Pull the latest commits from Git
echo "==> Pulling latest changes from Git ($BRANCH)..."
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull origin "$BRANCH"

# 3. Install/Update Composer PHP Dependencies
echo "==> Installing Composer dependencies (no-dev, optimized)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Run Database Migrations
echo "==> Running Database Migrations..."
php artisan migrate --force

# 5. Build Front-end Assets (Vite)
echo "==> Compiling Frontend Assets (Vite)..."
npm ci --prefer-offline || npm install
npm run build

# 6. Ensure Storage Symlink
echo "==> Verifying Storage Link..."
php artisan storage:link || true

# 7. Clear and Warm Production Caches
echo "==> Optimizing Laravel Caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Restart Queue Workers & PHP-FPM
echo "==> Restarting Queue Workers & Reloading PHP-FPM..."
php artisan queue:restart || true
sudo systemctl reload php8.2-fpm || sudo systemctl reload php8.3-fpm || true
sudo supervisorctl restart shibaura-feedback-worker:* || true

# 9. Set Permissions
echo "==> Enforcing Permissions for web server access..."
sudo chown -R john:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod +x /home/john /home/john/shibaura

# 10. Bring application back online
echo "==> Bringing Application Online..."
php artisan up

echo "=========================================================="
echo " Deployment Successfully Completed!"
echo " URL: http://shibaura.amoebatronix.com"
echo " Date: $(date)"
echo "=========================================================="
