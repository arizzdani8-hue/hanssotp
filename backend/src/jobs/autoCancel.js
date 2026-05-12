const cron = require('node-cron');
const { pool } = require('../config/database');
const { getProvider } = require('../providers');
const { emitOrderUpdate, emitBalanceUpdate } = require('../websocket');
const telegramService = require('../services/telegramService');
const logger = require('../utils/logger');

function startAutoCancel() {
  cron.schedule('* * * * *', async () => {
    try {
      const [orders] = await pool.query(
        `SELECT o.*, pr.slug as provider_slug, u.username, s.name as service_name
         FROM otp_orders o
         JOIN otp_providers pr ON pr.id = o.provider_id
         JOIN users u ON u.id = o.user_id
         LEFT JOIN otp_services s ON s.id = o.service_id
         WHERE o.status IN ('pending','waiting')
         AND o.expires_at IS NOT NULL
         AND o.expires_at <= NOW()`
      );

      for (const order of orders) {
        const conn = await pool.getConnection();
        try {
          const provider = await getProvider(order.provider_slug);
          try {
            await provider.cancelOrder(order.provider_order_id);
          } catch (cancelErr) {
            logger.warn({ err: cancelErr, orderId: order.id }, 'Provider cancel failed during auto-cancel');
          }

          await conn.beginTransaction();

          await conn.query(
            "UPDATE otp_orders SET status = 'expired', refunded = 1 WHERE id = ?",
            [order.id]
          );

          const [[user]] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [order.user_id]);
          const balanceBefore = parseFloat(user.balance);
          const balanceAfter = balanceBefore + parseFloat(order.price);
          await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, order.user_id]);

          await conn.query(
            `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, reference_id, description)
             VALUES (?, 'refund', ?, ?, ?, 'otp_order', ?, 'Auto cancel - expired')`,
            [order.user_id, order.price, balanceBefore, balanceAfter, order.id]
          );

          await conn.query(
            "INSERT INTO otp_order_logs (order_id, action, details) VALUES (?, 'auto_cancelled', ?)",
            [order.id, JSON.stringify({ reason: 'Expired after 15 minutes' })]
          );

          await conn.commit();

          emitOrderUpdate(order.user_id, order.id, { status: 'expired' });
          emitBalanceUpdate(order.user_id, balanceAfter);

          telegramService.notifyOrderFailed(
            order.user_id, order.username, order.service_name,
            'OTP tidak masuk dalam 15 menit'
          );

          logger.info({ orderId: order.id }, 'Order auto-cancelled');
        } catch (err) {
          await conn.rollback();
          logger.error({ err, orderId: order.id }, 'Auto cancel error');
        } finally {
          conn.release();
        }
      }
    } catch (err) {
      logger.error({ err }, 'Auto cancel job error');
    }
  });

  logger.info('Auto cancel job started (every 1 minute)');
}

module.exports = { startAutoCancel };
