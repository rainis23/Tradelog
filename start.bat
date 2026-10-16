@echo off
rem ================================================================
rem  TradeLog - double-click this file to start the website.
rem
rem  Before that: open the XAMPP Control Panel and click "Start"
rem  next to MySQL. You do NOT need Apache, Composer or Node.js.
rem ================================================================

title TradeLog
cd /d "%~dp0"

rem --- 1. Find PHP: the PHP from XAMPP (on drive C: or D:), otherwise "php" from PATH
set PHP=C:\xampp\php\php.exe
if not exist "%PHP%" set PHP=D:\xampp\php\php.exe
if not exist "%PHP%" set PHP=php

"%PHP%" -v >nul 2>&1
if errorlevel 1 goto no_php

rem --- 2. Laravel 12 needs PHP 8.2 or newer
"%PHP%" -r "exit(version_compare(PHP_VERSION, '8.2.0', '<') ? 1 : 0);"
if errorlevel 1 goto old_php

rem --- 3. Check that the needed PHP extensions are turned on
"%PHP%" -r "$missing = 0; foreach (['pdo_mysql', 'openssl', 'mbstring'] as $ext) { if (!extension_loaded($ext)) { echo '   Missing PHP extension: ' . $ext . PHP_EOL; $missing = 1; } } exit($missing);"
if errorlevel 1 goto missing_extension

rem --- 4. The "vendor" folder (Laravel itself) is missing if the project was
rem        downloaded from GitHub. Then Composer installs it (needs internet).
if not exist vendor\autoload.php (
    echo Installing Laravel with Composer, this takes a minute...
    call composer install
)
if not exist vendor\autoload.php goto no_vendor

rem --- 5. First start only: create the settings file (.env) and a secret app key
if not exist .env (
    echo Creating the .env settings file...
    copy .env.example .env >nul
    "%PHP%" artisan key:generate
)

rem --- 6. Create the "tradelog" database and tables, add the demo data
rem        (when everything already exists, nothing changes)
echo.
echo Preparing the database...
"%PHP%" artisan migrate --seed --force
if errorlevel 1 goto database_error

rem --- 7. Start the website and open it in the browser
echo.
echo ================================================================
echo   TradeLog is running at:  http://127.0.0.1:8000
echo   Demo login:  demo@tradelog.test  /  password
echo   To stop the website, close this window.
echo ================================================================
echo.
start "" http://127.0.0.1:8000
"%PHP%" artisan serve
pause
exit /b


:no_php
echo.
echo  PHP was not found.
echo  Install XAMPP into C:\xampp - download it from https://www.apachefriends.org
echo  If XAMPP is in another folder, open start.bat with Notepad and change the line: set PHP=...
echo.
pause
exit /b

:old_php
echo.
echo  Your PHP is too old. Laravel 12 needs PHP 8.2 or newer.
echo  Install the newest XAMPP from https://www.apachefriends.org
echo.
"%PHP%" -v
pause
exit /b

:missing_extension
echo.
echo  How to fix: XAMPP Control Panel - Apache "Config" button - PHP (php.ini).
echo  Find the line, for example ";extension=pdo_mysql", delete the ";" at the start,
echo  save the file and run start.bat again.
echo.
pause
exit /b

:no_vendor
echo.
echo  The "vendor" folder is missing and Composer could not install it.
echo  Install Composer from https://getcomposer.org (choose C:\xampp\php\php.exe
echo  when it asks for PHP), then run start.bat again.
echo.
pause
exit /b

:database_error
echo.
echo  Could not prepare the database.
echo  Most likely MySQL is not running: open the XAMPP Control Panel,
echo  click "Start" next to MySQL and then run start.bat again.
echo.
pause
exit /b
