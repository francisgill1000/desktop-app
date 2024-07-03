@echo off
cd backend && @set PATH=php;%PATH% && start /MIN php artisan serve:init  > ../server.txt
