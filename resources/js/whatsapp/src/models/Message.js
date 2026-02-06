const { DataTypes } = require('sequelize');
const { sequelize } = require('../config/database');
const { QueryTypes } = require('sequelize');

const Message = sequelize.define('Message', {
  id: {
    type: DataTypes.INTEGER, // Cambiado a INTEGER para coincidir con companies.id
    primaryKey: true,
    autoIncrement: true
  },
  messageId: {
    type: DataTypes.STRING,
    allowNull: false,
    unique: true
  },
  from: {
    type: DataTypes.STRING,
    allowNull: false
  },
  to: {
    type: DataTypes.STRING,
    allowNull: false
  },
  message: {
    type: DataTypes.TEXT,
    allowNull: false
  },
  type: {
    type: DataTypes.ENUM('text', 'image', 'document', 'audio', 'video'),
    defaultValue: 'text'
  },
  status: {
    type: DataTypes.ENUM('pending', 'sent', 'delivered', 'read', 'failed'),
    defaultValue: 'pending'
  },
  mediaUrl: {
    type: DataTypes.STRING,
    allowNull: true
  },
  retryCount: {
    type: DataTypes.INTEGER,
    defaultValue: 0
  },
  errorMessage: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  companyId: {
    type: DataTypes.INTEGER, // Cambiado a INTEGER para coincidir con companies.id
    allowNull: false,
    references: {
      model: 'companies',
      key: 'id'
    }
  }
}, {
  tableName: 'whatsapp_messages',
  timestamps: true,
  // Desactivar la creación automática de foreign keys para evitar conflictos
  hooks: {
    beforeSync: async (options) => {
      // Verificar si la tabla existe y tiene la estructura correcta
      const [results] = await sequelize.query(
        "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'whatsapp_messages'",
        { type: QueryTypes.SELECT }
      );
      
      if (results.count > 0) {
        // La tabla existe, verificar si necesita arreglos
        const columns = await sequelize.query(
          "SHOW COLUMNS FROM whatsapp_messages",
          { type: QueryTypes.SELECT }
        );
        
        const companyIdColumn = columns.find(col => col.Field === 'companyId');
        if (companyIdColumn && companyIdColumn.Type !== 'int(11)') {
          console.log('⚠️  La columna companyId en whatsapp_messages necesita ser arreglada');
          
          // Eliminar foreign keys existentes
          const foreignKeys = await sequelize.query(
            "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'whatsapp_messages' AND COLUMN_NAME = 'companyId' AND TABLE_SCHEMA = DATABASE()",
            { type: QueryTypes.SELECT }
          );
          
          for (const fk of foreignKeys) {
            await sequelize.query(`ALTER TABLE whatsapp_messages DROP FOREIGN KEY ${fk.CONSTRAINT_NAME}`);
          }
          
          // Modificar columna
          await sequelize.query('ALTER TABLE whatsapp_messages MODIFY COLUMN companyId INT NOT NULL');
          
          // Recrear foreign key
          await sequelize.query('ALTER TABLE whatsapp_messages ADD CONSTRAINT fk_whatsapp_messages_companyId FOREIGN KEY (companyId) REFERENCES companies(id)');
        }
      }
    }
  }
});

module.exports = Message;