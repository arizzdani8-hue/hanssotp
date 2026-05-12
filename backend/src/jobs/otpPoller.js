const cron = require('node-cron');
const { pool } = require('../config/database');
const { getProvider } = require('../providers');
const { emitOtpReceived, emitOrderUpdate, emitBalanceUpdate } = require('../websocket');
const telegramService = require('../services/telegramService');
const affiliateService = require('../services/affiliateService');
const logger = require('../utils/logger');

function startOtpPoller() {
  cron.schedule('*/15 * * * * *', async () => {
    try {
      const [orders] = await pool.query(
        `SELECT o.*, pr.slug as provider_slug, u.username, s.name as service_name
         FROM otp_orders o
         JOIN otp_providers pr ON pr.id = o.provider_id
         JOIN users u ON u.id = o.user_id
         LEFT JOIN otp_services s ON s.id = o.service_id
         WHERE o.status = 'waiting' AND o.provider_order_id IS NOT NULL`
      );

      for (const order of orders) {
        try {
          const provider = await getProvider(order.provider_slug);
          const result = await provider.getOrderStatus(order.provider_order_id);

          if (result.otpCode && result.status === 'received') {
            const [updateResult] = await pool.query(
              "UPDATE otp_orders SET status = 'received', otp_code = ?, completed_at = NOW() WHERE id = ? AND status = 'waiting'",
              [result.otpCode, order.id]
            );
            if (updateResult.affectedRows === 0) continue;

            await pool.query(
              "INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'otp_received', ?)",
              [order.id, JSON.stringify({ otp: result.otpCode })]
            );

            emitOtpReceived(order.user_id, order.id, {
              otp_code: result.otpCode,
              phone_number: order.phone_number,
              status: 'received',
              service_name: order.service_name,
            });

            telegramService.notifyOtpReceived(
              order.user_id, order.username, order.service_name,
              order.phone_number, result.otpCode
            );

            affiliateService.processCommission(order.user_id, 'order', parseFloat(order.price));

            logger.info({ orderId: order.id, otp: result.otpCode }, 'OTP received');
          } else if (['cancelled', 'expired'].includes(result.status)) {
            await handleOrderExpired(order);
          }
        } catch (err) {
          logger.error({ err, orderId: order.id }, 'OTP poll error for order');
        }
      }
    } catch (err) {
      logger.error({ err }, 'OTP poller error');
    }
  });

  logger.info('OTP poller started (every 15 seconds)');
}

async function handleOrderExpired(order) {
  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();

    await conn.query(
      "UPDATE otp_orders SET status = 'expired', refunded = 1 WHERE id = ? AND status = 'waiting'",
      [order.id]
    );

    const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [order.user_id]);
    const balanceBefore = parseFloat(user.balance);
    const balanceAfter = balanceBefore + parseFloat(order.price);
    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, order.user_id]);

    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
       VALUES (?, 'refund', ?, ?, ?, 'otp_order', ?, 'Auto refund - order expired')`,
      [order.user_id, order.price, balanceBefore, balanceAfter, order.id]
    );

    await conn.query(
      "INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'expired', ?)",
      [order.id, JSON.stringify({ reason: 'Provider reported expired/cancelled' })]
    );

    await conn.commit();

    emitOrderUpdate(order.user_id, order.id, { status: 'expired' });
    emitBalanceUpdate(order.user_id, balanceAfter);

    logger.info({ orderId: order.id }, 'Order expired, refunded');
  } catch (err) {
    await conn.rollback();
    logger.error({ err, orderId: order.id }, 'Handle expired error');
  } finally {
    conn.release();
  }
}

module.exports = { startOtpPoller };
