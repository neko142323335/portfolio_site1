#!/bin/bash
set -e

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
