const WhatsAppService = require('../services/WhatsAppService');
const Message = require('../models/Message');
const Session = require('../models/Session');
const logger = require('../utils/logger');
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;
const antiBlockProtection = require('../middleware/antiBlockProtection');
const QRCode = require('qrcode');

class WhatsAppController {
  async getContactInfo(req, res) {
    try {
      const whatsappService = req.app.locals.whatsappService;
      const { phone } = req.query;
      const companyId = req.company.id;

      if (!whatsappService) {
        return res.status(500).json({ success: false, error: 'Service not initialized' });
      }

      if (!phone) {
        return res.status(400).json({ success: false, error: 'Phone number required' });
      }

      const [profilePic, status] = await Promise.all([
        whatsappService.getProfilePicture(companyId, phone),
        whatsappService.getStatus(companyId, phone)
      ]);

      res.json({
        success: true,
        data: {
          phone,
          profilePic,
          status
        }
      });
    } catch (error) {
      logger.error('Error getting contact info:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async getStatus(req, res) {
    try {
      const whatsappService = req.app.locals.whatsappService;
      const companyId = req.company.id;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized',
          company: req.company.name
        });
      }

      const connectionState = whatsappService.connectionStates.get(companyId) || 'disconnected';
      const sock = whatsappService.getSocket(companyId);
      const qr = whatsappService.qrCodes.get(companyId);

      res.json({ 
          success: true, 
          company: req.company.name,
          connected: connectionState === 'connected',
          connectionState,
          user: sock?.user || null,
          qr
      });
    } catch (error) {
      logger.error('Error getting status:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async connect(req, res) {
    try {
      const whatsappService = req.app.locals.whatsappService;
      const companyId = req.company.id;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      await whatsappService.connect(companyId);
      res.json({
        success: true,
        company: req.company.name,
        message: 'Connection initiated'
      });
    } catch (error) {
      logger.error('Error connecting:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async disconnect(req, res) {
    try {
      const whatsappService = req.app.locals.whatsappService;
      const companyId = req.company.id;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      // No hay método logout en el nuevo servicio, sino desconexión manual si es necesario
      // Pero podemos simularlo borrando sesión o simplemente matando el socket
      // Por ahora, asumimos que desconectar es "no intentar reconectar"
      // TODO: Implementar disconnect específico en Service si se requiere
      
      res.json({ success: true, message: 'Disconnect not fully implemented for multi-tenant yet' });
    } catch (error) {
      logger.error('Error disconnecting:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async forceReset(req, res) {
    try {
      const whatsappService = req.app.locals.whatsappService;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      logger.info('🔄 Forzando reinicio completo...', { company: req.company.name });
      await whatsappService.forceReset();

      res.json({
        success: true,
        message: 'WhatsApp service reset successfully',
        company: req.company.name
      });
    } catch (error) {
      logger.error('Error forcing reset:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async getQRCode(req, res) {
    try {
      // Acceder a la instancia global del servicio WhatsApp
      const whatsappService = req.app.locals.whatsappService;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized',
          company: req.company.name
        });
      }

      const status = whatsappService.getStatus();
      const qrRaw = status.qr;

      if (qrRaw) {
        const qrDataUrl = await QRCode.toDataURL(qrRaw, { width: 300, margin: 2 });
        res.json({
          success: true,
          qr: qrDataUrl,
          company: req.company.name,
          message: 'QR code available'
        });
      } else {
        res.json({
          success: false,
          error: 'QR code not available. Connection status: ' + (status.connectionState || 'unknown'),
          company: req.company.name,
          connectionState: status.connectionState
        });
      }
    } catch (error) {
      logger.error('Error getting QR code:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async sendMessage(req, res) {
    try {
      const { to, message, type = 'text', mediaUrl, isWelcome = false } = req.body;
      const whatsappService = req.app.locals.whatsappService;
      const companyId = req.company.id; // Usar ID consistente

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      // 🔒 PROTECCIÓN ANTI-BLOQUEO CRÍTICA
      try {
        if (isWelcome) {
          // Para mensajes de bienvenida, usar protección especial
          await antiBlockProtection.protectWelcomeMessage(companyId, to, message);
        } else {
          // Para mensajes normales, usar protección completa
          await antiBlockProtection.protectMessage(companyId, to, message);
        }
      } catch (protectionError) {
        logger.warn(`Message blocked by anti-block protection: ${protectionError.message}`, {
          companyId: companyId,
          companyName: req.company.name,
          to,
          reason: protectionError.message,
          isWelcome
        });

        return res.status(429).json({
          success: false,
          error: protectionError.message,
          code: 'ANTI_BLOCK_PROTECTION',
          company: req.company.name,
          isWelcome
        });
      }

      const result = await whatsappService.sendMessage(companyId, {
        phone: to,
        message,
        type,
        url: mediaUrl
      });

      res.json({
        success: true,
        messageId: result?.key?.id,
        company: req.company.name,
        antiBlock: {
          protected: true,
          message: isWelcome ? 'Mensaje de bienvenida enviado' : 'Mensaje validado y protegido contra bloqueo',
          isWelcome: isWelcome
        }
      });
    } catch (error) {
      logger.error('Error sending message:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async getMessages(req, res) {
    try {
      const { page = 1, limit = 50, status, from, to } = req.query;
      const where = { companyId: req.company.company_id };

      if (status) where.status = status;
      if (from) where.from = from;
      if (to) where.to = to;

      const messages = await Message.findAndCountAll({
        where,
        limit: parseInt(limit),
        offset: (parseInt(page) - 1) * parseInt(limit),
        order: [['createdAt', 'DESC']]
      });

      res.json({
        success: true,
        messages: messages.rows,
        total: messages.count,
        page: parseInt(page),
        totalPages: Math.ceil(messages.count / parseInt(limit)),
        company: req.company.name
      });
    } catch (error) {
      logger.error('Error getting messages:', error);
      res.status(500).json({ success: false, error: error.message });
    }
  }

  async sendDocument(req, res) {
    try {
      const { to, message, caption = '' } = req.body;
      const whatsappService = req.app.locals.whatsappService;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      if (!req.file) {
        return res.status(400).json({
          success: false,
          error: 'No file uploaded'
        });
      }

      if (!to) {
        return res.status(400).json({
          success: false,
          error: 'Recipient phone number is required'
        });
      }

      // Leer el archivo subido
      const fileBuffer = await fs.readFile(req.file.path);
      const fileName = req.file.originalname;
      const mimeType = req.file.mimetype;

      // Preparar el contenido del documento para Baileys
      const documentContent = {
        document: fileBuffer,
        mimetype: mimeType,
        fileName: fileName,
        caption: caption || message || ''
      };

      // Enviar el documento usando el servicio WhatsApp
      const result = await whatsappService.sendMessage(to, documentContent, {
        type: 'document',
        companyId: req.company.company_id
      });

      // Limpiar el archivo temporal
      await fs.unlink(req.file.path).catch(err => {
        logger.warn('Error deleting temporary file:', err);
      });

      res.json({
        success: true,
        messageId: result.messageId,
        company: req.company.name,
        fileName: fileName
      });
    } catch (error) {
      logger.error('Error sending document:', error);

      // Limpiar el archivo temporal en caso de error
      if (req.file && req.file.path) {
        await fs.unlink(req.file.path).catch(err => {
          logger.warn('Error deleting temporary file after error:', err);
        });
      }

      res.status(500).json({ success: false, error: error.message });
    }
  }

  async sendImage(req, res) {
    try {
      const { to, message, caption = '' } = req.body;
      const whatsappService = req.app.locals.whatsappService;

      if (!whatsappService) {
        return res.status(500).json({
          success: false,
          error: 'WhatsApp service not initialized'
        });
      }

      if (!req.file) {
        return res.status(400).json({
          success: false,
          error: 'No file uploaded'
        });
      }

      if (!to) {
        return res.status(400).json({
          success: false,
          error: 'Recipient phone number is required'
        });
      }

      const fileBuffer = await fs.readFile(req.file.path);
      const mimeType = req.file.mimetype;

      const imageContent = {
        image: fileBuffer,
        mimetype: mimeType,
        caption: caption || message || ''
      };

      const result = await whatsappService.sendMessage(to, imageContent, {
        type: 'image',
        companyId: req.company.company_id
      });

      await fs.unlink(req.file.path).catch(err => {
        logger.warn('Error deleting temporary file:', err);
      });

      res.json({
        success: true,
        messageId: result.messageId,
        company: req.company.name
      });
    } catch (error) {
      logger.error('Error sending image:', error);

      if (req.file && req.file.path) {
        await fs.unlink(req.file.path).catch(err => {
          logger.warn('Error deleting temporary file after error:', err);
        });
      }

      res.status(500).json({ success: false, error: error.message });
    }
  }
}

// Configuración de multer para manejar la subida de archivos
const storage = multer.diskStorage({
  destination: async (req, file, cb) => {
    const uploadDir = path.join(__dirname, '../../temp');
    try {
      await fs.mkdir(uploadDir, { recursive: true });
      cb(null, uploadDir);
    } catch (error) {
      cb(error, uploadDir);
    }
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, uniqueSuffix + '-' + file.originalname);
  }
});

const upload = multer({
  storage: storage,
  limits: {
    fileSize: 16 * 1024 * 1024 // 16MB límite
  },
  fileFilter: (req, file, cb) => {
    // Permitir archivos de Excel y otros documentos comunes
    const allowedTypes = [
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'text/plain',
      'text/csv'
    ];

    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Tipo de archivo no permitido. Use Excel, PDF, Word o archivos de texto.'), false);
    }
  }
});

const uploadImage = multer({
  storage: storage,
  limits: {
    fileSize: 8 * 1024 * 1024
  },
  fileFilter: (req, file, cb) => {
    const allowedTypes = [
      'image/png',
      'image/jpeg',
      'image/jpg',
      'image/webp'
    ];

    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Tipo de archivo no permitido. Use PNG o JPG.'), false);
    }
  }
});

// Exportar el controlador y el middleware de upload
const controller = new WhatsAppController();
controller.upload = upload;
controller.uploadImage = uploadImage;

module.exports = controller;
