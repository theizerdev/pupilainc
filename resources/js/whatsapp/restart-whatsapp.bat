@echo off
echo 🔄 Reiniciando WhatsApp API...
echo.

:: Detener cualquier proceso de Node.js relacionado
echo 📛 Deteniendo procesos de Node.js...
taskkill /F /IM node.exe 2>nul

:: Esperar un segundo
timeout /T 1 /NOBREAK >nul

:: Limpiar sesiones
echo 🧹 Limpiando sesiones...
cd /d "C:\laragon\www\medical\resources\js\whatsapp"
node scripts/clear-all-sessions.js

:: Esperar un segundo
timeout /T 1 /NOBREAK >nul

:: Iniciar el servidor
echo 🚀 Iniciando servidor WhatsApp...
start cmd /k "cd /d C:\laragon\www\medical\resources\js\whatsapp && node src/server.js"

echo ✅ Proceso completado
echo 📱 El servidor WhatsApp se está iniciando...
echo.
echo Puedes verificar el estado en: http://localhost:3002/api/whatsapp/status
echo.
pause