<?php

// Ejemplo de mensaje de bienvenida que se enviará por WhatsApp

namespace App\Examples;

class MensajeBienvenidaMedico
{
    public static function ejemplo()
    {
        return "🩺 ¡Bienvenido/a Dr./Dra. Juan Pérez!\n\n"
            . "✅ Su cuenta ha sido creada exitosamente en nuestro sistema médico.\n\n"
            . "📋 *Datos de acceso:*\n"
            . "• Usuario: juan.perez\n"
            . "• Email: juan.perez@email.com\n"
            . "• Contraseña temporal: 12345678\n\n"
            . "🏥 *Información institucional:*\n"
            . "• Empresa: Centro Médico Vargas\n"
            . "• Sucursal: Sede Principal\n"
            . "• Especialidad: Cardiología, Medicina Interna\n\n"
            . "🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n"
            . "📱 ¿Preguntas? Contáctenos al (01) 555-1234\n\n"
            . "¡Gracias por formar parte de nuestro equipo médico! 🏥✨";
    }
    
    public static function formatoCompacto()
    {
        return "🩺 *¡Bienvenido Dr./Dra. [NOMBRE]!*\n\n"
            . "✅ Cuenta creada exitosamente\n\n"
            . "📱 *Acceso:*\n"
            . "Usuario: [USUARIO]\n"
            . "Contraseña: [DOCUMENTO]\n\n"
            . "🏥 [EMPRESA] - [SUCURSAL]\n"
            . "Especialidad: [ESPECIALIDAD]\n\n"
            . "🔐 Cambie su contraseña al iniciar sesión\n"
            . "📞 Dudas: [TELEFONO_EMPRESA]";
    }
}