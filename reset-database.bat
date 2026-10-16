@echo off
rem ================================================================
rem  Deletes ALL data and fills the database with fresh demo data
rem  (runs: php artisan migrate:fresh --seed)
rem  MySQL must be running in the XAMPP Control Panel.
rem ================================================================

title TradeLog - reset database
cd /d "%~dp0"

set PHP=C:\xampp\php\php.exe
if not exist "%PHP%" set PHP=D:\xampp\php\php.exe
if not exist "%PHP%" set PHP=php

if not exist .env (
    echo Run start.bat first.
    pause
    exit /b
)

echo This deletes ALL trades, accounts and users and creates the demo data again.
set /p ANSWER=Are you sure? Type y and press Enter: 
if /i not "%ANSWER%"=="y" exit /b

"%PHP%" artisan migrate:fresh --seed --force
if errorlevel 1 goto failed

echo.
echo Done. Demo login: demo@tradelog.test / password
pause
exit /b

:failed
echo.
echo Something went wrong. Is MySQL running in the XAMPP Control Panel?
pause
