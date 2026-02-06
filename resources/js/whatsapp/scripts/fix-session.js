const fs = require('fs').promises;
const path = require('path');

async function fixWhatsAppSession() {
  try {
    console.log('🔄 Iniciando reparación de sesión WhatsApp...');
    
    // Obtener el nombre de la sesión del archivo .env o usar valor por defecto
    const sessionName = process.env.WHATSAPP_SESSION_NAME || 'default';
    const sessionPath = path.join('storage', 'sessions', sessionName);
    
    console.log(`📁 Ruta de sesión: ${sessionPath}`);
    
    // Verificar si existe el directorio de sesión
    try {
      await fs.access(sessionPath);
      console.log('📂 Directorio de sesión encontrado');
      
      // Listar archivos antes de eliminar
      const filesBefore = await fs.readdir(sessionPath);
      console.log('📋 Archivos antes de limpieza:', filesBefore);
      
      // Eliminar todos los archivos de la sesión
      await fs.rm(sessionPath, { recursive: true, force: true });
      console.log('✅ Sesión eliminada exitosamente');
      
      // Crear directorio nuevamente
      await fs.mkdir(sessionPath, { recursive: true });
      console.log('📂 Directorio de sesión recreado');
      
    } catch (error) {
      if (error.code === 'ENOENT') {
        console.log('📂 Directorio de sesión no existe, creando uno nuevo...');
        await fs.mkdir(sessionPath, { recursive: true });
        console.log('✅ Directorio de sesión creado');
      } else {
        throw error;
      }
    }
    
    console.log('🎉 Reparación de sesión completada');
    console.log('💡 Reinicia tu aplicación WhatsApp para generar una nueva sesión');
    
  } catch (error) {
    console.error('❌ Error reparando sesión:', error);
    process.exit(1);
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  fixWhatsAppSession();
}

module.exports = { fixWhatsAppSession };