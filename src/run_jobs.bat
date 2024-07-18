@echo off
cd backend && @set PATH=php;%PATH% && start php artisan schedule:work  > ../cron_jobs.txt