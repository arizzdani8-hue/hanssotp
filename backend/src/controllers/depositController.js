const { z } = require('zod');
const { pool } = require('../config/database');
const { getGateway } = require('../payments');
const { generateReference } = require('../utils/helpers');
const activityService = require('../services/activityService');
const affiliateService = require('../services/affiliateService');
const telegramService = require('../services/telegramService');
const { emitDepositUpdate, emitBalanceUpdate } = require('../websocket');
const logger = require('../utils/logger');

const createDepositSchema = z.object({
  amount: z.number().int().min(10000).max(10000000),
});

async function createDeposit(req, res) {
  try {
    const data = createDepositSchema.parse(req.body);
    const userId = req.user.id;
    const reference = generateReference('DEP');

    const [[paymentSettings]] = await pool.query(
      "SELECT * FROM payment_settings WHERE gateway_name = 'pakasir' AND is_active = 1 LIMIT 1"
    );

    const feePercent = paymentSettings ? parseFloat(paymentSettings.fee_percent) : 0;
    const feeFlat = paymentSettings ? parseFloat(paymentSettings.fee_flat) : 0;
    const fee = Math.ceil(data.amount * feePercent / 100) + feeFlat;
    const totalAmount = data.amount + fee;

    const gateway = getGateway('pakasir');
    const payment = await gateway.createPayment(totalAmount, reference, 'Deposit Saldo NyooApp');

    const [result] = await pool.query(
      `INSERT INTO deposits (user_id, gateway, reference, merchant_ref, amount, fee, total_amount, status, payment_method, qr_url, checkout_url, expired_at, gateway_response)
       VALUES (?, 'pakasir', ?, ?, ?, ?, ?, 'pending', 'QRIS', ?, ?, ?, ?)`,
      [
        userId, reference, payment.merchantRef || reference,
        data.amount, fee, totalAmount,
        payment.qrUrl, payment.checkoutUrl,
        payment.expiresAt, JSON.stringify(payment.rawResponse || {}),
      ]
    );

    await activityService.log({
      actorType: 'user',
      actorId: userId,
      action: 'create_deposit',
      targetType: 'deposit',
      targetId: result.insertId,
      details: { amount: data.amount, gateway: 'pakasir' },
      ipAddress: req.ip,
    });

    res.status(201).json({
      success: true,
      data: {
        id: result.insertId,
        reference,
        amount: data.amount,
        fee,
        total_amount: totalAmount,
        qr_url: payment.qrUrl,
        checkout_url: payment.checkoutUrl,
        expired_at: payment.expiresAt,
        status: 'pending',
      },
    });
  } catch (err) {
    if (err instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: err.errors });
    }
    logger.error({ err }, 'Create deposit error');
    res.status(500).json({ success: false, message: err.message || 'Gagal membuat deposit' });
  }
}

async function getDeposit(req, res) {
  try {
    const [[deposit]] = await pool.query(
      'SELECT * FROM deposits WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (!deposit) {
      return res.status(404).json({ success: false, message: 'Deposit tidak ditemukan' });
    }
    res.json({ success: true, data: deposit });
  } catch (err) {
    logger.error({ err }, 'Get deposit error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function getDepositHistory(req, res) {
  try {
    const userId = req.user.id;
    const page = Math.max(1, parseInt(req.query.page) || 1);
    const limit = Math.min(50, Math.max(1, parseInt(req.query.limit) || 10));
    const offset = (page - 1) * limit;

    const [rows] = await pool.query(
      'SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
      [userId, limit, offset]
    );
    const [[{ total }]] = await pool.query(
      'SELECT COUNT(*) as total FROM deposits WHERE user_id = ?',
      [userId]
    );

    res.json({ success: true, data: { deposits: rows, total, page, limit } });
  } catch (err) {
    logger.error({ err }, 'Get deposit history error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function webhookPakasir(req, res) {
  try {
    const gateway = getGateway('pakasir');

    await pool.query(
      `INSERT INTO webhook_logs (gateway, event_type, payload, ip_address)
       VALUES ('pakasir', 'callback', ?, ?)`,
      [JSON.stringify(req.body), req.ip]
    );

    const isValid = gateway.verifyWebhook(req.body);
    if (!isValid) {
      await pool.query(
        "UPDATE webhook_logs SET is_valid = 0, error_message = 'Invalid webhook data' WHERE gateway = 'pakasir' ORDER BY id DESC LIMIT 1"
      );
      return res.status(400).json({ success: false, message: 'Invalid webhook' });
    }

    await pool.query(
      "UPDATE webhook_logs SET is_valid = 1 WHERE gateway = 'pakasir' ORDER BY id DESC LIMIT 1"
    );

    const parsed = gateway.parseWebhook(req.body);
    await processDepositCallback(parsed);

    res.json({ success: true });
  } catch (err) {
    logger.error({ err }, 'Pakasir webhook error');
    res.status(500).json({ success: false });
  }
}

async function processDepositCallback(parsed) {
  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();

    const [deposits] = await conn.query(
      "SELECT * FROM deposits WHERE (reference = ? OR merchant_ref = ?) AND status = 'pending' FOR UPDATE",
      [parsed.reference, parsed.reference]
    );
    if (!deposits.length) {
      await conn.rollback();
      return;
    }

    const deposit = deposits[0];

    if (parsed.status === 'paid') {
      await conn.query(
        "UPDATE deposits SET status = 'paid', paid_at = NOW() WHERE id = ?",
        [deposit.id]
      );

      const [users] = await conn.query(
        'SELECT id, username, balance FROM users WHERE id = ? FOR UPDATE',
        [deposit.user_id]
      );
      const user = users[0];
      const balanceBefore = parseFloat(user.balance);
      const balanceAfter = balanceBefore + parseFloat(deposit.amount);

      await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, user.id]);

      await conn.query(
        `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
         VALUES (?, 'deposit', ?, ?, ?, 'deposit', ?, 'Deposit via QRIS Pakasir')`,
        [user.id, deposit.amount, balanceBefore, balanceAfter, deposit.id]
      );

      await conn.commit();

      emitDepositUpdate(user.id, deposit.id, { status: 'paid', amount: parseFloat(deposit.amount) });
      emitBalanceUpdate(user.id, balanceAfter);
      telegramService.notifyDepositSuccess(user.id, user.username, parseFloat(deposit.amount));
      affiliateService.processCommission(user.id, 'deposit', parseFloat(deposit.amount));

      await pool.query(
        "UPDATE webhook_logs SET processed = 1 WHERE gateway = 'pakasir' ORDER BY id DESC LIMIT 1"
      );
    } else if (['expired', 'failed'].includes(parsed.status)) {
      await conn.query('UPDATE deposits SET status = ? WHERE id = ?', [parsed.status, deposit.id]);
      await conn.commit();
      emitDepositUpdate(deposit.user_id, deposit.id, { status: parsed.status });
    } else {
      await conn.rollback();
    }
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'processDepositCallback error');
    throw err;
  } finally {
    conn.release();
  }
}

module.exports = { createDeposit, getDeposit, getDepositHistory, webhookPakasir };
