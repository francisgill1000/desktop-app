@echo off
cd device_sdk && @set PATH=dotnet;%PATH% && start dotnet FCardProtocolAPI.dll > ../sdk_server.txt