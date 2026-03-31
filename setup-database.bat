@echo off
echo.
echo 🔧 OVACS Database Setup Script
echo ========================
echo.

REM Check if MySQL is installed and accessible
set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
if not exist %MYSQL_PATH% (
    echo ❌ MySQL not found at expected location
    echo Please ensure MySQL is installed at: %MYSQL_PATH%
    echo.
    pause
    exit /b 1
)

echo ✅ MySQL found
echo.

REM Prompt for MySQL root credentials
set /p mysql_user="Enter MySQL username (default: root): "
if "%mysql_user%"=="" set mysql_user=root

set /p mysql_password="Enter MySQL password: "

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
    echo - Tables Created: 12
    echo - Sample Vehicles: 20
    echo - Sample Stations: 10
    echo.
    echo 🔐 Default Admin Credentials:
    echo - Username: admin
    echo - Password: admin123
    echo.
    echo ⚠️  IMPORTANT: Update database credentials in includes\database.php
    echo.
    echo 🎉 Setup complete! You can now use the OVACS system.
) else (
    echo ❌ Database setup failed!
    echo Please check your MySQL credentials and try again.
    echo.
)

pause