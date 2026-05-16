const crypto = require('crypto');

function generateApiKey() {
  return crypto.randomBytes(32).toString('hex');
}

function generateReferralCode() {
  return crypto.randomBytes(4).toString('hex').toUpperCase();
}

function generateReference(prefix = 'DEP') {
  const timestamp = Date.now().toString(36).toUpperCase();
  const rand = crypto.randomBytes(4).toString('hex').toUpperCase();
  return `${prefix}-${timestamp}-${rand}`;
}

function paginate(page = 1, limit = 20) {
  const p = Math.max(1, parseInt(page, 10) || 1);
  const l = Math.min(100, Math.max(1, parseInt(limit, 10) || 20));
  return { offset: (p - 1) * l, limit: l, page: p };
}

function calculateSellPrice(costPrice, markupPercent, demandMultiplier = 1) {
  const base = costPrice * (1 + markupPercent / 100);
  return Math.ceil(base * demandMultiplier);
}

module.exports = {
  generateApiKey,
  generateReferralCode,
  generateReference,
  paginate,
  calculateSellPrice,
};
