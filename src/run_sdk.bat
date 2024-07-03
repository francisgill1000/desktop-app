@echo off
cd device_sdk && @set PATH=dotnet;%PATH% && start /MIN dotnet FCardProtocolAPI.dll > ../sdk_server.txt