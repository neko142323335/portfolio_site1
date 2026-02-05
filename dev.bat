@echo off
REM Скрипт для швидкого управління проектом (Windows)

if "%1"=="start" goto start
if "%1"=="stop" goto stop
if "%1"=="restart" goto restart
if "%1"=="rebuild" goto rebuild
if "%1"=="logs" goto logs
if "%1"=="shell" goto shell
if "%1"=="autoload" goto autoload
if "%1"=="db" goto db
goto usage

:start
echo 🚀 Запуск проекту...
docker-compose up -d
echo ✅ Проект запущено: http://localhost:8080
goto end

:stop
echo ⏸️  Зупинка проекту...
docker-compose down
echo ✅ Проект зупинено
goto end

:restart
echo 🔄 Перезапуск проекту...
docker-compose restart
echo ✅ Проект перезапущено
goto end

:rebuild
echo 🔨 Перебудова проекту...
docker-compose down
docker-compose build
docker-compose up -d
echo ✅ Проект перебудовано та запущено
goto end

:logs
docker-compose logs -f
goto end

:shell
docker exec -it portfolio-php bash
goto end

:autoload
echo 🔄 Оновлення autoload...
docker exec portfolio-php composer dump-autoload
echo ✅ Autoload оновлено
goto end

:db
docker exec -it portfolio-php sqlite3 database/portfolio.db
goto end

:usage
echo Використання: %0 {start^|stop^|restart^|rebuild^|logs^|shell^|autoload^|db}
echo.
echo Команди:
echo   start    - Запустити проект
echo   stop     - Зупинити проект
echo   restart  - Перезапустити проект
echo   rebuild  - Перебудувати та запустити
echo   logs     - Показати логи
echo   shell    - Відкрити bash в контейнері
echo   autoload - Оновити composer autoload (вручну, якщо потрібно)
echo   db       - Відкрити SQLite консоль
goto end

:end
