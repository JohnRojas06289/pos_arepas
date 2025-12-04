@echo off
title POS Arepas - Deteniendo servidor...

echo Deteniendo servidor PHP...
taskkill /F /IM php.exe /T >nul 2>&1

if %errorlevel% equ 0 (
    echo Servidor detenido correctamente.
) else (
    echo No se encontro ningun servidor corriendo.
)

echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
