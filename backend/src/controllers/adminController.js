const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { z } = require('zod');
const { pool } = require('../config/database');
const config = require('../config');
const { paginate } = require('../utils/helpers');
const activityService = require('../services/activityService');
const settingsService = require('./../../src/services/settingsService');
const { clearProviderCache } = require('../providers');
const logger = require('../utils/logger');

const loginSchema = z.object({
  username: z.string().min(1),
  password: z.string().min(1),
});

async function login(req, res) {
  try {
    const data = loginSchema.parse(req.body);
    const [admins] = await pool.query(
      'SELECT * FROM admins WHERE username = ? AND is_active = 1',
      [data.username]
    );
    if (!admins.length) {
      return res.status(401).json({ success: false, message: 'Username atau password salah' });
    }
    const admin = admins[0];
    const valid = await bcrypt.compare(data.password, admin.password);
    if (!valid) {
      return res.status(401).json({ success: false, message: 'Username atau password salah' });
    }
    await pool.query('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [admin.id]);
    await activityService.log({
      actorType: 'admin', actorId: admin.id, action: 'admin_login', ipAddress: req.ip,
    });
    const token = jwt.sign({ id: admin.id, role: admin.role }, config.jwt.adminSecret, {
      expiresIn: config.jwt.adminExpiresIn,
    });
    res.json({
      success: true,
      data: { token, admin: { id: admin.id, username: admin.username, role: admin.role } },
    });
  } catch (err) {
    if (err instanceof z.ZodError) return res.status(400).json({ success: false, errors: err.errors });
    logger.error({ err }, 'Admin login error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function dashboard(req, res) {
  try {
    const [[{ totalUsers }]] = await pool.query('SELECT COUNT(*) as totalUsers FROM users');
    const [[{ totalDeposits }]] = await pool.query("SELECT COALESCE(SUM(amount),0) as totalDeposits FROM deposits WHERE status='paid'");
    const [[{ totalOrders }]] = await pool.query('SELECT COUNT(*) as totalOrders FROM otp_orders');
    const [[{ revenue }]] = await pool.query("SELECT COALESCE(SUM(price),0) as revenue FROM otp_orders WHERE status='received'");
    const [[{ cost }]] = await pool.query("SELECT COALESCE(SUM(cost_price),0) as cost FROM otp_orders WHERE status='received'");
    const profit = parseFloat(revenue) - parseFloat(cost);
    const [recentOrders] = await pool.query(
      `SELECT o.*, u.username, s.name as service_name FROM otp_orders o
       LEFT JOIN users u ON u.id = o.user_id LEFT JOIN otp_services s ON s.id = o.service_id
       ORDER BY o.created_at DESC LIMIT 10`
    );
    const [recentDeposits] = await pool.query(
      `SELECT d.*, u.username FROM deposits d LEFT JOIN users u ON u.id = d.user_id
       ORDER BY d.created_at DESC LIMIT 10`
    );
    res.json({
      success: true,
      data: { totalUsers, totalDeposits: parseFloat(totalDeposits), totalOrders, profit, recentOrders, recentDeposits },
    });
  } catch (err) {
    logger.error({ err }, 'Admin dashboard error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getUsers(req, res) {
  try {
    const { offset, limit, page } = paginate(req.query.page, req.query.limit);
    const search = req.query.search;
    let where = '1=1';
    const params = [];
    if (search) {
      where += ' AND (username LIKE ? OR email LIKE ?)';
      params.push(`%${search}%`, `%${search}%`);
    }
    const [rows] = await pool.query(
      `SELECT id, username, email, phone, balance, role, is_banned, ban_reason, referral_code, created_at, last_login_at
       FROM users WHERE ${where} ORDER BY created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await pool.query(`SELECT COUNT(*) as total FROM users WHERE ${where}`, params);
    res.json({ success: true, data: { users: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Admin getUsers error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function banUser(req, res) {
  try {
    const { is_banned, ban_reason } = req.body;
    await pool.query('UPDATE users SET is_banned = ?, ban_reason = ? WHERE id = ?', [
      is_banned ? 1 : 0, ban_reason || null, req.params.id,
    ]);
    await activityService.log({
      actorType: 'admin', actorId: req.admin.id, action: is_banned ? 'ban_user' : 'unban_user',
      targetType: 'user', targetId: parseInt(req.params.id, 10), ipAddress: req.ip,
    });
    res.json({ success: true, message: is_banned ? 'User dibanned' : 'User di-unban' });
  } catch (err) {
    logger.error({ err }, 'Ban user error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function adjustBalance(req, res) {
  const conn = await pool.getConnection();
  try {
    const { amount, type, description } = req.body;
    const userId = parseInt(req.params.id, 10);
    if (!amount || !type || !['add', 'deduct'].includes(type)) {
      conn.release();
      return res.status(400).json({ success: false, message: 'Amount dan type (add/deduct) wajib diisi' });
    }
    await conn.beginTransaction();
    const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [userId]);
    if (!user) { await conn.rollback(); conn.release(); return res.status(404).json({ success: false, message: 'User not found' }); }
    const balanceBefore = parseFloat(user.balance);
    const adjustAmount = parseFloat(amount);
    const balanceAfter = type === 'add' ? balanceBefore + adjustAmount : balanceBefore - adjustAmount;
    if (balanceAfter < 0) { await conn.rollback(); conn.release(); return res.status(400).json({ success: false, message: 'Saldo tidak boleh minus' }); }
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, userId]);
    const txType = type === 'add' ? 'manual_add' : 'manual_deduct';
    const txAmount = type === 'add' ? adjustAmount : -adjustAmount;
    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [userId, txType, txAmount, balanceBefore, balanceAfter, description || `Manual ${type} by admin`]
    );
    await conn.commit();
    await activityService.log({
      actorType: 'admin', actorId: req.admin.id, action: `balance_${type}`,
      targetType: 'user', targetId: userId,
      details: { amount: adjustAmount, balanceBefore, balanceAfter }, ipAddress: req.ip,
    });
    res.json({ success: true, message: 'Saldo berhasil diubah', data: { balanceBefore, balanceAfter } });
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Adjust balance error');
    res.status(500).json({ success: false, message: 'Server error' });
  } finally {
    conn.release();
  }
}

async function getOrders(req, res) {
  try {
    const { offset, limit, page } = paginate(req.query.page, req.query.limit);
    const status = req.query.status;
    let where = '1=1';
    const params = [];
    if (status) { where += ' AND o.status = ?'; params.push(status); }
    const [rows] = await pool.query(
      `SELECT o.*, u.username, s.name as service_name, c.name as country_name,
              p.name as provider_name
       FROM otp_orders o
       LEFT JOIN users u ON u.id = o.user_id
       LEFT JOIN otp_services s ON s.id = o.service_id
       LEFT JOIN countries c ON c.id = o.country_id
       LEFT JOIN otp_providers p ON p.id = o.provider_id
       WHERE ${where} ORDER BY o.created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await pool.query(`SELECT COUNT(*) as total FROM otp_orders o WHERE ${where}`, params);
    res.json({ success: true, data: { orders: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Admin getOrders error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getDeposits(req, res) {
  try {
    const { offset, limit, page } = paginate(req.query.page, req.query.limit);
    const status = req.query.status;
    let where = '1=1';
    const params = [];
    if (status) { where += ' AND d.status = ?'; params.push(status); }
    const [rows] = await pool.query(
      `SELECT d.*, u.username FROM deposits d LEFT JOIN users u ON u.id = d.user_id
       WHERE ${where} ORDER BY d.created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await pool.query(`SELECT COUNT(*) as total FROM deposits d WHERE ${where}`, params);
    res.json({ success: true, data: { deposits: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Admin getDeposits error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function refund(req, res) {
  const conn = await pool.getConnection();
  try {
    const { order_id, reason } = req.body;
    if (!order_id) return res.status(400).json({ success: false, message: 'order_id wajib' });
    await conn.beginTransaction();
    const [[order]] = await conn.query('SELECT * FROM otp_orders WHERE id = ? AND refunded = 0 FOR UPDATE', [order_id]);
    if (!order) { await conn.rollback(); conn.release(); return res.status(404).json({ success: false, message: 'Order tidak ditemukan atau sudah di-refund' }); }
    const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [order.user_id]);
    const balanceBefore = parseFloat(user.balance);
    const balanceAfter = balanceBefore + parseFloat(order.price);
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, order.user_id]);
    await conn.query("UPDATE otp_orders SET status = 'refunded', refunded = 1 WHERE id = ?", [order.id]);
    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'refund', ?, ?, ?, 'otp_order', ?, ?)`,
      [order.user_id, order.price, balanceBefore, balanceAfter, order.id, reason || 'Manual refund by admin']
    );
    await conn.query("INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'refunded', ?)", [
      order.id, JSON.stringify({ admin_id: req.admin.id, reason }),
    ]);
    await conn.commit();
    await activityService.log({
      actorType: 'admin', actorId: req.admin.id, action: 'manual_refund',
      targetType: 'otp_order', targetId: order.id, ipAddress: req.ip,
    });
    res.json({ success: true, message: 'Refund berhasil' });
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Refund error');
    res.status(500).json({ success: false, message: 'Server error' });
  } finally {
    conn.release();
  }
}

async function getServicesList(req, res) {
  try {
    const [rows] = await pool.query(
      `SELECT p.*, s.name as service_name, c.name as country_name,
              o.name as operator_name, pr.name as provider_name
       FROM otp_pricing p
       JOIN otp_services s ON s.id = p.service_id
       JOIN countries c ON c.id = p.country_id
       JOIN otp_providers pr ON pr.id = p.provider_id
       LEFT JOIN operators o ON o.id = p.operator_id
       ORDER BY c.name, s.name`
    );
    res.json({ success: true, data: rows });
  } catch (err) {
    logger.error({ err }, 'Get services list error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function createService(req, res) {
  try {
    const { country_id, service_id, operator_id, provider_id, provider_service_code, provider_country_code, provider_operator_code, cost_price, markup_percent, is_active } = req.body;
    const sellPrice = Math.ceil(cost_price * (1 + markup_percent / 100));
    const [result] = await pool.query(
      `INSERT INTO otp_pricing (country_id, service_id, operator_id, provider_id, provider_service_code, provider_country_code, provider_operator_code, cost_price, markup_percent, sell_price, is_active)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [country_id, service_id, operator_id || null, provider_id, provider_service_code, provider_country_code, provider_operator_code || null, cost_price, markup_percent, sellPrice, is_active !== undefined ? is_active : 1]
    );
    res.status(201).json({ success: true, data: { id: result.insertId } });
  } catch (err) {
    logger.error({ err }, 'Create service error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function updateService(req, res) {
  try {
    const id = req.params.id;
    const { cost_price, markup_percent, is_active, provider_service_code, provider_country_code, provider_operator_code } = req.body;
    const [[old]] = await pool.query('SELECT * FROM otp_pricing WHERE id = ?', [id]);
    if (!old) return res.status(404).json({ success: false, message: 'Not found' });

    const newCost = cost_price !== undefined ? cost_price : old.cost_price;
    const newMarkup = markup_percent !== undefined ? markup_percent : old.markup_percent;
    const newSell = Math.ceil(newCost * (1 + newMarkup / 100));

    await pool.query(
      `UPDATE otp_pricing SET cost_price=?, markup_percent=?, sell_price=?, is_active=?,
       provider_service_code=?, provider_country_code=?, provider_operator_code=? WHERE id=?`,
      [newCost, newMarkup, newSell, is_active !== undefined ? is_active : old.is_active,
       provider_service_code || old.provider_service_code, provider_country_code || old.provider_country_code,
       provider_operator_code || old.provider_operator_code, id]
    );

    if (newCost !== parseFloat(old.cost_price) || newSell !== parseFloat(old.sell_price)) {
      await pool.query(
        `INSERT INTO pricing_history (pricing_id, old_cost_price, new_cost_price, old_sell_price, new_sell_price, old_markup_percent, new_markup_percent, changed_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'admin')`,
        [id, old.cost_price, newCost, old.sell_price, newSell, old.markup_percent, newMarkup]
      );
    }
    res.json({ success: true, message: 'Service updated' });
  } catch (err) {
    logger.error({ err }, 'Update service error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function deleteService(req, res) {
  try {
    await pool.query('DELETE FROM otp_pricing WHERE id = ?', [req.params.id]);
    res.json({ success: true, message: 'Service deleted' });
  } catch (err) {
    logger.error({ err }, 'Delete service error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getSettings(req, res) {
  try {
    const settings = await settingsService.getAll();
    res.json({ success: true, data: settings });
  } catch (err) {
    logger.error({ err }, 'Get settings error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function updateSettings(req, res) {
  try {
    await settingsService.setMany(req.body);
    clearProviderCache();
    await activityService.log({
      actorType: 'admin', actorId: req.admin.id, action: 'update_settings',
      details: { keys: Object.keys(req.body) }, ipAddress: req.ip,
    });
    res.json({ success: true, message: 'Settings updated' });
  } catch (err) {
    logger.error({ err }, 'Update settings error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getActivityLogs(req, res) {
  try {
    const result = await activityService.getList({
      page: req.query.page,
      limit: req.query.limit,
      actorType: req.query.actor_type,
      action: req.query.action,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    logger.error({ err }, 'Get activity logs error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

module.exports = {
  login, dashboard, getUsers, banUser, adjustBalance, getOrders, getDeposits,
  refund, getServicesList, createService, updateService, deleteService,
  getSettings, updateSettings, getActivityLogs,
};
