@echo off
REM Laravel Reverb WebSocket Server Starter for Windows
REM Usage: start-reverb.bat

title Laravel Reverb - Real-time WebSocket Server
color 0E

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║   🚀 Laravel Reverb - Real-time WebSocket Server           ║
echo ╠════════════════════════════════════════════════════════════╣
echo ║ Starting WebSocket server for real-time notifications      ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo ⚙️  Configuration:
echo    Host: localhost
echo    Port: 8080
echo    Protocol: ws://localhost:8080
echo.
echo 📊 Features enabled:
echo    ✓ Real-time application notifications
echo    ✓ Live dashboard counter updates
echo    ✓ Sound notifications ('ting' effect)
echo    ✓ Auto-reconnection on disconnect
echo.
echo 🔌 Starting Reverb server...
echo    php artisan reverb:start
echo.
echo ✅ Server is running!
echo.
echo 📝 Next steps:
echo    1. Open Admin Dashboard in browser
echo    2. Submit a new application
echo    3. Watch dashboard update in real-time! 🎉
echo.
echo 🔍 Debug mode:
echo    Use: php artisan reverb:start --debug
echo    To see all WebSocket events and connections
echo.
echo 💡 Tips:
echo    • Keep this server running while developing
echo    • Use separate terminal windows for:
echo      - npm run dev (frontend Vite server)
echo      - php artisan reverb:start (WebSocket server - THIS WINDOW)
echo      - php artisan serve (Laravel app server)
echo.
echo ⚠️  Important:
echo    • Press Ctrl+C to stop this process
echo    • In production, use Supervisor to auto-restart
echo.

php artisan reverb:start

pause
