@echo off
chcp 65001 >nul
title Backup Base de Datos - POS Arepas

echo ================================================
echo   BACKUP DE BASE DE DATOS - POS AREPAS
echo ================================================
echo.

REM Obtener fecha y hora para el nombre del backup
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set BACKUP_DATE=%datetime:~0,8%
set BACKUP_TIME=%datetime:~8,6%
set BACKUP_TIMESTAMP=%BACKUP_DATE%_%BACKUP_TIME%

REM Crear carpeta de backups si no existe
if not exist "database\backups" mkdir "database\backups"

REM Ruta de la base de datos
set DB_PATH=database\database.sqlite
set BACKUP_PATH=database\backups\backup_%BACKUP_TIMESTAMP%.sqlite

REM Verificar que existe la base de datos
if not exist "%DB_PATH%" (
    echo [ERROR] No se encontró la base de datos en: %DB_PATH%
    echo.
    pause
    exit /b 1
)

REM Crear backup
echo [INFO] Creando backup de la base de datos...
echo [INFO] Origen: %DB_PATH%
echo [INFO] Destino: %BACKUP_PATH%
echo.

copy "%DB_PATH%" "%BACKUP_PATH%" >nul

if %ERRORLEVEL% EQU 0 (
    echo [OK] ✓ Backup creado exitosamente!
    echo.
    
    REM Mostrar tamaño del backup
    for %%A in ("%BACKUP_PATH%") do (
        set size=%%~zA
        set /a size_kb=!size! / 1024
        echo [INFO] Tamaño del backup: !size_kb! KB
    )
    
    echo.
    echo [INFO] Ubicación: %BACKUP_PATH%
    
    REM Limpiar backups antiguos (mantener solo los últimos 10)
    echo.
    echo [INFO] Limpiando backups antiguos (manteniendo últimos 10)...
    
    setlocal enabledelayedexpansion
    set count=0
    for /f "delims=" %%f in ('dir /b /o-d "database\backups\backup_*.sqlite" 2^>nul') do (
        set /a count+=1
        if !count! GTR 10 (
            del "database\backups\%%f" >nul 2>&1
            echo [INFO] Eliminado: %%f
        )
    )
    endlocal
    
    echo.
    echo [OK] ✓ Proceso completado!
) else (
    echo [ERROR] ✗ Error al crear el backup
    echo.
    pause
    exit /b 1
)

echo.
echo ================================================
pause
