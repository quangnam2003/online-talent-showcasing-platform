#!/bin/sh
set -e

# Cảnh báo nếu thiếu APP_KEY (phải truyền qua biến môi trường / .env)
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY chưa được set. Chạy 'php artisan key:generate --show' và đặt vào biến môi trường APP_KEY."
fi

php artisan storage:link 2>/dev/null || true

# Cache config/route/view cho production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Tự động migrate khi AUTO_MIGRATE=true (dùng trong docker-compose)
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

exec "$@"
