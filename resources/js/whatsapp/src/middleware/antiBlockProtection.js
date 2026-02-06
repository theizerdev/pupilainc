const logger = require('../utils/logger');

class AntiBlockProtection {
  constructor() {
    this.messageQueue = new Map();
    this.userLimits = new Map();
    this.companyDailyLimits = new Map();
    
    // Límites configurables desde variables de entorno
    this.MESSAGE_DELAY_MS = parseInt(process.env.ANTI_BLOCK_MESSAGE_DELAY_MS) || 30000; // 30 segundos
    this.MAX_MESSAGES_PER_HOUR_PER_USER = parseInt(process.env.ANTI_BLOCK_MAX_PER_HOUR) || 10;
    this.MAX_MESSAGES_PER_DAY_PER_USER = parseInt(process.env.ANTI_BLOCK_MAX_PER_DAY) || 50;
    this.BUSINESS_HOURS_START = parseInt(process.env.ANTI_BLOCK_BUSINESS_HOURS_START) || 7; // 7 AM
    this.BUSINESS_HOURS_END = parseInt(process.env.ANTI_BLOCK_BUSINESS_HOURS_END) || 22; // 10 PM
    
    // Limpiar cache cada hora
    setInterval(() => {
      this.cleanOldEntries();
    }, 60 * 60 * 1000);
  }

  /**
   * Verifica y aplica delay entre mensajes al mismo destinatario
   */
  async checkAndDelay(companyId, to, message) {
    const key = `${companyId}:${to}`;
    const now = Date.now();
    
    if (this.messageQueue.has(key)) {
      const lastMessage = this.messageQueue.get(key);
      const timeDiff = now - lastMessage;
      
      if (timeDiff < this.MESSAGE_DELAY_MS) {
        const delay = this.MESSAGE_DELAY_MS - timeDiff;
        logger.info(`Anti-block: Delaying message to ${to} by ${delay}ms`);
        await this.sleep(delay);
      }
    }
    
    this.messageQueue.set(key, now);
    this.cleanOldEntries();
  }

  /**
   * Valida límites por usuario (por hora y por día)
   */
  checkUserLimits(companyId, to) {
    const key = `${companyId}:${to}`;
    const now = new Date();
    const hourKey = `${key}:${now.getHours()}`;
    const dayKey = `${key}:${now.getDate()}:${now.getMonth()}:${now.getFullYear()}`;
    
    // Límite por hora
    const hourlyCount = this.userLimits.get(hourKey) || 0;
    if (hourlyCount >= this.MAX_MESSAGES_PER_HOUR_PER_USER) {
      throw new Error(`Límite de ${this.MAX_MESSAGES_PER_HOUR_PER_USER} mensajes por hora excedido para ${to}`);
    }
    
    // Límite por día
    const dailyCount = this.userLimits.get(dayKey) || 0;
    if (dailyCount >= this.MAX_MESSAGES_PER_DAY_PER_USER) {
      throw new Error(`Límite de ${this.MAX_MESSAGES_PER_DAY_PER_USER} mensajes por día excedido para ${to}`);
    }
    
    // Incrementar contadores
    this.userLimits.set(hourKey, hourlyCount + 1);
    this.userLimits.set(dayKey, dailyCount + 1);
  }

  /**
   * Valida horarios comerciales
   */
  validateBusinessHours() {
    const now = new Date();
    const hour = now.getHours();
    const day = now.getDay();
    
    // Fuera de horario comercial (7 AM - 10 PM)
    if (hour < this.BUSINESS_HOURS_START || hour >= this.BUSINESS_HOURS_END) {
      throw new Error(`Mensajes no permitidos fuera del horario comercial (${this.BUSINESS_HOURS_START}:00 - ${this.BUSINESS_HOURS_END}:00)`);
    }
    
    // Domingos (día 0)
    if (day === 0) {
      throw new Error('Mensajes no permitidos los domingos');
    }
  }

  /**
   * Valida el contenido del mensaje contra spam
   */
  validateMessageContent(message) {
    if (!message || typeof message !== 'string') {
      throw new Error('Mensaje inválido');
    }
    
    // Longitud mínima
    if (message.length < 2) {
      throw new Error('Mensaje demasiado corto');
    }
    
    // Longitud máxima (WhatsApp limit)
    if (message.length > 4096) {
      throw new Error('Mensaje demasiado largo (máximo 4096 caracteres)');
    }
    
    // Detectar patrones de spam
    const spamPatterns = [
      /(.)\1{10,}/, // Mismo carácter repetido 10+ veces
      /\b(compra|oferta|descuento|promoción)\b.*\b(compra|oferta|descuento|promoción)\b.*\b(compra|oferta|descuento|promoción)\b/i, // Spam comercial
      /https?:\/\/.*\..*\..*\..*\..*/i, // URLs sospechosas (múltiples puntos)
      /\d{10,}/ // Números largos (posibles teléfonos/IDs)
    ];
    
    for (const pattern of spamPatterns) {
      if (pattern.test(message)) {
        throw new Error('Mensaje detectado como spam');
      }
    }
    
    // Limitar uso de mayúsculas (más de 50%)
    const uppercaseCount = (message.match(/[A-Z]/g) || []).length;
    if (uppercaseCount > message.length * 0.5) {
      throw new Error('Mensaje con demasiadas mayúsculas (posible spam)');
    }
  }

  /**
   * Valida el número destinatario
   */
  validateRecipient(to) {
    if (!to || typeof to !== 'string') {
      throw new Error('Número destinatario inválido');
    }
    
    // Formato WhatsApp JID
    const whatsappPattern = /^\d{10,15}@s\.whatsapp\.net$/;
    const groupPattern = /^\d{10,20}@g\.us$/;
    // Formato teléfono directo (con o sin +)
    const phonePattern = /^\+?\d{10,15}$/;
    
    if (!whatsappPattern.test(to) && !groupPattern.test(to) && !phonePattern.test(to)) {
      throw new Error('Formato de número WhatsApp inválido');
    }
    
    // Prevenir números de prueba comunes
    const testNumbers = ['1234567890', '0000000000', '1111111111', '9999999999'];
    const numberOnly = to.replace(/[@+].*$/, '').replace(/\D/g, '');
    
    if (testNumbers.some(test => numberOnly.includes(test))) {
      throw new Error('Número de prueba no permitido');
    }
  }

  /**
   * Método principal de protección - ejecuta todas las validaciones
   */
  async protectMessage(companyId, to, message) {
    try {
      // Validar horario comercial
      this.validateBusinessHours();
      
      // Validar destinatario
      this.validateRecipient(to);
      
      // Validar contenido
      this.validateMessageContent(message);
      
      // Verificar límites por usuario
      this.checkUserLimits(companyId, to);
      
      // Aplicar delay si es necesario
      await this.checkAndDelay(companyId, to, message);
      
      logger.info(`Anti-block: Message validated and delayed for ${to}`, {
        companyId,
        to,
        messageLength: message.length
      });
      
      return true;
    } catch (error) {
      logger.warn(`Anti-block: Message blocked - ${error.message}`, {
        companyId,
        to,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Método especial para mensajes de bienvenida - menos restricciones
   */
  async protectWelcomeMessage(companyId, to, message) {
    try {
      // Para mensajes de bienvenida, solo validar destinatario y contenido básico
      this.validateRecipient(to);
      
      // Validación más relajada del contenido
      if (!message || typeof message !== 'string') {
        throw new Error('Mensaje inválido');
      }
      
      if (message.length < 2 || message.length > 4096) {
        throw new Error('Mensaje con longitud inválida');
      }
      
      // No aplicar límites de horario ni de cantidad para mensajes de bienvenida
      // pero sí aplicar un delay mínimo
      await this.checkAndDelay(companyId, to, message);
      
      logger.info(`Anti-block: Welcome message validated for ${to}`, {
        companyId,
        to,
        messageLength: message.length,
        type: 'welcome'
      });
      
      return true;
    } catch (error) {
      logger.warn(`Anti-block: Welcome message blocked - ${error.message}`, {
        companyId,
        to,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Limpia entradas antiguas del cache
   */
  cleanOldEntries() {
    const now = Date.now();
    const maxAge = 24 * 60 * 60 * 1000; // 24 horas
    
    for (const [key, timestamp] of this.messageQueue.entries()) {
      if (now - timestamp > maxAge) {
        this.messageQueue.delete(key);
      }
    }
    
    for (const [key, timestamp] of this.userLimits.entries()) {
      if (now - timestamp > maxAge) {
        this.userLimits.delete(key);
      }
    }
  }

  /**
   * Helper para delays asíncronos
   */
  sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Obtiene estadísticas del sistema anti-bloqueo
   */
  getStats() {
    return {
      messageQueueSize: this.messageQueue.size,
      userLimitsSize: this.userLimits.size,
      companyDailyLimitsSize: this.companyDailyLimits.size,
      config: {
        messageDelayMs: this.MESSAGE_DELAY_MS,
        maxPerHour: this.MAX_MESSAGES_PER_HOUR_PER_USER,
        maxPerDay: this.MAX_MESSAGES_PER_DAY_PER_USER,
        businessHours: `${this.BUSINESS_HOURS_START}:00 - ${this.BUSINESS_HOURS_END}:00`
      }
    };
  }
}

// Exportar instancia única (singleton)
module.exports = new AntiBlockProtection();