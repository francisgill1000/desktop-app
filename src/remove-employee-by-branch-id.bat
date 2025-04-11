@echo off
setlocal EnableDelayedExpansion

:: Get the directory where the .bat file is located
set script_dir=%~dp0

:: Check if the backend directory exists
if not exist "%script_dir%backend" (
    echo "The 'backend' directory does not exist!"
    pause
    exit /b
)

:: Change to the backend directory
cd "%script_dir%backend"

:: Ensure PHP is in the PATH (optional, if PHP is globally available, you can skip this)
set PATH=php;%PATH%

:: Run php artisan command (you can replace with any specific artisan command)
php artisan remove-data-by-filter-id


pause
