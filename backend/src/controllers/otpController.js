const { z } = require('zod');
const { pool } = require('../config/database');
const { getProvider } = require('../providers');
const activityService = require('../services/activityService');
const telegramService = require('../services/telegramService');
const logger = require('../utils/logger');

const orderSchema = z.object({
  country_id: z.number().int().positive(),
  service_id: z.number().int().positive(),
  operator_id: z.number().int().positive().optional(),
  provider_id: z.number().int().positive().optional(),
});

async function getCountries(req, res) {
  try {
    const [rows] = await pool.query(
      'SELECT * FROM countries WHERE is_active = 1 ORDER BY sort_order, name'
    );
    res.json({ success: true, data: rows });
  } catch (err) {
    logger.error({ err }, 'Get countries error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getServices(req, res) {
  try {
    const countryId = req.query.country_id;
    let rows;
    if (countryId) {
      [rows] = await pool.query(
        `SELECT DISTINCT s.* FROM otp_services s
         JOIN otp_pricing p ON p.service_id = s.id
         WHERE p.country_id = ? AND p.is_active = 1 AND s.is_active = 1
         ORDER BY s.sort_order, s.name`,
        [countryId]
      );
    } else {
      [rows] = await pool.query(
        'SELECT * FROM otp_services WHERE is_active = 1 ORDER BY sort_order, name'
      );
    }
    res.json({ success: true, data: rows });
  } catch (err) {
    logger.error({ err }, 'Get services error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getOperators(req, res) {
  try {
    const countryId = req.query.country_id;
    const serviceId = req.query.service_id;
    let where = 'o.is_active = 1';
    const params = [];

    if (countryId) { where += ' AND o.country_id = ?'; params.push(countryId); }

    let query;
    if (serviceId) {
      query = `SELECT DISTINCT o.* FROM operators o
               JOIN otp_pricing p ON p.operator_id = o.id
               WHERE ${where} AND p.service_id = ? AND p.is_active = 1
               ORDER BY o.name`;
      params.push(serviceId);
    } else {
      query = `SELECT * FROM operators o WHERE ${where} ORDER BY o.name`;
    }

    const [rows] = await pool.query(query, params);
    res.json({ success: true, data: rows });
  } catch (err) {
    logger.error({ err }, 'Get operators error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getPricing(req, res) {
  try {
    const { country_id, service_id, operator_id } = req.query;
    let where = 'p.is_active = 1';
    const params = [];

    if (country_id) { where += ' AND p.country_id = ?'; params.push(country_id); }
    if (service_id) { where += ' AND p.service_id = ?'; params.push(service_id); }
    if (operator_id) { where += ' AND p.operator_id = ?'; params.push(operator_id); }

    const [rows] = await pool.query(
      `SELECT p.*, s.name as service_name, c.name as country_name,
              o.name as operator_name, pr.name as provider_name, pr.slug as provider_slug
       FROM otp_pricing p
       JOIN otp_services s ON s.id = p.service_id
       JOIN countries c ON c.id = p.country_id
       JOIN otp_providers pr ON pr.id = p.provider_id
       LEFT JOIN operators o ON o.id = p.operator_id
       WHERE ${where}
       ORDER BY p.sell_price ASC`,
      params
    );
    res.json({ success: true, data: rows });
  } catch (err) {
    logger.error({ err }, 'Get pricing error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function createOrder(req, res) {
  const conn = await pool.getConnection();
  try {
    const data = orderSchema.parse(req.body);
    const userId = req.user.id;

    const [[{ activeCount }]] = await conn.query(
      "SELECT COUNT(*) as activeCount FROM otp_orders WHERE user_id = ? AND status IN ('pending','waiting')",
      [userId]
    );
    const [[user]] = await conn.query('SELECT * FROM users WHERE id = ? FOR UPDATE', [userId]);

    if (activeCount >= user.max_active_orders) {
      conn.release();
      return res.status(429).json({ success: false, message: 'Batas order aktif tercapai' });
    }

    const [[{ recentCount }]] = await conn.query(
      "SELECT COUNT(*) as recentCount FROM otp_orders WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
      [userId]
    );
    if (recentCount >= user.max_orders_per_minute) {
      conn.release();
      return res.status(429).json({ success: false, message: 'Terlalu banyak order per menit' });
    }

    let where = 'p.country_id = ? AND p.service_id = ? AND p.is_active = 1';
    const params = [data.country_id, data.service_id];

    if (data.operator_id) { where += ' AND p.operator_id = ?'; params.push(data.operator_id); }
    if (data.provider_id) { where += ' AND p.provider_id = ?'; params.push(data.provider_id); }

    const [pricings] = await conn.query(
      `SELECT p.*, pr.slug as provider_slug FROM otp_pricing p
       JOIN otp_providers pr ON pr.id = p.provider_id AND pr.is_active = 1
       WHERE ${where}
       ORDER BY pr.priority ASC, p.sell_price ASC LIMIT 1`,
      params
    );

    if (!pricings.length) {
      conn.release();
      return res.status(404).json({ success: false, message: 'Layanan tidak tersedia' });
    }

    const pricing = pricings[0];
    const sellPrice = parseFloat(pricing.sell_price);
    const userBalance = parseFloat(user.balance);

    if (userBalance < sellPrice) {
      conn.release();
      return res.status(400).json({ success: false, message: 'Saldo tidak mencukupi' });
    }

    const provider = await getProvider(pricing.provider_slug);

    const providerResult = await provider.createOrder(
      pricing.provider_country_code || 'indonesia',
      pricing.provider_service_code || 'any',
      pricing.provider_operator_code || 'any'
    );

    await conn.beginTransaction();

    const balanceBefore = userBalance;
    const balanceAfter = userBalance - sellPrice;
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, userId]);

    const expiresAt = new Date(Date.now() + 15 * 60 * 1000);

    const [orderResult] = await conn.query(
      `INSERT INTO otp_orders (user_id, pricing_id, provider_id, provider_order_id, country_id, service_id, operator_id, phone_number, status, price, cost_price, source, expires_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'waiting', ?, ?, 'web', ?)`,
      [
        userId, pricing.id, pricing.provider_id, providerResult.orderId,
        data.country_id, data.service_id, data.operator_id || null,
        providerResult.phoneNumber, sellPrice, pricing.cost_price, expiresAt,
      ]
    );

    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'order', ?, ?, ?, 'otp_order', ?, 'Order OTP')`,
      [userId, -sellPrice, balanceBefore, balanceAfter, orderResult.insertId]
    );

    await conn.query(
      `INSERT INTO otp_order_logs (order_id, action, details)
       VALUES (?, 'created', ?)`,
      [orderResult.insertId, JSON.stringify({ provider: pricing.provider_slug, providerOrderId: providerResult.orderId })]
    );

    await conn.commit();

    await activityService.log({
      actorType: 'user',
      actorId: userId,
      action: 'create_otp_order',
      targetType: 'otp_order',
      targetId: orderResult.insertId,
      ipAddress: req.ip,
    });

    res.status(201).json({
      success: true,
      data: {
        id: orderResult.insertId,
        phone_number: providerResult.phoneNumber,
        status: 'waiting',
        price: sellPrice,
        expires_at: expiresAt,
        provider_order_id: providerResult.orderId,
      },
    });
  } catch (err) {
    await conn.rollback();
    if (err instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: err.errors });
    }
    logger.error({ err }, 'Create OTP order error');
    res.status(500).json({ success: false, message: err.message || 'Gagal membuat order' });
  } finally {
    conn.release();
  }
}

async function getOrder(req, res) {
  try {
    const [[order]] = await pool.query(
      `SELECT o.*, s.name as service_name, c.name as country_name,
              p.name as provider_name, op.name as operator_name
       FROM otp_orders o
       LEFT JOIN otp_services s ON s.id = o.service_id
       LEFT JOIN countries c ON c.id = o.country_id
       LEFT JOIN otp_providers p ON p.id = o.provider_id
       LEFT JOIN operators op ON op.id = o.operator_id
       WHERE o.id = ? AND o.user_id = ?`,
      [req.params.id, req.user.id]
    );
    if (!order) {
      return res.status(404).json({ success: false, message: 'Order tidak ditemukan' });
    }

    const [logs] = await pool.query(
      'SELECT * FROM otp_order_logs WHERE order_id = ? ORDER BY created_at DESC',
      [order.id]
    );

    res.json({ success: true, data: { ...order, logs } });
  } catch (err) {
    logger.error({ err }, 'Get order error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function cancelOrder(req, res) {
  const conn = await pool.getConnection();
  try {
    const [orders] = await conn.query(
      "SELECT o.*, pr.slug as provider_slug FROM otp_orders o JOIN otp_providers pr ON pr.id = o.provider_id WHERE o.id = ? AND o.user_id = ? AND o.status IN ('pending','waiting')",
      [req.params.id, req.user.id]
    );
    if (!orders.length) {
      conn.release();
      return res.status(404).json({ success: false, message: 'Order tidak ditemukan atau tidak bisa dibatalkan' });
    }

    const order = orders[0];
    const provider = await getProvider(order.provider_slug);
    const result = await provider.cancelOrder(order.provider_order_id);

    await conn.beginTransaction();

    await conn.query(
      "UPDATE otp_orders SET status = 'cancelled', refunded = 1 WHERE id = ?",
      [order.id]
    );

    const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [order.user_id]);
    const balanceBefore = parseFloat(user.balance);
    const balanceAfter = balanceBefore + parseFloat(order.price);
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, order.user_id]);

    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'refund', ?, ?, ?, 'otp_order', ?, 'Refund order OTP dibatalkan')`,
      [order.user_id, order.price, balanceBefore, balanceAfter, order.id]
    );

    await conn.query(
      "INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'cancelled', ?)",
      [order.id, JSON.stringify({ providerResult: result })]
    );

    await conn.commit();

    res.json({ success: true, message: 'Order dibatalkan dan saldo dikembalikan' });
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Cancel order error');
    res.status(500).json({ success: false, message: 'Gagal membatalkan order' });
  } finally {
    conn.release();
  }
}

async function resendOtp(req, res) {
  try {
    const [[order]] = await pool.query(
      "SELECT o.*, pr.slug as provider_slug FROM otp_orders o JOIN otp_providers pr ON pr.id = o.provider_id WHERE o.id = ? AND o.user_id = ? AND o.status = 'waiting'",
      [req.params.id, req.user.id]
    );
    if (!order) {
      return res.status(404).json({ success: false, message: 'Order tidak ditemukan' });
    }

    const provider = await getProvider(order.provider_slug);
    const result = await provider.resendOtp(order.provider_order_id);

    await pool.query(
      "INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'resend', ?)",
      [order.id, JSON.stringify(result)]
    );

    res.json({ success: true, message: result.message, data: result });
  } catch (err) {
    logger.error({ err }, 'Resend OTP error');
    res.status(500).json({ success: false, message: 'Gagal resend OTP' });
  }
}

module.exports = { getCountries, getServices, getOperators, getPricing, createOrder, getOrder, cancelOrder, resendOtp };
