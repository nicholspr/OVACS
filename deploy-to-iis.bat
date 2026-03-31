@echo off
echo.
echo 🚀 Deploying OVACS to IIS...
echo.

REM Create directory if it doesn't exist
if not exist "C:\inetpub\wwwroot\OVACS\" mkdir "C:\inetpub\wwwroot\OVACS"

REM Copy files to IIS
robocopy "C:\DATA\GIT\phpOVACS" "C:\inetpub\wwwroot\OVACS" *.php *.css *.html *.js *.md /s /e /xd .git .vscode node_modules

REM Set permissions
icacls "C:\inetpub\wwwroot\OVACS" /grant "IIS_IUSRS:(OI)(CI)F" /T > nul 2>&1

echo.
echo ✅ Deployment complete!
echo 🌐 Visit: http://localhost/OVACS/
echo.
pause