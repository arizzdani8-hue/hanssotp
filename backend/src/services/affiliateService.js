const { pool } = require('../config/database');
const settingsService = require('./settingsService');
const logger = require('../utils/logger');

async function processCommission(userId, type, sourceAmount) {
  const conn = await pool.getConnection();
  try {
    const [users] = await conn.query('SELECT referred_by FROM users WHERE id = ?', [userId]);
    if (!users.length || !users[0].referred_by) return;

    const referrerId = users[0].referred_by;
    const [affiliates] = await conn.query(
      'SELECT * FROM affiliates WHERE user_id = ? AND is_active = 1',
      [referrerId]
    );
    if (!affiliates.length) return;

    const affiliate = affiliates[0];
    const rate = type === 'deposit' ? affiliate.commission_rate_deposit : affiliate.commission_rate_order;
    const commission = Math.floor(sourceAmount * rate / 100);
    if (commission <= 0) return;

    await conn.beginTransaction();

    const [referrer] = await conn.query('SELECT balance FROM users WHERE id = ? FOR UPDATE', [referrerId]);
    const balanceBefore = parseFloat(referrer[0].balance);
    const balanceAfter = balanceBefore + commission;

    await conn.query('UPDATE users SET balance = ? WHERE id = ?', [balanceAfter, referrerId]);

    await conn.query(
      `INSERT INTO affiliate_commissions (affiliate_id, referral_user_id, type, source_amount, commission_rate, commission_amount, status)
       VALUES (?, ?, ?, ?, ?, ?, 'paid')`,
      [affiliate.id, userId, type, sourceAmount, rate, commission]
    );

    await conn.query(
      `INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, reference_type, description)
       VALUES (?, 'affiliate_commission', ?, ?, ?, 'affiliate_commission', ?)`,
      [referrerId, commission, balanceBefore, balanceAfter, `Komisi ${type} dari referral`]
    );

    await conn.query(
      'UPDATE affiliates SET total_commission = total_commission + ? WHERE id = ?',
      [commission, affiliate.id]
    );

    await conn.commit();
    logger.info({ referrerId, userId, type, commission }, 'Affiliate commission processed');
  } catch (err) {
    await conn.rollback();
    logger.error({ err }, 'Failed to process affiliate commission');
  } finally {
    conn.release();
  }
}

module.exports = { processCommission };
