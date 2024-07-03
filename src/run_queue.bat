@echo off
cd backend && @set PATH=php;%PATH% && start /MIN php artisan queue:work  > ../queue_jobs.txt