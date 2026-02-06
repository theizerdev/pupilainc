const { sequelize } = require('../src/config/database');
const { QueryTypes } = require('sequelize');

async function fixDatabaseV2() {
  try {
    console.log('🔄 Iniciando arreglo de base de datos v2...');
    
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
    
    // Verificar si existe la tabla whatsapp_messages
    const [tables] = await sequelize.query(
      "SHOW TABLES LIKE 'whatsapp_messages'",
      { type: QueryTypes.SELECT }
    );
    
    if (tables) {
      console.log('📋 Tabla whatsapp_messages encontrada, analizando estructura...');
      
      // Obtener información de las columnas
      const columns = await sequelize.query(
        "SHOW COLUMNS FROM whatsapp_messages",
        { type: QueryTypes.SELECT }
      );
      
      console.log('📊 Columnas actuales:', columns.map(col => `${col.Field} (${col.Type})`));
      
      // Verificar si existe la columna companyId
      const companyIdColumn = columns.find(col => col.Field === 'companyId');
      
      if (companyIdColumn) {
        console.log('⚠️  Columna companyId existe, eliminando...');
        
        // Obtener foreign keys existentes
        const foreignKeys = await sequelize.query(
          "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'whatsapp_messages' AND COLUMN_NAME = 'companyId' AND TABLE_SCHEMA = DATABASE()",
          { type: QueryTypes.SELECT }
        );
        
        // Eliminar foreign keys existentes
        for (const fk of foreignKeys) {
          console.log(`🗑️  Eliminando foreign key: ${fk.CONSTRAINT_NAME}`);
          await sequelize.query(`ALTER TABLE whatsapp_messages DROP FOREIGN KEY ${fk.CONSTRAINT_NAME}`);
        }
        
        // Eliminar columna companyId existente
        console.log('🗑️  Eliminando columna companyId existente...');
        await sequelize.query('ALTER TABLE whatsapp_messages DROP COLUMN companyId');
      }
      
      // Determinar el tipo de dato correcto basado en companies.id
      const companyIdType = companyColumns.Type;
      
      // Agregar columna companyId con el tipo correcto
      console.log(`➕ Agregando columna companyId con tipo ${companyIdType}...`);
      await sequelize.query(`ALTER TABLE whatsapp_messages ADD COLUMN companyId ${companyIdType} NOT NULL`);
      
      // Crear foreign key
      console.log('🔑 Creando foreign key...');
      await sequelize.query(`
        ALTER TABLE whatsapp_messages 
        ADD CONSTRAINT fk_whatsapp_messages_companyId 
        FOREIGN KEY (companyId) REFERENCES companies(id)
      `);
      
      console.log('✅ Foreign key creada exitosamente');
      
    } else {
      console.log('📋 Tabla whatsapp_messages no existe, se creará automáticamente');
    }
    
    console.log('🎉 Arreglo de base de datos completado');
    
  } catch (error) {
    console.error('❌ Error arreglando base de datos:', error);
    console.error('SQL:', error.sql);
    process.exit(1);
  } finally {
    await sequelize.close();
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  fixDatabaseV2();
}

module.exports = { fixDatabaseV2 };