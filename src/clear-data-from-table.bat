@echo off
cd backend && @set PATH=php;%PATH% && php artisan clear-data && pause
