@echo off
cd backend && @set PATH=php;%PATH% && start /MIN php artisan schedule:work  > ../cron_jobs.txt