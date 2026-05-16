const mysql = require('mysql2/promise');
const logger = require('../utils/logger');

const pool = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  port: parseInt(process.env.DB_PORT || '3306', 10),
  user: process.env.DB_USER || 'hanssotp',
  password: process.env.DB_PASS || '',
  database: process.env.DB_NAME || 'hanssotp',
  waitForConnections: true,
  connectionLimit: 20,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelay: 10000,
  charset: 'utf8mb4',
});

async function testConnection() {
  try {
    const conn = await pool.getConnection();
    logger.info('Database connected successfully');
    conn.release();
  } catch (err) {
    logger.error({ err }, 'Database connection failed');
    process.exit(1);
  }
}

module.exports = { pool, testConnection };
