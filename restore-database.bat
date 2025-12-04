@echo off
chcp 65001 >nul
title Restaurar Base de Datos - POS Arepas

echo ================================================
echo   RESTAURAR BASE DE DATOS - POS AREPAS
echo ================================================
echo.

REM Verificar que existe la carpeta de backups
if not exist "database\backups" (
    echo [ERROR] No se encontró la carpeta de backups
    echo.
    pause
    exit /b 1
)

REM Listar backups disponibles
echo [INFO] Backups disponibles:
echo.
set count=0
for /f "delims=" %%f in ('dir /b /o-d "database\backups\backup_*.sqlite" 2^>nul') do (
    set /a count+=1
    echo [!count!] %%f
    set "backup_!count!=%%f"
)

if %count% EQU 0 (
    echo [ERROR] No hay backups disponibles
    echo.
    pause
    exit /b 1
)

echo.
echo ================================================
set /p choice="Ingrese el número del backup a restaurar (1-%count%): "

REM Validar entrada
if not defined backup_%choice% (
    echo [ERROR] Opción inválida
    pause
    exit /b 1
)

REM Obtener nombre del backup seleccionado
call set selected_backup=%%backup_%choice%%%

echo.
echo [ADVERTENCIA] Esta acción sobrescribirá la base de datos actual
echo [ADVERTENCIA] Backup seleccionado: %selected_backup%
echo.
set /p confirm="¿Está seguro? (S/N): "

if /i not "%confirm%"=="S" (
    echo [INFO] Operación cancelada
    pause
    exit /b 0
)

REM Crear backup de seguridad antes de restaurar
echo.
echo [INFO] Creando backup de seguridad de la BD actual...
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set BACKUP_TIMESTAMP=%datetime:~0,8%_%datetime:~8,6%
copy "database\database.sqlite" "database\backups\pre_restore_%BACKUP_TIMESTAMP%.sqlite" >nul

REM Restaurar backup
echo [INFO] Restaurando backup...
copy "database\backups\%selected_backup%" "database\database.sqlite" /Y >nul

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] ✓ Base de datos restaurada exitosamente!
    echo [INFO] Se creó un backup de la BD anterior: pre_restore_%BACKUP_TIMESTAMP%.sqlite
) else (
    echo.
    echo [ERROR] ✗ Error al restaurar el backup
)

echo.
echo ================================================
pause
