@echo off
echo.
echo 🔧 OVACS Database Setup Script (Automated)
echo ========================
echo.

REM Use known MySQL path and credentials
set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
set mysql_user=root
set mysql_password=Jiffyor@nge999

echo ✅ Using MySQL at: %MYSQL_PATH%
echo ✅ Using username: %mysql_user%
echo.

echo 🔄 Setting up OVACS database...
echo.

REM Create database and tables
%MYSQL_PATH% -u%mysql_user% -p%mysql_password% < "database\schema.sql"

if %errorlevel% equ 0 (
    echo ✅ Database schema created successfully!
    echo.
    echo 📊 Database Info:
    echo - Database Name: ovacs_db
    echo - Sample Data: Loaded
    echo - Tables Created: Multiple tables
    echo.
    echo 🔐 Default Admin Credentials:
    echo - Username: admin
    echo - Password: admin123
    echo.
    echo ⚠️  IMPORTANT: Database credentials are configured in includes\database.php
    echo.
    echo 🎉 Setup complete! You can now use the OVACS system.
) else (
    echo ❌ Database setup failed!
    echo Please check your MySQL credentials and try again.
    echo Error level: %errorlevel%
    echo.
)

pause