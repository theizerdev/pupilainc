const { 
  default: makeWASocket, 
  DisconnectReason, 
  useMultiFileAuthState,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  Browsers
} = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const fs = require('fs').promises;
const path = require('path');
const logger = require('../utils/logger');
const QueueService = require('./QueueService');
const Message = require('../models/Message');
const Session = require('../models/Session');

class WhatsAppService {
  constructor(io) {
    this.io = io;
    this.sock = null;
    this.qrCode = null;
    this.isConnected = false;
    this.isConnecting = false;
    this.sessionPath = path.join('storage', 'sessions', process.env.WHATSAPP_SESSION_NAME || 'default');
    this.queueService = QueueService;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.connectionState = 'disconnected';
    this.lastSeen = null;
    this.messageTimeout = 30000; // 30 segundos timeout para mensajes
  }

  async initialize() {
    try {
      logger.whatsapp('Inicializando servicio WhatsApp...');
      
      // Crear directorio de sesión si no existe
      await this.ensureSessionDirectory();
      
      // Obtener versión más reciente de Baileys
      const { version, isLatest } = await fetchLatestBaileysVersion();
      logger.whatsapp(`Usando Baileys v${version.join('.')}, es la última: ${isLatest}`);
      
      // Conectar
      await this.connect();
      
    } catch (error) {
      logger.error('Error inicializando WhatsApp service:', error);
      throw error;
    }
  }

  async clearSession() {
    try {
      await fs.rm(this.sessionPath, { recursive: true, force: true });
      logger.whatsapp(`Sesión limpiada: ${this.sessionPath}`);
      await this.ensureSessionDirectory();
    } catch (error) {
      logger.error('Error limpiando sesión:', error);
    }
  }

  async ensureSessionDirectory() {
    try {
      await fs.access(this.sessionPath);
    } catch {
      await fs.mkdir(this.sessionPath, { recursive: true });
      logger.whatsapp(`Directorio de sesión creado: ${this.sessionPath}`);
    }
  }

  async connect() {
    if (this.isConnecting) {
      logger.whatsapp('Ya hay una conexión en progreso...');
      return;
    }

    this.isConnecting = true;
    this.connectionState = 'connecting';
    
    try {
      logger.whatsapp('Iniciando conexión a WhatsApp...');
      
      // Configurar autenticación multi-archivo
      const { state, saveCreds } = await useMultiFileAuthState(this.sessionPath);
      
      // Crear socket
      this.sock = makeWASocket({
        version: (await fetchLatestBaileysVersion()).version,
        auth: {
          creds: state.creds,
          keys: makeCacheableSignalKeyStore(state.keys, {
            trace: () => {},
            debug: () => {},
            info: () => {},
            warn: () => {},
            error: () => {},
            child: () => ({
              trace: () => {},
              debug: () => {},
              info: () => {},
              warn: () => {},
              error: () => {},
              child: () => ({})
            })
          })
        },
        browser: Browsers.macOS('Desktop'),
        printQRInTerminal: false,
        generateHighQualityLinkPreview: true,
        markOnlineOnConnect: true,
        connectTimeoutMs: 60000,
        keepAliveIntervalMs: 30000,
        // Configuración adicional para mejor estabilidad
        retryRequestDelayMs: 250,
        maxMsgRetryCount: 5,
        msgRetryCounterMap: {},
        // Manejo de eventos
        logger: {
          trace: () => {},
          debug: () => {},
          info: () => {},
          warn: (msg) => logger.warn('Baileys warn:', msg),
          error: (msg) => logger.error('Baileys error:', msg),
          child: () => ({
            trace: () => {},
            debug: () => {},
            info: () => {},
            warn: (msg) => logger.warn('Baileys warn:', msg),
            error: (msg) => logger.error('Baileys error:', msg),
            child: () => ({})
          })
        }
      });

      // Manejar eventos de conexión
      this.sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
          logger.whatsapp('QR Code recibido');
          this.qrCode = qr;
          this.connectionState = 'qr_ready';
          this.io.emit('qr', { qr });
        }
        
        if (connection === 'close') {
          this.isConnected = false;
          this.isConnecting = false;
          this.connectionState = 'disconnected';
          
          const statusCode = lastDisconnect?.error?.output?.statusCode;
          const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
          logger.whatsapp(`Conexión cerrada, razón: ${statusCode}, reconectar: ${shouldReconnect}`);
          
          if (!shouldReconnect) {
            logger.whatsapp('Sesión expirada (loggedOut). Limpiando sesión para permitir nuevo QR...');
            await this.clearSession();
          } else if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            logger.whatsapp(`Intentando reconexión ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            setTimeout(() => this.connect(), 5000 * this.reconnectAttempts);
          }
        } else if (connection === 'open') {
          this.isConnected = true;
          this.isConnecting = false;
          this.connectionState = 'connected';
          this.reconnectAttempts = 0;
          this.qrCode = null;
          this.lastSeen = new Date();
          
          logger.whatsapp('✅ WhatsApp conectado exitosamente');
          this.io.emit('connected', { 
            user: this.sock.user,
            timestamp: new Date().toISOString()
          });
        }
      });

      // Manejar mensajes entrantes
      this.sock.ev.on('messages.upsert', async (m) => {
        const message = m.messages[0];
        if (!message.key.fromMe && m.type === 'notify') {
          logger.message('received', {
            from: message.key.remoteJid,
            message: message.message,
            timestamp: new Date().toISOString()
          });
          
          this.io.emit('message', {
            id: message.key.id,
            from: message.key.remoteJid,
            message: message.message,
            timestamp: message.messageTimestamp
          });
        }
      });

      // Manejar actualizaciones de estado de mensajes (delivered, read)
      this.sock.ev.on('messages.update', async (updates) => {
        for (const update of updates) {
          try {
            const { key, update: msgUpdate } = update;
            if (!key?.id || !msgUpdate?.status) continue;

            const statusMap = {
              2: 'sent',
              3: 'delivered',
              4: 'read',
            };
            const newStatus = statusMap[msgUpdate.status];
            if (!newStatus) continue;

            const [affectedRows] = await Message.update(
              { status: newStatus },
              { where: { messageId: key.id } }
            );

            if (affectedRows > 0) {
              logger.whatsapp(`Mensaje ${key.id} actualizado a: ${newStatus}`);
            }
          } catch (err) {
            logger.error('Error actualizando status de mensaje:', err.message);
          }
        }
      });

      // Manejar actualizaciones de credenciales
      this.sock.ev.on('creds.update', saveCreds);
      
    } catch (error) {
      this.isConnecting = false;
      logger.error('Error en conexión de WhatsApp:', error);
      throw error;
    }
  }

  formatPhoneNumber(number) {
    // Remover caracteres no numéricos
    let cleaned = number.replace(/\D/g, '');
    
    // Si empieza con 58 (Venezuela), agregar @s.whatsapp.net
    if (cleaned.startsWith('58')) {
      return `${cleaned}@s.whatsapp.net`;
    }
    
    // Si empieza con 0, removerlo y agregar @s.whatsapp.net
    if (cleaned.startsWith('0')) {
      cleaned = cleaned.substring(1);
    }
    
    // Por defecto, agregar @s.whatsapp.net
    return `${cleaned}@s.whatsapp.net`;
  }

  async sendMessage(to, content, options = {}) {
    if (!this.isConnected) {
      throw new Error('WhatsApp no está conectado');
    }

    try {
      // Formatear número
      const jid = this.formatPhoneNumber(to);
      
      // Preparar mensaje
      let messageContent;
      if (typeof content === 'string') {
        messageContent = { text: content };
      } else {
        messageContent = content;
      }
      
      // Crear promesa con timeout
      const sendPromise = this.sock.sendMessage(jid, messageContent, options);
      const timeoutPromise = new Promise((_, reject) => 
        setTimeout(() => reject(new Error('Timed Out')), this.messageTimeout)
      );
      
      // Enviar mensaje con timeout
      const result = await Promise.race([sendPromise, timeoutPromise]);
      
      logger.message('sent', {
        to: jid,
        messageId: result.key.id,
        timestamp: new Date().toISOString(),
        companyId: options.companyId
      });
      
      // Guardar en base de datos
      await Message.create({
        messageId: result.key.id,
        from: this.sock.user?.id || 'self',
        to: jid,
        message: typeof messageContent === 'string' ? messageContent : JSON.stringify(messageContent),
        type: options.type || 'text',
        status: 'sent',
        companyId: options.companyId || 1
      });
      
      return {
        success: true,
        messageId: result.key.id,
        timestamp: result.messageTimestamp
      };
      
    } catch (error) {
      logger.error('Error sending WhatsApp message:', {
        error: error.message,
        to: to,
        companyId: options.companyId,
        stack: error.stack
      });
      
      // Si es timeout, intentar reconectar
      if (error.message === 'Timed Out') {
        logger.warn('Message timeout detected, checking connection...');
        await this.checkAndReconnect();
      }
      
      throw error;
    }
  }

  async checkAndReconnect() {
    try {
      if (!this.isConnected) {
        logger.info('WhatsApp disconnected, attempting to reconnect...');
        await this.connect();
      }
    } catch (error) {
      logger.error('Error during reconnection attempt:', error);
    }
  }

  async disconnect() {
    try {
      if (this.sock) {
        await this.sock.logout();
        this.isConnected = false;
        this.connectionState = 'disconnected';
        logger.whatsapp('WhatsApp desconectado');
      }
    } catch (error) {
      logger.error('Error disconnecting WhatsApp:', error);
      throw error;
    }
  }

  async forceReset() {
    try {
      logger.whatsapp('Forzando reinicio completo del servicio...');
      
      // Forzar desconexión
      if (this.sock) {
        try {
          await this.sock.logout();
          await this.sock.end();
        } catch (error) {
          logger.warn('Error durante logout forzado:', error.message);
        }
        this.sock = null;
      }
      
      // Reiniciar todas las variables de estado
      this.isConnected = false;
      this.isConnecting = false;
      this.connectionState = 'disconnected';
      this.qrCode = null;
      this.reconnectAttempts = 0;
      this.lastSeen = null;
      
      // Pequeña pausa para asegurar limpieza
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      logger.whatsapp('✅ Servicio reiniciado completamente');
      
    } catch (error) {
      logger.error('Error forzando reinicio:', error);
      throw error;
    }
  }

  getStatus() {
    return {
      connected: this.isConnected,
      connectionState: this.connectionState,
      user: this.sock?.user || null,
      qr: this.qrCode,
      lastSeen: this.lastSeen,
      reconnectAttempts: this.reconnectAttempts,
      messageTimeout: this.messageTimeout
    };
  }

  async shutdown() {
    try {
      logger.whatsapp('Apagando servicio WhatsApp...');
      if (this.sock) {
        await this.sock.end();
      }
      logger.whatsapp('Servicio WhatsApp apagado');
    } catch (error) {
      logger.error('Error shutting down WhatsApp service:', error);
      throw error;
    }
  }
}

module.exports = WhatsAppService;