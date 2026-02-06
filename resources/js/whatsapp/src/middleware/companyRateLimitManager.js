const rateLimit = require('express-rate-limit');

// Cache para almacenar los limitadores por empresa
const companyLimiters = new Map();

/**
 * Crea un limitador para una empresa específica
 * @param {Object} company - Objeto empresa con id y rateLimitPerMinute
 * @returns {Function} Middleware de rate limit
 */
function createCompanyLimiter(company) {
  if (!company || !company.id) {
    return null;
  }

  const key = `company_${company.id}`;
  
  // Crear limitador con configuración fija
  const limiter = rateLimit({
    windowMs: 60 * 1000, // 1 minuto
    max: company.rateLimitPerMinute || 60, // Valor por defecto si no está definido
    keyGenerator: (req) => key,
    message: {
      success: false,
      error: `Límite de ${company.rateLimitPerMinute || 60} mensajes por minuto excedido`
    },
    standardHeaders: true,
    legacyHeaders: false,
    validate: false
  });

  return limiter;
}

/**
 * Obtiene o crea un limitador para una empresa específica
 * @param {Object} company - Objeto empresa con id y rateLimitPerMinute
 * @returns {Function} Middleware de rate limit
 */
function getCompanyLimiter(company) {
  if (!company || !company.id) {
    return null;
  }

  const key = `company_${company.id}`;
  
  // Si ya existe un limitador para esta empresa, devolverlo
  if (companyLimiters.has(key)) {
    return companyLimiters.get(key);
  }

  // Crear nuevo limitador
  const limiter = createCompanyLimiter(company);
  
  if (!limiter) {
    return null;
  }

  // Almacenar en cache
  companyLimiters.set(key, limiter);
  
  return limiter;
}

/**
 * Limpia los limitadores antiguos que no se han usado recientemente
 */
function cleanupOldLimiters() {
  const now = Date.now();
  const maxAge = 5 * 60 * 1000; // 5 minutos

  for (const [key, limiter] of companyLimiters.entries()) {
    // Si el limitador no tiene requests recientes, eliminarlo
    if (limiter.resetTime && (now - limiter.resetTime) > maxAge) {
      companyLimiters.delete(key);
    }
  }
}

/**
 * Middleware principal que aplica el rate limit por empresa
 */
function rateLimitByCompany(req, res, next) {
  const company = req.company;
  
  if (!company) {
    return next();
  }

  const limiter = getCompanyLimiter(company);
  
  if (!limiter) {
    return next();
  }

  return limiter(req, res, next);
}

/**
 * Función para actualizar el límite de una empresa
 */
function updateCompanyLimit(companyId, newLimit) {
  const key = `company_${companyId}`;
  
  if (companyLimiters.has(key)) {
    companyLimiters.delete(key);
  }
}

/**
 * Función para limpiar todos los limitadores (útil para testing)
 */
function clearAllLimiters() {
  companyLimiters.clear();
}

module.exports = {
  rateLimitByCompany,
  getCompanyLimiter,
  updateCompanyLimit,
  clearAllLimiters
};