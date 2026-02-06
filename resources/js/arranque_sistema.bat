@echo off
title Whatsapp - Inicio
echo Levantando api de whatsapp

cd C:\laragon\www\medical\resources\js\whatsapp

pm2 start start.js --name Whatsapp

pause
