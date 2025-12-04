@echo off
REM Script para crear acceso directo en el escritorio

set SCRIPT_DIR=%~dp0
set TARGET=%SCRIPT_DIR%start-pos.bat
set SHORTCUT_NAME=POS Arepas.lnk

REM Detectar la ubicación correcta del escritorio
REM Primero intentar OneDrive
set DESKTOP=%USERPROFILE%\OneDrive\Escritorio
if not exist "%DESKTOP%" (
    REM Si no existe, intentar Desktop en inglés
    set DESKTOP=%USERPROFILE%\Desktop
)
if not exist "%DESKTOP%" (
    REM Si tampoco existe, intentar Escritorio local
    set DESKTOP=%USERPROFILE%\Escritorio
)

echo ========================================
echo   Creando Acceso Directo
echo ========================================
echo.
echo Ubicacion: %DESKTOP%\%SHORTCUT_NAME%
echo Destino: %TARGET%
echo.

REM Verificar que la carpeta del escritorio existe
if not exist "%DESKTOP%" (
    echo [ERROR] No se pudo encontrar la carpeta del escritorio
    echo Intentado en:
    echo   - %USERPROFILE%\OneDrive\Escritorio
    echo   - %USERPROFILE%\Desktop
    echo   - %USERPROFILE%\Escritorio
    echo.
    echo Por favor, crea el acceso directo manualmente.
    pause
    exit /b 1
)

REM Crear acceso directo usando PowerShell
powershell -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%DESKTOP%\%SHORTCUT_NAME%'); $Shortcut.TargetPath = '%TARGET%'; $Shortcut.WorkingDirectory = '%SCRIPT_DIR%'; $Shortcut.Description = 'POS Arepas Boyacenses - Sistema de Punto de Venta'; $Shortcut.Save()"

if %errorlevel% equ 0 (
    echo [OK] Acceso directo creado exitosamente en:
    echo %DESKTOP%
    echo.
    echo Ahora puedes iniciar el sistema desde el escritorio
    echo haciendo doble clic en "POS Arepas"
) else (
    echo [ERROR] No se pudo crear el acceso directo
    echo.
    echo Intenta ejecutar este script como Administrador
)

echo.
pause
