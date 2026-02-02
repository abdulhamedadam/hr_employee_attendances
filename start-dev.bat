@echo off
echo Starting Laravel Development Environment...
echo.

REM Start Laravel Server
start "Laravel Server" cmd /k "php artisan serve"

REM Wait 2 seconds
timeout /t 2 /nobreak > nul

REM Start Queue Worker
start "Queue Worker" cmd /k "php artisan queue:work --tries=3 --timeout=600"

echo.
echo ========================================
echo Laravel Server: http://localhost:8000
echo Queue Worker: Running in background
echo ========================================
echo.
echo Press any key to stop all services...
pause > nul

REM Kill all PHP processes (optional - be careful!)
REM taskkill /F /IM php.exe
