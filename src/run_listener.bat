@ECHO OFF

cd listener && @set PATH=nodejs;%PATH% && start /MIN node log-listener > ../listener.txt