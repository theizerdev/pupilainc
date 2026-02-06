const { sequelize } = require('../src/config/database');
const logger = require('../src/utils/logger');

// Importar todos los modelos
const Company = require('../src/models/Company');
const Message = require('../src/models/Message');
const Session = require('../src/models/Session');

async function syncDatabase() {
  try {
    logger.info('🔄 Iniciando sincronización de base de datos...');
    
    // Verificar conexión
    await sequelize.authenticate();
    logger.info('✅ Conexión a base de datos establecida');
    
    // Sincronizar todos los modelos
    await sequelize.sync({ force: false, alter: true });
    logger.info('✅ Modelos sincronizados');
    
    // Verificar que las tablas existan
    const tables = await sequelize.query('SHOW TABLES', { type: 'SELECT' });
    logger.info('📊 Tablas en la base de datos:', tables.map(t => Object.values(t)[0]));
    
    // Verificar estructura de cada tabla
    const tableNames = ['companies', 'whatsapp_messages', 'whatsapp_sessions'];
    
    for (const tableName of tableNames) {
      try {
        const [columns] = await sequelize.query(`DESCRIBE ${tableName}`);
        logger.info(`📋 Estructura de ${tableName}:`, columns.map(col => `${col.Field} (${col.Type})`));
      } catch (error) {
        logger.warn(`⚠️  No se pudo describir la tabla ${tableName}:`, error.message);
      }
    }
    
    logger.info('✅ Sincronización completada');
    
  } catch (error) {
    logger.error('❌ Error en sincronización:', error);
    process.exit(1);
  } finally {
    await sequelize.close();
  }
}

// Ejecutar sincronización
syncDatabase();