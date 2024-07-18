@ECHO OFF

cd listener && @set PATH=nodejs;%PATH% && start node log-listener > ../listener.txt