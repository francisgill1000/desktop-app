@echo off
cd backend && @set PATH=php;%PATH% && start php artisan queue:work  > ../queue_jobs.txt