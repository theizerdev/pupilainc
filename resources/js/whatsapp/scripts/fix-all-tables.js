const { sequelize } = require('../src/config/database');
const { QueryTypes } = require('sequelize');

async function fixAllTables() {
  try {
    console.log('🔄 Iniciando arreglo de todas las tablas...');
    
    // Obtener información de las columnas de companies
    const [companyColumns] = await sequelize.query(
      "SHOW COLUMNS FROM companies WHERE Field = 'id'",
      { type: QueryTypes.SELECT }
    );
    
    console.log('📊 Tipo de dato de companies.id:', companyColumns?.Type || 'No encontrado');
    
    if (!companyColumns) {
      console.log('❌ No se encontró la tabla companies');
      return;
    }
    
    const companyIdType = companyColumns.Type;
    
    // 1. Arreglar tabla whatsapp_messages
    console.log('\n📋 Arreglando tabla whatsapp_messages...');
    await fixTable('whatsapp_messages', companyIdType);
    
    // 2. Arreglar tabla whatsapp_sessions
    console.log('\n📋 Arreglando tabla whatsapp_sessions...');
    await fixTable('whatsapp_sessions', companyIdType);
    
    console.log('\n🎉 Arreglo de todas las tablas completado');
    
  } catch (error) {
    console.error('❌ Error arreglando tablas:', error);
    process.exit(1);
  } finally {
    await sequelize.close();
  }
}

async function fixTable(tableName, companyIdType) {
  try {
    // Verificar si existe la tabla
    const [tables] = await sequelize.query(
      `SHOW TABLES LIKE '${tableName}'`,
      { type: QueryTypes.SELECT }
    );
    
    if (!tables) {
      console.log(`📋 Tabla ${tableName} no existe, se creará automáticamente`);
      return;
    }
    
    // Obtener información de las columnas
    const columns = await sequelize.query(
      `SHOW COLUMNS FROM ${tableName}`,
      { type: QueryTypes.SELECT }
    );
    
    console.log(`📊 Columnas de ${tableName}:`, columns.map(col => `${col.Field} (${col.Type})`));
    
    // Verificar si existe la columna companyId
    const companyIdColumn = columns.find(col => col.Field === 'companyId');
    
    if (companyIdColumn) {
      console.log(`⚠️  Columna companyId existe en ${tableName}, verificando tipo...`);
      
      // Si el tipo ya es correcto, no hacer nada
      if (companyIdColumn.Type === companyIdType) {
        console.log(`✅ Tipo de companyId ya es correcto en ${tableName}`);
        return;
      }
      
      // Obtener foreign keys existentes
      const foreignKeys = await sequelize.query(
        `SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '${tableName}' AND COLUMN_NAME = 'companyId' AND TABLE_SCHEMA = DATABASE()`,
        { type: QueryTypes.SELECT }
      );
      
      // Eliminar foreign keys existentes
      for (const fk of foreignKeys) {
        console.log(`🗑️  Eliminando foreign key: ${fk.CONSTRAINT_NAME}`);
        await sequelize.query(`ALTER TABLE ${tableName} DROP FOREIGN KEY ${fk.CONSTRAINT_NAME}`);
      }
      
      // Eliminar columna companyId existente
      console.log(`🗑️  Eliminando columna companyId de ${tableName}...`);
      await sequelize.query(`ALTER TABLE ${tableName} DROP COLUMN companyId`);
    }
    
    // Agregar columna companyId con el tipo correcto
    console.log(`➕ Agregando columna companyId (${companyIdType}) a ${tableName}...`);
    await sequelize.query(`ALTER TABLE ${tableName} ADD COLUMN companyId ${companyIdType} NOT NULL`);
    
    // Crear foreign key
    console.log(`🔑 Creando foreign key para ${tableName}...`);
    await sequelize.query(`
      ALTER TABLE ${tableName} 
      ADD CONSTRAINT fk_${tableName}_companyId 
      FOREIGN KEY (companyId) REFERENCES companies(id)
    `);
    
    console.log(`✅ Tabla ${tableName} arreglada exitosamente`);
    
  } catch (error) {
    console.error(`❌ Error arreglando tabla ${tableName}:`, error);
    throw error;
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  fixAllTables();
}

module.exports = { fixAllTables };