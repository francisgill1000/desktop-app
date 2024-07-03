@ECHO OFF

cd listener && @set PATH=nodejs;%PATH% && node ip-updater > ../ip_updater.txt