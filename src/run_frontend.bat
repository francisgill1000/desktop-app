@set PATH=backend/php;%PATH%

setlocal EnableDelayedExpansion

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| find "IPv4 Address"') do (
    set ip=%%a
)

php -S !ip:~1!:3001 -t frontend > frontend.txt