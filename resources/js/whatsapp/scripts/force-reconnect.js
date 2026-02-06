const { WhatsAppService } = require('../src/services/WhatsAppService');
const logger = require('../src/utils/logger');

async function forceReconnect() {
  try {
    logger.info('🔄 Forzando reconexión completa de WhatsApp...');
    
    // Crear una instancia del servicio
    const whatsappService = new WhatsAppService();
    
    // Forzar desconexión si existe conexión
    if (whatsappService.sock) {
      logger.info('📴 Cerrando conexión existente...');
      await whatsappService.sock.logout();
      whatsappService.sock = null;
    }
    
    // Reiniciar todas las banderas
    whatsappService.isConnecting = false;
    whatsappService.isConnected = false;
    whatsappService.connectionState = 'disconnected';
    whatsappService.qrCode = null;
    whatsappService.reconnectAttempts = 0;
    
    logger.info('✅ Estado reiniciado completamente');
    logger.info('📱 Ahora puedes iniciar una nueva conexión');
    
    // Pequeña pausa antes de salir
    await new Promise(resolve => setTimeout(resolve, 1000));
    
  } catch (error) {
    logger.error('❌ Error forzando reconexión:', error);
  }
}

// Si se ejecuta directamente
if (require.main === module) {
  forceReconnect();
}

module.exports = { forceReconnect };