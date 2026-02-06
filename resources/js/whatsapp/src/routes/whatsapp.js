const express = require('express');
const rateLimit = require('express-rate-limit');
const { body, validationResult } = require('express-validator');
const router = express.Router();
const logger = require('../utils/logger');
const { validateApiKey } = require('./companies');
const rateLimitByCompany = require('../middleware/rateLimitByCompany');
const WhatsAppController = require('../controllers/WhatsAppController');

// Aplicar validación de API key a todas las rutas
router.use(validateApiKey);



// Rutas multi-tenant
router.get('/status', WhatsAppController.getStatus);
router.post('/connect', WhatsAppController.connect);
router.delete('/disconnect', WhatsAppController.disconnect);
router.post('/force-reset', WhatsAppController.forceReset);
router.get('/qr', WhatsAppController.getQRCode);
router.post('/send', rateLimitByCompany, WhatsAppController.sendMessage);
router.post('/send-document', rateLimitByCompany, WhatsAppController.upload.single('document'), WhatsAppController.sendDocument);
router.get('/messages', WhatsAppController.getMessages);









module.exports = router;