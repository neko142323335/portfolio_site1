#!/bin/bash
# Скрипт для швидкого управління проектом

case "$1" in
  start)
    echo "🚀 Запуск проекту..."
    docker-compose up -d
    echo "✅ Проект запущено: http://localhost:8080"
    ;;
    
  stop)
    echo "⏸️  Зупинка проекту..."
    docker-compose down
    echo "✅ Проект зупинено"
    ;;
    
  restart)
    echo "🔄 Перезапуск проекту..."
    docker-compose restart
    echo "✅ Проект перезапущено"
    ;;
    
  rebuild)
    echo "🔨 Перебудова проекту..."
    docker-compose down
    docker-compose build
    docker-compose up -d
    echo "✅ Проект перебудовано та запущено"
    ;;
    
  logs)
    docker-compose logs -f
    ;;
    
  shell)
    docker exec -it portfolio-php bash
    ;;
    
  autoload)
    echo "🔄 Оновлення autoload..."
    docker exec portfolio-php composer dump-autoload
    echo "✅ Autoload оновлено"
    ;;
    
  db)
    docker exec -it portfolio-php sqlite3 database/portfolio.db
    ;;
    
  *)
    echo "Використання: $0 {start|stop|restart|rebuild|logs|shell|autoload|db}"
    echo ""
    echo "Команди:"
    echo "  start    - Запустити проект"
    echo "  stop     - Зупинити проект"
    echo "  restart  - Перезапустити проект"
    echo "  rebuild  - Перебудувати та запустити"
    echo "  logs     - Показати логи"
    echo "  shell    - Відкрити bash в контейнері"
    echo "  autoload - Оновити composer autoload"
    echo "  db       - Відкрити SQLite консоль"
    exit 1
    ;;
esac
