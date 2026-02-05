#!/bin/bash
# Скрипт для створення симлінків на Linux/Mac

cd "$(dirname "$0")"

echo "Створення симлінків для assets..."

# Видалити старі симлінки якщо існують
rm -f public/assets
rm -f public/database

# Створити симлінки
ln -sf ../assets public/assets
ln -sf ../database public/database

echo "Симлінки створено успішно!"
