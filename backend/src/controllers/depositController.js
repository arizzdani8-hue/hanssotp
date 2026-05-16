const { z } = require('zod');
const { pool } = require('../config/database');
const { getGateway } = require('../payments');
const { generateReference } = require('../utils/helpers');
const activityService = require('../services/activityService');
const affiliateService = require('../services/affiliateService');
const telegramService = require('../services/telegramService');
const logger = require('../utils/logger');

const createDepositSchema = z.object({
  amount: z.number().int().min(10000).max(10000000),
  gateway: z.enum(['tripay', 'qrispy']).default('tripay'),
});

async function createDeposit(req, res) {
  try {
    const data = createDepositSchema.parse(req.body);
    const userId = req.user.id;
    const reference = generateReference('DEP');

    const [[gateway]] = await pool.query(
      'SELECT * FROM payment_gateways WHERE slug = ? AND is_active = 1',
      [data.gateway]
    );
    if (!gateway) {
      return res.status(400).json({ success: false, message: 'Payment gateway tidak tersedia' });
    }

    const fee = Math.ceil(data.amount * gateway.fee_percent / 100) + parseFloat(gateway.fee_flat);
    const totalAmount = data.amount + fee;

    const paymentGateway = getGateway(data.gateway);
    const payment = await paymentGateway.createPayment(totalAmount, reference, 'Deposit Saldo HanssOTP');

    const [result] = await pool.query(
      `INSERT INTO deposits (user_id, gateway_id, reference, merchant_ref, amount, fee, total_amount, status, payment_method, qr_url, checkout_url, expired_at, gateway_response)
       VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'QRIS', ?, ?, ?, ?)`,
      [
        userId, gateway.id, reference, payment.merchantRef || reference,
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
      details: { amount: data.amount, gateway: data.gateway },
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
    res.status(500).json({ success: false, message: 'Gagal membuat deposit' });
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

async function webhookTripay(req, res) {
  try {
    const gateway = getGateway('tripay');
    const signature = req.headers['x-callback-signature'];

    await pool.query(
      `INSERT INTO webhook_logs (gateway, event_type, payload, signature, ip_address)
       VALUES ('tripay', 'callback', ?, ?, ?)`,
      [JSON.stringify(req.body), signature, req.ip]
    );

    const valid = await gateway.verifySignature(req.body, signature);
    if (!valid) {
      await pool.query(
        "UPDATE webhook_logs SET is_valid = 0, error_message = 'Invalid signature' WHERE gateway = 'tripay' ORDER BY id DESC LIMIT 1"
      );
      return res.status(400).json({ success: false, message: 'Invalid signature' });
    }

    await pool.query(
      'UPDATE webhook_logs SET is_valid = 1 WHERE gateway = ? ORDER BY id DESC LIMIT 1',
      ['tripay']
    );

    const parsed = gateway.parseWebhook(req.body);
    await processDepositCallback(parsed);

    res.json({ success: true });
  } catch (err) {
    logger.error({ err }, 'Tripay webhook error');
    res.status(500).json({ success: false });
  }
}

async function webhookQrispy(req, res) {
  try {
    const gateway = getGateway('qrispy');
    const signature = req.headers['x-signature'] || req.headers['x-callback-signature'];

    await pool.query(
      `INSERT INTO webhook_logs (gateway, event_type, payload, signature, ip_address)
       VALUES ('qrispy', 'callback', ?, ?, ?)`,
      [JSON.stringify(req.body), signature, req.ip]
    );

    const valid = await gateway.verifySignature(req.body, signature);
    if (!valid) {
      await pool.query(
        "UPDATE webhook_logs SET is_valid = 0, error_message = 'Invalid signature' WHERE gateway = 'qrispy' ORDER BY id DESC LIMIT 1"
      );
      return res.status(400).json({ success: false, message: 'Invalid signature' });
    }

    await pool.query(
      'UPDATE webhook_logs SET is_valid = 1 WHERE gateway = ? ORDER BY id DESC LIMIT 1',
      ['qrispy']
    );

    const parsed = gateway.parseWebhook(req.body);
    await processDepositCallback(parsed);

    res.json({ success: true });
  } catch (err) {
    logger.error({ err }, 'QRISPY webhook error');
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
         VALUES (?, 'deposit', ?, ?, ?, 'deposit', ?, 'Deposit via QRIS')`,
        [user.id, deposit.amount, balanceBefore, balanceAfter, deposit.id]
      );

      await conn.commit();

      telegramService.notifyDepositSuccess(user.id, user.username, parseFloat(deposit.amount));
      affiliateService.processCommission(user.id, 'deposit', parseFloat(deposit.amount));

      await pool.query(
        'UPDATE webhook_logs SET processed = 1 WHERE gateway IN (?,?) ORDER BY id DESC LIMIT 1',
        ['tripay', 'qrispy']
      );
    } else if (['expired', 'failed'].includes(parsed.status)) {
      await conn.query('UPDATE deposits SET status = ? WHERE id = ?', [parsed.status, deposit.id]);
      await conn.commit();
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

module.exports = { createDeposit, getDeposit, webhookTripay, webhookQrispy };
