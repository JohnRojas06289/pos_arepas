@echo off
title POS Arepas Boyacenses - Iniciando...
cd /d "%~dp0"

echo ========================================
echo   POS AREPAS BOYACENSES
echo   Sistema de Punto de Venta
echo ========================================
echo.

REM Verificar si PHP esta instalado
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP no esta instalado o no esta en el PATH
    echo Por favor instala PHP 8.2 o superior
    echo.
    pause
    exit /b 1
)

echo [OK] PHP detectado
echo.

REM Verificar si existe la base de datos SQLite
if not exist "database\database.sqlite" (
    echo [INFO] Creando base de datos SQLite...
    type nul > database\database.sqlite
    echo [OK] Base de datos creada
    echo.
    
    echo [INFO] Ejecutando migraciones...
    php artisan migrate --force
    
    echo [INFO] Ejecutando seeders...
    php artisan db:seed --force
    echo.
)

echo [INFO] Limpiando caches...
php artisan optimize:clear >nul 2>&1
php artisan permission:cache-reset >nul 2>&1
php artisan config:clear >nul 2>&1

echo [OK] Sistema listo
echo.
echo ========================================
echo   MODO: SQLite Local (Offline)
echo   URL: http://127.0.0.1:8000
echo ========================================
echo.
echo [INFO] Abriendo navegador...
echo [INFO] Para cerrar el servidor, cierra esta ventana
echo.

timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000

echo [INFO] Iniciando servidor Laravel...
echo.
php artisan serve
