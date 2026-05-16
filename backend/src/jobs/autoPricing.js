const cron = require('node-cron');
const { pool } = require('../config/database');
const settingsService = require('../services/settingsService');
const logger = require('../utils/logger');

function startAutoPricing() {
  cron.schedule('0 * * * *', async () => {
    try {
      const settings = await settingsService.getAll();
      if (!settings.auto_pricing_enabled) return;

      const threshold = settings.auto_pricing_demand_threshold || 100;
      const increasePercent = settings.auto_pricing_increase_percent || 5;

      const [pricings] = await pool.query(
        'SELECT p.* FROM otp_pricing p WHERE p.auto_pricing = 1 AND p.is_active = 1'
      );

      for (const pricing of pricings) {
        const [[{ orderCount }]] = await pool.query(
          `SELECT COUNT(*) as orderCount FROM otp_orders
           WHERE pricing_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)`,
          [pricing.id]
        );

        const demandMultiplier = 1 + Math.floor(orderCount / threshold) * (increasePercent / 100);
        const newSellPrice = Math.ceil(
          parseFloat(pricing.cost_price) * (1 + parseFloat(pricing.markup_percent) / 100) * demandMultiplier
        );

        if (newSellPrice !== parseFloat(pricing.sell_price)) {
          await pool.query(
            'UPDATE otp_pricing SET sell_price = ?, demand_multiplier = ? WHERE id = ?',
            [newSellPrice, demandMultiplier, pricing.id]
          );

          await pool.query(
            `INSERT INTO pricing_history (pricing_id, old_sell_price, new_sell_price, reason, changed_by)
             VALUES (?, ?, ?, ?, 'system')`,
            [pricing.id, pricing.sell_price, newSellPrice, `Auto pricing: ${orderCount} orders in 24h`]
          );

          logger.info({ pricingId: pricing.id, oldPrice: pricing.sell_price, newPrice: newSellPrice }, 'Auto pricing updated');
        }
      }
    } catch (err) {
      logger.error({ err }, 'Auto pricing job error');
    }
  });

  logger.info('Auto pricing job started (every hour)');
}

module.exports = { startAutoPricing };
