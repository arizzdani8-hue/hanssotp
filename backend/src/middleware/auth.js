const jwt = require('jsonwebtoken');
const config = require('../config');
const { pool } = require('../config/database');

async function userAuth(req, res, next) {
  try {
    const header = req.headers.authorization;
    if (!header || !header.startsWith('Bearer ')) {
      return res.status(401).json({ success: false, message: 'Token tidak ditemukan' });
    }
    const token = header.split(' ')[1];
    const decoded = jwt.verify(token, config.jwt.secret);
    const [rows] = await pool.query('SELECT id, username, email, balance, role, is_banned, language, referral_code FROM users WHERE id = ?', [decoded.id]);
    if (!rows.length) {
      return res.status(401).json({ success: false, message: 'User tidak ditemukan' });
    }
    if (rows[0].is_banned) {
      return res.status(403).json({ success: false, message: 'Akun Anda telah dibanned' });
    }
    req.user = rows[0];
    next();
  } catch (err) {
    if (err.name === 'TokenExpiredError') {
      return res.status(401).json({ success: false, message: 'Token expired' });
    }
    return res.status(401).json({ success: false, message: 'Token tidak valid' });
  }
}

async function adminAuth(req, res, next) {
  try {
    const header = req.headers.authorization;
    if (!header || !header.startsWith('Bearer ')) {
      return res.status(401).json({ success: false, message: 'Token tidak ditemukan' });
    }
    const token = header.split(' ')[1];
    const decoded = jwt.verify(token, config.jwt.adminSecret);
    const [rows] = await pool.query('SELECT id, username, email, role FROM admins WHERE id = ? AND is_active = 1', [decoded.id]);
    if (!rows.length) {
      return res.status(401).json({ success: false, message: 'Admin tidak ditemukan' });
    }
    req.admin = rows[0];
    next();
  } catch (err) {
    if (err.name === 'TokenExpiredError') {
      return res.status(401).json({ success: false, message: 'Token expired' });
    }
    return res.status(401).json({ success: false, message: 'Token tidak valid' });
  }
}

async function resellerAuth(req, res, next) {
  try {
    const apiKey = req.headers['x-api-key'];
    if (!apiKey) {
      return res.status(401).json({ success: false, message: 'API key tidak ditemukan' });
    }
    const [rows] = await pool.query(
      `SELECT rak.id as api_key_id, rak.user_id, rak.rate_limit, u.username, u.balance, u.is_banned
       FROM reseller_api_keys rak
       JOIN users u ON u.id = rak.user_id
       WHERE rak.api_key = ? AND rak.is_active = 1`,
      [apiKey]
    );
    if (!rows.length) {
      return res.status(401).json({ success: false, message: 'API key tidak valid' });
    }
    if (rows[0].is_banned) {
      return res.status(403).json({ success: false, message: 'Akun telah dibanned' });
    }
    await pool.query('UPDATE reseller_api_keys SET last_used_at = NOW() WHERE id = ?', [rows[0].api_key_id]);
    req.reseller = rows[0];
    next();
  } catch (err) {
    return res.status(500).json({ success: false, message: 'Server error' });
  }
}

module.exports = { userAuth, adminAuth, resellerAuth };
