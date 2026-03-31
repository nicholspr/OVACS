@echo off
echo.
echo 🔄 Pushing OVACS to GitHub...
echo.

REM Initialize git if not already done
if not exist ".git\" (
    echo Initializing Git repository...
    git init
    git add .
    git commit -m "Initial commit: OVACS landing page"
    echo.
    echo ⚠️  To connect to GitHub, run these commands:
    echo git remote add origin https://github.com/nicholspr/OVACS.git
    echo git branch -M main
    echo git push -u origin main
    echo.
    pause
    exit /b
)

REM Add all changes
git add .

REM Check if there are any changes to commit
git diff --cached --quiet
if %errorlevel% equ 0 (
    echo ℹ️  No changes to commit.
    echo.
    pause
    exit /b
)

REM Ask for commit message
set /p commit_message="💬 Enter commit message: "
if "%commit_message%"=="" set commit_message=Update OVACS website

REM Commit and push
git commit -m "%commit_message%"
git push origin main

echo.
echo ✅ Successfully pushed to GitHub!
echo 🌐 View at: https://github.com/nicholspr/OVACS

echo.
pause