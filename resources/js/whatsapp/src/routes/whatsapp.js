const express = require('express');
const rateLimit = require('express-rate-limit');
const { body, validationResult } = require('express-validator');
const { Op, fn, col, literal } = require('sequelize');
const router = express.Router();
const logger = require('../utils/logger');
const { validateApiKey } = require('./companies');
const rateLimitByCompany = require('../middleware/rateLimitByCompany');
const WhatsAppController = require('../controllers/WhatsAppController');
const Message = require('../models/Message');
const { sequelize } = require('../config/database');

// Aplicar validación de API key a todas las rutas
router.use(validateApiKey);



// Rutas multi-tenant
router.get('/contact-info', WhatsAppController.getContactInfo);
router.get('/status', WhatsAppController.getStatus);
router.post('/connect', WhatsAppController.connect);
router.delete('/disconnect', WhatsAppController.disconnect);
router.post('/force-reset', WhatsAppController.forceReset);
router.get('/qr', WhatsAppController.getQRCode);
router.post('/send', rateLimitByCompany, WhatsAppController.sendMessage);
router.post('/send-document', rateLimitByCompany, WhatsAppController.upload.single('document'), WhatsAppController.sendDocument);
router.post('/send-image', rateLimitByCompany, WhatsAppController.uploadImage.single('image'), WhatsAppController.sendImage);
router.get('/messages', WhatsAppController.getMessages);

router.get('/conversations', async (req, res) => {
  try {
    const companyId = req.company.company_id;
    const { search, page = 1, per_page = 20 } = req.query;
    const pageNum = Math.max(1, parseInt(page));
    const limit = Math.max(1, Math.min(100, parseInt(per_page) || 20));
    const offset = (pageNum - 1) * limit;

    const companyPhone = await Message.findOne({
      where: { companyId },
      attributes: ['from'],
      order: [['createdAt', 'DESC']],
      raw: true
    });

    if (!companyPhone) {
      return res.json({
        success: true,
        data: [],
        pagination: { page: pageNum, per_page: limit, total: 0, total_pages: 0 }
      });
    }

    let searchCondition = '';
    const replacements = { companyId };

    if (search) {
      searchCondition = 'AND contact LIKE :search';
      replacements.search = `%${search}%`;
    }

    const countQuery = `
      SELECT COUNT(*) as total FROM (
        SELECT
          CASE
            WHEN \`from\` = (
              SELECT m2.\`from\` FROM whatsapp_messages m2
              WHERE m2.companyId = :companyId AND m2.\`to\` != m2.\`from\`
              ORDER BY m2.createdAt DESC LIMIT 1
            ) THEN \`to\`
            ELSE \`from\`
          END AS contact
        FROM whatsapp_messages
        WHERE companyId = :companyId
        GROUP BY contact
        HAVING contact IS NOT NULL ${searchCondition}
      ) AS sub
    `;

    const [countResult] = await sequelize.query(countQuery, {
      replacements,
      type: sequelize.QueryTypes.SELECT
    });
    const total = countResult.total;

    const dataQuery = `
      SELECT
        contact AS id,
        REPLACE(contact, '@s.whatsapp.net', '') AS name,
        last_msg AS last_message,
        last_time AS last_message_time,
        last_type AS last_message_type,
        msg_count AS total_messages
      FROM (
        SELECT
          CASE
            WHEN \`from\` = (
              SELECT m2.\`from\` FROM whatsapp_messages m2
              WHERE m2.companyId = :companyId AND m2.\`to\` != m2.\`from\`
              ORDER BY m2.createdAt DESC LIMIT 1
            ) THEN \`to\`
            ELSE \`from\`
          END AS contact,
          SUBSTRING_INDEX(GROUP_CONCAT(message ORDER BY createdAt DESC SEPARATOR '|||'), '|||', 1) AS last_msg,
          MAX(createdAt) AS last_time,
          SUBSTRING_INDEX(GROUP_CONCAT(type ORDER BY createdAt DESC SEPARATOR '|||'), '|||', 1) AS last_type,
          COUNT(*) AS msg_count
        FROM whatsapp_messages
        WHERE companyId = :companyId
        GROUP BY contact
        HAVING contact IS NOT NULL ${searchCondition}
      ) AS conversations
      ORDER BY last_time DESC
      LIMIT :limit OFFSET :offset
    `;

    replacements.limit = limit;
    replacements.offset = offset;

    const conversations = await sequelize.query(dataQuery, {
      replacements,
      type: sequelize.QueryTypes.SELECT
    });

    const result = conversations.map(c => ({
      ...c,
      unread_count: 0,
      online: false,
      pinned: false,
      muted: false
    }));

    res.json({
      success: true,
      data: result,
      pagination: {
        page: pageNum,
        per_page: limit,
        total,
        total_pages: Math.ceil(total / limit)
      }
    });
  } catch (error) {
    logger.error('Error fetching conversations:', error);
    res.status(500).json({ success: false, error: 'Failed to fetch conversations' });
  }
});


router.get('/stats', async (req, res) => {
  try {
    const companyId = req.company.company_id;
    const companyWhere = { companyId };

    const [total, sent, delivered, read, failed, pending] = await Promise.all([
      Message.count({ where: companyWhere }),
      Message.count({ where: { ...companyWhere, status: 'sent' } }),
      Message.count({ where: { ...companyWhere, status: 'delivered' } }),
      Message.count({ where: { ...companyWhere, status: 'read' } }),
      Message.count({ where: { ...companyWhere, status: 'failed' } }),
      Message.count({ where: { ...companyWhere, status: 'pending' } }),
    ]);

    const todayStart = new Date();
    todayStart.setHours(0, 0, 0, 0);
    const todayCount = await Message.count({
      where: {
        ...companyWhere,
        createdAt: { [Op.gte]: todayStart },
      },
    });

    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 6);
    sevenDaysAgo.setHours(0, 0, 0, 0);
    const dailyMessages = await Message.findAll({
      attributes: [
        [fn('DATE', col('createdAt')), 'date'],
        [fn('COUNT', col('id')), 'count'],
      ],
      where: {
        ...companyWhere,
        createdAt: { [Op.gte]: sevenDaysAgo },
      },
      group: [fn('DATE', col('createdAt'))],
      order: [[fn('DATE', col('createdAt')), 'ASC']],
      raw: true,
    });

    const recentMessages = await Message.findAll({
      where: companyWhere,
      order: [['createdAt', 'DESC']],
      limit: 10,
    });

    res.json({
      success: true,
      stats: {
        total,
        sent,
        delivered,
        read,
        failed,
        pending,
        today: todayCount,
        dailyMessages,
        recentMessages,
      },
    });
  } catch (error) {
    logger.error('Error fetching company stats:', error);
    res.status(500).json({ success: false, error: error.message });
  }
});

module.exports = router;
