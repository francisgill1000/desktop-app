@echo off
cd backend && @set PATH=php;%PATH% && start php artisan serve:init  > ../server.txt
