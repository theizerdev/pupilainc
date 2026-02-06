const { DataTypes } = require('sequelize');
const { sequelize } = require('../config/database');
const { QueryTypes } = require('sequelize');

const Session = sequelize.define('Session', {
  id: {
    type: DataTypes.STRING,
    primaryKey: true
  },
  status: {
    type: DataTypes.ENUM('disconnected', 'connecting', 'connected', 'qr_ready'),
    defaultValue: 'disconnected'
  },
  qrCode: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  lastSeen: {
    type: DataTypes.DATE,
    allowNull: true
  },
  phoneNumber: {
    type: DataTypes.STRING,
    allowNull: true
  },
  deviceName: {
    type: DataTypes.STRING,
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
  tableName: 'whatsapp_sessions',
  timestamps: true,
  // Desactivar la creación automática de foreign keys para evitar conflictos
  hooks: {
    beforeSync: async (options) => {
      // Verificar si la tabla existe y tiene la estructura correcta
      const [results] = await sequelize.query(
        "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'whatsapp_sessions'",
        { type: QueryTypes.SELECT }
      );
      
      if (results.count > 0) {
        // La tabla existe, verificar si necesita arreglos
        const columns = await sequelize.query(
          "SHOW COLUMNS FROM whatsapp_sessions",
          { type: QueryTypes.SELECT }
        );
        
        const companyIdColumn = columns.find(col => col.Field === 'companyId');
        if (companyIdColumn && companyIdColumn.Type !== 'int(11)') {
          console.log('⚠️  La columna companyId en whatsapp_sessions necesita ser arreglada');
          
          // Eliminar foreign keys existentes
          const foreignKeys = await sequelize.query(
            "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'whatsapp_sessions' AND COLUMN_NAME = 'companyId' AND TABLE_SCHEMA = DATABASE()",
            { type: QueryTypes.SELECT }
          );
          
          for (const fk of foreignKeys) {
            await sequelize.query(`ALTER TABLE whatsapp_sessions DROP FOREIGN KEY ${fk.CONSTRAINT_NAME}`);
          }
          
          // Modificar columna
          await sequelize.query('ALTER TABLE whatsapp_sessions MODIFY COLUMN companyId INT NOT NULL');
          
          // Recrear foreign key
          await sequelize.query('ALTER TABLE whatsapp_sessions ADD CONSTRAINT fk_whatsapp_sessions_companyId FOREIGN KEY (companyId) REFERENCES companies(id)');
        }
      }
    }
  }
});

module.exports = Session;