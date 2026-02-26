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
    this.sessions = new Map(); // Mapa de sesiones: companyId -> socket
    this.qrCodes = new Map(); // Mapa de QRs: companyId -> qrCode
    this.connectionStates = new Map(); // Mapa de estados: companyId -> state
    this.messageRateLimits = new Map(); // Mapa de rate limits: companyId -> { count, timestamp }
    
    this.baseSessionPath = path.join('storage', 'sessions');
    this.queueService = QueueService;
    this.reconnectAttempts = new Map();
    this.maxReconnectAttempts = 5;
    this.messageTimeout = 30000;
    this.rateLimitConfig = {
      maxMessages: 10,
      windowMs: 60000 // 1 minuto
    };
  }

  // Verificar límite de tasa de envío
  checkRateLimit(companyId) {
    const now = Date.now();
    const limit = this.messageRateLimits.get(companyId) || { count: 0, timestamp: now };
    
    // Resetear contador si pasó la ventana de tiempo
    if (now - limit.timestamp > this.rateLimitConfig.windowMs) {
      limit.count = 0;
      limit.timestamp = now;
    }
    
    if (limit.count >= this.rateLimitConfig.maxMessages) {
      return false;
    }
    
    limit.count++;
    this.messageRateLimits.set(companyId, limit);
    return true;
  }

  async initialize() {
    try {
      logger.whatsapp('Inicializando servicio WhatsApp Multi-Sesión...');
      
      // Asegurar directorio base
      try {
        await fs.access(this.baseSessionPath);
      } catch {
        await fs.mkdir(this.baseSessionPath, { recursive: true });
      }

      // Cargar empresas activas desde la BD y conectar
      const Company = require('../models/Company');
      const companies = await Company.findAll({ where: { isActive: true } });
      
      logger.whatsapp(`Encontradas ${companies.length} empresas activas para conectar.`);
      
      for (const company of companies) {
        this.connect(company.id).catch(err => 
          logger.error(`Error conectando empresa ${company.id}:`, err)
        );
      }
      
    } catch (error) {
      logger.error('Error inicializando WhatsApp service:', error);
      throw error;
    }
  }

  async connect(companyId) {
    if (this.connectionStates.get(companyId) === 'connecting') return;
    
    this.connectionStates.set(companyId, 'connecting');
    const sessionDir = path.join(this.baseSessionPath, `company_${companyId}`);
    
    try {
      logger.whatsapp(`Iniciando conexión para empresa ${companyId}...`);
      
      const { state, saveCreds } = await useMultiFileAuthState(sessionDir);
      
      const sock = makeWASocket({
        version: (await fetchLatestBaileysVersion()).version,
        auth: {
          creds: state.creds,
          keys: makeCacheableSignalKeyStore(state.keys, logger)
        },
        printQRInTerminal: false,
        browser: Browsers.macOS('Desktop'),
        syncFullHistory: false
      });

      this.sessions.set(companyId, sock);

      sock.ev.on('creds.update', saveCreds);

      sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
          logger.whatsapp(`QR recibido para empresa ${companyId}`);
          this.qrCodes.set(companyId, qr);
          // Emitir a todos los clientes conectados a la sala de esta empresa
          // Importante: El cliente debe unirse a la sala 'company_{id}'
          this.io.to(`company_${companyId}`).emit('qr_code', { qr, companyId });
          // También emitimos globalmente por si acaso el cliente no se unió a la sala
          this.io.emit(`qr_code_${companyId}`, { qr });
        }

        if (connection === 'close') {
          this.connectionStates.set(companyId, 'disconnected');
          this.sessions.delete(companyId);
          this.qrCodes.delete(companyId); // Limpiar QR al desconectar
          
          const shouldReconnect = (lastDisconnect?.error)?.output?.statusCode !== DisconnectReason.loggedOut;
          
          if (shouldReconnect) {
            logger.whatsapp(`Empresa ${companyId} desconectada. Reintentando...`);
            this.reconnect(companyId);
          } else {
            logger.whatsapp(`Empresa ${companyId} desconectada (Log out). Limpiando sesión.`);
            try {
                await fs.rm(sessionDir, { recursive: true, force: true });
            } catch (e) {
                logger.error(`Error borrando sesión ${companyId}:`, e);
            }
          }
        } else if (connection === 'open') {
          this.connectionStates.set(companyId, 'connected');
          this.qrCodes.delete(companyId);
          this.reconnectAttempts.set(companyId, 0);
          logger.whatsapp(`Empresa ${companyId} conectada exitosamente!`);
          this.io.to(`company_${companyId}`).emit('whatsapp_connected', { companyId });
          this.io.emit(`whatsapp_connected_${companyId}`, { companyId });
        }
      });
      // Manejar mensajes entrantes
      sock.ev.on('messages.upsert', async (m) => {
        const message = m.messages[0];
        if (!message.key.fromMe && m.type === 'notify') {
          const from = message.key.remoteJid;
          const msgContent = message.message?.conversation || 
                            message.message?.extendedTextMessage?.text || 
                            message.message?.imageMessage?.caption || 
                            'Media/Unknown';
          
          logger.info(`Mensaje recibido en empresa ${companyId}: ${from} - ${msgContent}`);
          
          // Emitir a socket.io
          this.io.to(`company_${companyId}`).emit('message', {
            id: message.key.id,
            from: from,
            message: msgContent,
            timestamp: message.messageTimestamp,
            companyId: companyId
          });

          // Guardar en BD
          try {
            await Message.create({
              messageId: message.key.id,
              from: from,
              to: sock.user.id.split(':')[0] + '@s.whatsapp.net',
              message: msgContent,
              type: message.message?.imageMessage ? 'image' : 'text',
              status: 'delivered',
              companyId: companyId,
              timestamp: new Date()
            });
          } catch (e) {
            logger.error(`Error guardando mensaje entrante empresa ${companyId}:`, e);
          }
        }
      });
      
    } catch (error) {
        logger.error(`Error fatal conectando empresa ${companyId}:`, error);
        this.connectionStates.set(companyId, 'error');
    }
  }

  async reconnect(companyId) {
      const attempts = this.reconnectAttempts.get(companyId) || 0;
      if (attempts < this.maxReconnectAttempts) {
          this.reconnectAttempts.set(companyId, attempts + 1);
          setTimeout(() => this.connect(companyId), 5000 * (attempts + 1));
      }
  }
  // Método para obtener el socket de una empresa
  getSocket(companyId) {
    return this.sessions.get(parseInt(companyId));
  }
  
  // Enviar mensaje (adaptado para multi-tenant)
  async sendMessage(companyId, { phone, message, type = 'text', url = null, caption = null }) {
    // Verificar Rate Limit
    if (!this.checkRateLimit(companyId)) {
        throw new Error(`Rate limit excedido para empresa ${companyId}. Espere un momento.`);
    }

    const sock = this.getSocket(companyId);
    
    if (!sock) {
        throw new Error(`Empresa ${companyId} no está conectada.`);
    }

    const jid = phone.includes('@s.whatsapp.net') ? phone : `${phone}@s.whatsapp.net`;
    
    try {
        await sock.presenceSubscribe(jid);
        await sock.sendPresenceUpdate('composing', jid);
        
        let sentMsg;
        if (type === 'text') {
            sentMsg = await sock.sendMessage(jid, { text: message });
        } else if (type === 'image' && url) {
            sentMsg = await sock.sendMessage(jid, { 
                image: { url }, 
                caption: caption || message 
            });
        }
        
        return sentMsg;
    } catch (error) {
        logger.error(`Error enviando mensaje empresa ${companyId}:`, error);
        throw error;
    }
  }

  async getProfilePicture(companyId, phone) {
    const sock = this.getSocket(companyId);
    if (!sock) return null;
    
    try {
      const jid = phone.includes('@s.whatsapp.net') ? phone : `${phone}@s.whatsapp.net`;
      const ppUrl = await sock.profilePictureUrl(jid, 'image');
      return ppUrl;
    } catch (error) {
      return null;
    }
  }

  async getStatus(companyId, phone) {
    const sock = this.getSocket(companyId);
    if (!sock) return null;
    
    try {
      const jid = phone.includes('@s.whatsapp.net') ? phone : `${phone}@s.whatsapp.net`;
      const status = await sock.fetchStatus(jid);
      return status?.status || null;
    } catch (error) {
      return null;
    }
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