const { sequelize } = require('../src/config/database');
const { QueryTypes } = require('sequelize');

async function fixDatabase() {
  try {
    console.log('🔄 Iniciando arreglo de base de datos...');
    
    // Verificar si existe la tabla whatsapp_messages
    const [tables] = await sequelize.query(
      "SHOW TABLES LIKE 'whatsapp_messages'",
      { type: QueryTypes.SELECT }
    );
    
    if (tables) {
      console.log('📋 Tabla whatsapp_messages encontrada, verificando estructura...');
      
      // Obtener información de las columnas
      const columns = await sequelize.query(
        "SHOW COLUMNS FROM whatsapp_messages",
        { type: QueryTypes.SELECT }
      );
      
      console.log('📊 Columnas actuales:', columns.map(col => `${col.Field} (${col.Type})`));
      
      // Verificar si existe la columna companyId
      const companyIdColumn = columns.find(col => col.Field === 'companyId');
      
      if (companyIdColumn) {
        console.log('⚠️  Columna companyId existe, verificando foreign key...');
        
        // Obtener foreign keys
        const foreignKeys = await sequelize.query(
          "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'whatsapp_messages' AND COLUMN_NAME = 'companyId'",
          { type: QueryTypes.SELECT }
        );
        
        if (foreignKeys.length > 0) {
          console.log('🔑 Foreign keys encontradas:', foreignKeys.map(fk => fk.CONSTRAINT_NAME));
          
          // Eliminar foreign key existente
          for (const fk of foreignKeys) {
            console.log(`🗑️  Eliminando foreign key: ${fk.CONSTRAINT_NAME}`);
            await sequelize.query(`ALTER TABLE whatsapp_messages DROP FOREIGN KEY ${fk.CONSTRAINT_NAME}`);
          }
        }
        
        // Eliminar columna companyId existente
        console.log('🗑️  Eliminando columna companyId existente...');
        await sequelize.query('ALTER TABLE whatsapp_messages DROP COLUMN companyId');
      }
      
      // Agregar columna companyId correctamente
      console.log('➕ Agregando columna companyId...');
      await sequelize.query('ALTER TABLE whatsapp_messages ADD COLUMN companyId INT NOT NULL');
      
      // Crear foreign key
      console.log('🔑 Creando foreign key...');
      await sequelize.query(`
        ALTER TABLE whatsapp_messages 
        ADD CONSTRAINT fk_whatsapp_messages_companyId 
        FOREIGN KEY (companyId) REFERENCES companies(id)
      `);
      
    } else {
      console.log('📋 Tabla whatsapp_messages no existe, se creará automáticamente');
    }
    
    console.log('✅ Arreglo de base de datos completado');
    
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
  fixDatabase();
}

module.exports = { fixDatabase };