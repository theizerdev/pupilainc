const { sequelize } = require('../src/config/database');
const Session = require('../src/models/Session');
const Message = require('../src/models/Message');
const fs = require('fs').promises;
const path = require('path');
const logger = require('../src/utils/logger');

async function clearAllSessions() {
  try {
    logger.info('🧹 Iniciando limpieza completa de sesiones...');
    
    // 1. Eliminar todos los registros de la tabla de sesiones
    logger.info('📋 Eliminando registros de sesiones...');
    await sequelize.query('DELETE FROM whatsapp_sessions', { type: sequelize.QueryTypes.DELETE });
    logger.info('✅ Registros de sesiones eliminados');
    
    // 2. Eliminar todos los mensajes (opcional, pero recomendado para iniciar limpio)
    logger.info('💬 Eliminando mensajes...');
    await sequelize.query('DELETE FROM whatsapp_messages', { type: sequelize.QueryTypes.DELETE });
    logger.info('✅ Mensajes eliminados');
    
    // 3. Eliminar archivos de sesión en el sistema de archivos
    const sessionsDir = path.join(__dirname, '..', 'sessions');
    logger.info(`📁 Eliminando archivos de sesión en: ${sessionsDir}`);
    
    try {
      await fs.rm(sessionsDir, { recursive: true, force: true });
      logger.info('✅ Archivos de sesión eliminados');
      
      // Recrear el directorio
      await fs.mkdir(sessionsDir, { recursive: true });
      logger.info('✅ Directorio de sesiones recreado');
    } catch (error) {
      logger.warn('⚠️  No se pudieron eliminar archivos de sesión:', error.message);
    }
    
    // 4. Eliminar cachés de QR codes (si existen)
    const qrDir = path.join(__dirname, '..', 'public', 'qr-codes');
    logger.info(`📱 Eliminando códigos QR en: ${qrDir}`);
    
    try {
      await fs.rm(qrDir, { recursive: true, force: true });
      logger.info('✅ Códigos QR eliminados');
      
      // Recrear el directorio
      await fs.mkdir(qrDir, { recursive: true });
      logger.info('✅ Directorio de QR codes recreado');
    } catch (error) {
      logger.warn('⚠️  No se pudieron eliminar códigos QR:', error.message);
    }
    
    // 5. Reiniciar contadores de autoincremento
    logger.info('🔄 Reiniciando contadores de autoincremento...');
    await sequelize.query('ALTER TABLE whatsapp_sessions AUTO_INCREMENT = 1', { type: sequelize.QueryTypes.RAW });
    await sequelize.query('ALTER TABLE whatsapp_messages AUTO_INCREMENT = 1', { type: sequelize.QueryTypes.RAW });
    logger.info('✅ Contadores reiniciados');
    
    logger.info('🎉 ¡Limpieza completa realizada!');
    logger.info('📱 El sistema está listo para iniciar sesiones nuevas');
    
  } catch (error) {
    logger.error('❌ Error durante la limpieza:', error);
    process.exit(1);
  } finally {
    await sequelize.close();
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  clearAllSessions();
}

module.exports = { clearAllSessions };