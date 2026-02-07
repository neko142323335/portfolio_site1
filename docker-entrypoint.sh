#!/bin/bash
set -e
mkdir -p /var/www/html/database
touch /var/www/html/database/portfolio.db
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/database

# Завжди оновлювати autoload при старті контейнера
echo "Updating Composer autoload..."
composer dump-autoload --optimize

# Створити symlink для assets якщо не існує
if [ ! -L /var/www/html/public/assets ]; then
    echo "Creating assets symlink..."
    ln -sf /var/www/html/assets /var/www/html/public/assets
fi

# Запустити Apache
echo "Starting Apache..."
exec apache2-foreground