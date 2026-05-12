const { pool } = require('../config/database');
const { paginate } = require('../utils/helpers');
const logger = require('../utils/logger');

async function dashboard(req, res) {
  try {
    const userId = req.user.id;

    const [[user]] = await pool.query('SELECT balance FROM users WHERE id = ?', [userId]);
    const [[{ totalOrders }]] = await pool.query(
      'SELECT COUNT(*) as totalOrders FROM otp_orders WHERE user_id = ?', [userId]
    );
    const [[{ totalDeposits }]] = await pool.query(
      'SELECT COALESCE(SUM(amount), 0) as totalDeposits FROM deposits WHERE user_id = ? AND status = ?',
      [userId, 'paid']
    );
    const [[{ activeOrders }]] = await pool.query(
      "SELECT COUNT(*) as activeOrders FROM otp_orders WHERE user_id = ? AND status IN ('pending','waiting')",
      [userId]
    );

    const [recentOrders] = await pool.query(
      `SELECT o.*, s.name as service_name, c.name as country_name
       FROM otp_orders o
       LEFT JOIN otp_services s ON s.id = o.service_id
       LEFT JOIN countries c ON c.id = o.country_id
       WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5`,
      [userId]
    );

    const [recentTransactions] = await pool.query(
      'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5',
      [userId]
    );

    res.json({
      success: true,
      data: {
        balance: user.balance,
        totalOrders,
        totalDeposits,
        activeOrders,
        recentOrders,
        recentTransactions,
      },
    });
  } catch (err) {
    logger.error({ err }, 'Dashboard error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function transactions(req, res) {
  try {
    const userId = req.user.id;
    const { offset, limit, page } = paginate(req.query.page, req.query.limit);
    const type = req.query.type;

    let where = 'user_id = ?';
    const params = [userId];
    if (type) { where += ' AND type = ?'; params.push(type); }

    const [rows] = await pool.query(
      `SELECT * FROM transactions WHERE ${where} ORDER BY created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await pool.query(`SELECT COUNT(*) as total FROM transactions WHERE ${where}`, params);

    res.json({ success: true, data: { transactions: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Transactions error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function orders(req, res) {
  try {
    const userId = req.user.id;
    const { offset, limit, page } = paginate(req.query.page, req.query.limit);
    const status = req.query.status;

    let where = 'o.user_id = ?';
    const params = [userId];
    if (status) { where += ' AND o.status = ?'; params.push(status); }

    const [rows] = await pool.query(
      `SELECT o.*, s.name as service_name, c.name as country_name,
              p.name as provider_name, op.name as operator_name
       FROM otp_orders o
       LEFT JOIN otp_services s ON s.id = o.service_id
       LEFT JOIN countries c ON c.id = o.country_id
       LEFT JOIN otp_providers p ON p.id = o.provider_id
       LEFT JOIN operators op ON op.id = o.operator_id
       WHERE ${where}
       ORDER BY o.created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await pool.query(
      `SELECT COUNT(*) as total FROM otp_orders o WHERE ${where}`, params
    );

    res.json({ success: true, data: { orders: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Orders error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function profile(req, res) {
  try {
    const userId = req.user.id;
    const [[user]] = await pool.query(
      'SELECT id, username, email, phone, balance, referral_code, api_key, role, language, created_at FROM users WHERE id = ?',
      [userId]
    );
    const [[affiliate]] = await pool.query(
      'SELECT * FROM affiliates WHERE user_id = ?',
      [userId]
    );

    res.json({ success: true, data: { user, affiliate } });
  } catch (err) {
    logger.error({ err }, 'Profile error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function updateProfile(req, res) {
  try {
    const userId = req.user.id;
    const { phone, language } = req.body;
    const updates = [];
    const params = [];

    if (phone !== undefined) { updates.push('phone = ?'); params.push(phone); }
    if (language && ['id', 'en'].includes(language)) { updates.push('language = ?'); params.push(language); }

    if (updates.length) {
      params.push(userId);
      await pool.query(`UPDATE users SET ${updates.join(', ')} WHERE id = ?`, params);
    }

    res.json({ success: true, message: 'Profile updated' });
  } catch (err) {
    logger.error({ err }, 'Update profile error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

module.exports = { dashboard, transactions, orders, profile, updateProfile };
