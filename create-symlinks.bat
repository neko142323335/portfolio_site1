@echo off
REM Скрипт для створення симлінків на Windows
REM Запускати з правами адміністратора

echo Створення симлінків для assets...

cd /d "%~dp0"

REM Видалити старі симлінки якщо існують
if exist "public\assets" rmdir "public\assets"
if exist "public\database" rmdir "public\database"

REM Створити симлінки
mklink /D "public\assets" "..\assets"
mklink /D "public\database" "..\database"

echo.
echo Симлінки створено успішно!
echo.
pause
