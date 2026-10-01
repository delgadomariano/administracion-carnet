@echo off
setlocal enabledelayedexpansion

REM ============================================================
REM  Carnet IGCA - servidor local de prueba
REM  Levanta PHP en http://localhost:8090 y abre el lanzador,
REM  donde se ingresa el DNI y se genera el PDF del carnet.
REM ============================================================

cd /d "%~dp0"
set PORT=8090

REM --- Buscar PHP: winget, XAMPP, C:\php o el PATH ---
set "PHP_EXE="
for /d %%D in ("%LocalAppData%\Microsoft\WinGet\Packages\PHP.PHP*") do (
    if not defined PHP_EXE if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
)
if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
if not defined PHP_EXE (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
)
if not defined PHP_EXE (
    echo.
    echo [ERROR] No se encontro PHP. Instalalo con:  winget install --id PHP.PHP.8.4 -e
    echo.
    pause
    exit /b 1
)

REM --- Extensiones necesarias (openssl para la API de miembros por https) ---
for %%F in ("%PHP_EXE%") do set "PHP_DIR=%%~dpF"
set "PHP_ARGS="
if exist "%PHP_DIR%ext\php_openssl.dll" (
    set PHP_ARGS=-d "extension_dir=%PHP_DIR%ext" -d extension=openssl -d extension=mbstring
)

echo ============================================================
echo  Carnet IGCA - prueba local
echo  Lanzador:  http://localhost:%PORT%/_lanzador-local.php
echo  PHP:       %PHP_EXE%
echo.
echo  Cerra esta ventana (o Ctrl+C) para detener el servidor.
echo ============================================================
echo.

start "" "http://localhost:%PORT%/_lanzador-local.php"
"%PHP_EXE%" %PHP_ARGS% -S localhost:%PORT%

endlocal
