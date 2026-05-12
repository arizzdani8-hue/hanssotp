const { z } = require('zod');
const { pool } = require('../config/database');
const { getProvider } = require('../providers');
const logger = require('../utils/logger');

async function logApiCall(apiKeyId, userId, endpoint, method, reqBody, resCode, resBody, ip) {
  await pool.query(
    `INSERT INTO reseller_api_logs (api_key_id, user_id, endpoint, method, request_body, response_code, response_body, ip_address)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [apiKeyId, userId, endpoint, method, reqBody ? JSON.stringify(reqBody) : null, resCode, resBody ? JSON.stringify(resBody) : null, ip]
  );
}

async function getBalance(req, res) {
  const result = { success: true, data: { balance: req.reseller.balance } };
  await logApiCall(req.reseller.api_key_id, req.reseller.user_id, '/api/reseller/balance', 'GET', null, 200, result, req.ip);
  res.json(result);
}

async function getServicesList(req, res) {
  try {
    const countryId = req.query.country_id;
    let where = 'p.is_active = 1';
    const params = [];
    if (countryId) { where += ' AND p.country_id = ?'; params.push(countryId); }

    const [rows] = await pool.query(
      `SELECT p.id as pricing_id, p.sell_price as price, p.stock,
              s.name as service, s.slug as service_slug,
              c.name as country, c.code as country_code,
              o.name as operator, pr.name as provider
       FROM otp_pricing p
       JOIN otp_services s ON s.id = p.service_id AND s.is_active = 1
       JOIN countries c ON c.id = p.country_id AND c.is_active = 1
       JOIN otp_providers pr ON pr.id = p.provider_id AND pr.is_active = 1
       LEFT JOIN operators o ON o.id = p.operator_id
       WHERE ${where}
       ORDER BY c.name, s.name, p.sell_price`,
      params
    );
    const result = { success: true, data: rows };
    await logApiCall(req.reseller.api_key_id, req.reseller.user_id, '/api/reseller/services', 'GET', req.query, 200, result, req.ip);
    res.json(result);
  } catch (err) {
    logger.error({ err }, 'Reseller services error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

const orderSchema = z.object({
  pricing_id: z.number().int().positive(),
});

async function createOrder(req, res) {
  const conn = await pool.getConnection();
  try {
    const data = orderSchema.parse(req.body);
    const userId = req.reseller.user_id;

    const [[pricing]] = await conn.query(
      `SELECT p.*, pr.slug as provider_slug FROM otp_pricing p
       JOIN otp_providers pr ON pr.id = p.provider_id
       WHERE p.id = ? AND p.is_active = 1 AND pr.is_active = 1`,
      [data.pricing_id]
    );
    if (!pricing) {
      conn.release();
      const result = { success: false, message: 'Service tidak tersedia' };
      await logApiCall(req.reseller.api_key_id, userId, '/api/reseller/order', 'POST', req.body, 404, result, req.ip);
      return res.status(404).json(result);
    }

    const [[user]] = await conn.query('SELECT balance, max_active_orders FROM users WHERE id = ? FOR UPDATE', [userId]);
    const sellPrice = parseFloat(pricing.sell_price);
    if (parseFloat(user.balance) < sellPrice) {
      conn.release();
      const result = { success: false, message: 'Saldo tidak mencukupi' };
      await logApiCall(req.reseller.api_key_id, userId, '/api/reseller/order', 'POST', req.body, 400, result, req.ip);
      return res.status(400).json(result);
    }

    const [[{ activeCount }]] = await conn.query(
      "SELECT COUNT(*) as activeCount FROM otp_orders WHERE user_id = ? AND status IN ('pending','waiting')",
      [userId]
    );
    if (activeCount >= user.max_active_orders) {
      conn.release();
      const result = { success: false, message: 'Batas order aktif tercapai' };
      await logApiCall(req.reseller.api_key_id, userId, '/api/reseller/order', 'POST', req.body, 429, result, req.ip);
      return res.status(429).json(result);
    }

    const provider = await getProvider(pricing.provider_slug);
    const providerResult = await provider.createOrder(
      pricing.provider_country_code || 'indonesia',
      pricing.provider_service_code || 'any',
      pricing.provider_operator_code || 'any'
    );

    await conn.beginTransaction();
    const balanceBefore = parseFloat(user.balance);
    const balanceAfter = balanceBefore - sellPrice;
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, userId]);

    const expiresAt = new Date(Date.now() + 15 * 60 * 1000);
    const [orderResult] = await conn.query(
      `INSERT INTO otp_orders (user_id, pricing_id, provider_id, provider_order_id, country_id, service_id, operator_id, phone_number, status, price, cost_price, source, expires_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'waiting', ?, ?, 'api', ?)`,
      [userId, pricing.id, pricing.provider_id, providerResult.orderId, pricing.country_id, pricing.service_id, pricing.operator_id, providerResult.phoneNumber, sellPrice, pricing.cost_price, expiresAt]
    );
    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'order', ?, ?, ?, 'otp_order', ?, 'API Order')`,
      [userId, -sellPrice, balanceBefore, balanceAfter, orderResult.insertId]
    );
    await conn.commit();

    const result = {
      success: true,
      data: { order_id: orderResult.insertId, phone_number: providerResult.phoneNumber, status: 'waiting', price: sellPrice, expires_at: expiresAt },
    };
    await logApiCall(req.reseller.api_key_id, userId, '/api/reseller/order', 'POST', req.body, 201, result, req.ip);
    res.status(201).json(result);
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Reseller order error');
    const result = { success: false, message: err.message || 'Server error' };
    await logApiCall(req.reseller.api_key_id, req.reseller.user_id, '/api/reseller/order', 'POST', req.body, 500, result, req.ip);
    res.status(500).json(result);
  } finally {
    conn.release();
  }
}

async function getOrder(req, res) {
  try {
    const [[order]] = await pool.query(
      `SELECT o.id, o.phone_number, o.otp_code, o.status, o.price, o.created_at, o.expires_at,
              s.name as service, c.name as country
       FROM otp_orders o
       LEFT JOIN otp_services s ON s.id = o.service_id
       LEFT JOIN countries c ON c.id = o.country_id
       WHERE o.id = ? AND o.user_id = ?`,
      [req.params.id, req.reseller.user_id]
    );
    if (!order) {
      return res.status(404).json({ success: false, message: 'Order tidak ditemukan' });
    }
    res.json({ success: true, data: order });
  } catch (err) {
    logger.error({ err }, 'Reseller getOrder error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function cancelOrder(req, res) {
  const conn = await pool.getConnection();
  try {
    const [orders] = await conn.query(
      "SELECT o.*, pr.slug as provider_slug FROM otp_orders o JOIN otp_providers pr ON pr.id = o.provider_id WHERE o.id = ? AND o.user_id = ? AND o.status IN ('pending','waiting')",
      [req.params.id, req.reseller.user_id]
    );
    if (!orders.length) {
      conn.release();
      return res.status(404).json({ success: false, message: 'Order tidak ditemukan atau tidak bisa dibatalkan' });
    }
    const order = orders[0];
    const provider = await getProvider(order.provider_slug);
    await provider.cancelOrder(order.provider_order_id);

    await conn.beginTransaction();
    await conn.query("UPDATE otp_orders SET status = 'cancelled', refunded = 1 WHERE id = ?", [order.id]);
    const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [order.user_id]);
    const balanceBefore = parseFloat(user.balance);
    const balanceAfter = balanceBefore + parseFloat(order.price);
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, order.user_id]);
    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'refund', ?, ?, ?, 'otp_order', ?, 'API Cancel refund')`,
      [order.user_id, order.price, balanceBefore, balanceAfter, order.id]
    );
    await conn.commit();

    res.json({ success: true, message: 'Order dibatalkan, saldo dikembalikan' });
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Reseller cancel error');
    res.status(500).json({ success: false, message: 'Server error' });
  } finally {
    conn.release();
  }
}

module.exports = { getBalance, getServicesList, createOrder, getOrder, cancelOrder };
