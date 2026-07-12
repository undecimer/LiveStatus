@echo off
echo Packaging LiveStatus Joomla extension...

:: Remove existing package if it exists
if exist install.zip (
    del /f /q install.zip
)

:: Package using PowerShell Compress-Archive (native to Windows, zero dependencies)
powershell -NoProfile -Command "Compress-Archive -Path livestatus.xml, livestatus.php, bootstrap.php, script.php, LICENSE, README.md, language, services, src -DestinationPath install.zip -Force"

if %ERRORLEVEL% EQU 0 (
    echo LiveStatus Joomla extension successfully packaged into install.zip!
) else (
    echo Error: Packaging failed.
)
pause
