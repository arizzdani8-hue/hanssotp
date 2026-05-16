const axios = require('axios');
const BaseGateway = require('./BaseGateway');
const config = require('../config');
const settingsService = require('../services/settingsService');
const logger = require('../utils/logger');

const PAKASIR_BASE_URL = 'https://app.pakasir.com';

class PakasirGateway extends BaseGateway {
  constructor() {
    super('pakasir');
  }

  async _getConfig() {
    const settings = await settingsService.getAll();
    return {
      slug: settings.pakasir_slug || config.pakasir.slug,
      apiKey: settings.pakasir_api_key || config.pakasir.apiKey,
      mode: settings.pakasir_mode || config.pakasir.mode,
      callbackUrl: settings.pakasir_callback_url || config.pakasir.callbackUrl,
    };
  }

  async createPayment(amount, reference, description) {
    const { slug, apiKey } = await this._getConfig();
    if (!slug || !apiKey) {
      throw new Error('Pakasir slug atau API key belum dikonfigurasi');
    }
    try {
      const resp = await axios.post(
        `${PAKASIR_BASE_URL}/api/v1/transaction/create`,
        { project: slug, order_id: reference, amount: parseInt(amount, 10), sel_key: apiKey },
        { headers: { 'Content-Type': 'application/json' }, timeout: 30000 }
      );
      const data = resp.data;
      if (!data.success && !data.payment_url) {
        throw new Error(data.message || 'Pakasir payment creation failed');
      }
      return {
        reference,
        merchantRef: data.order_id || reference,
        qrUrl: data.qr_url || data.payment_url || null,
        checkoutUrl: data.payment_url || `${PAKASIR_BASE_URL}/pay/${slug}/${reference}`,
        expiresAt: null,
        rawResponse: data,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'Pakasir createPayment failed');
      throw new Error(err.response?.data?.message || err.message || 'Gagal membuat pembayaran Pakasir');
    }
  }

  async checkTransaction(reference) {
    const { slug, apiKey } = await this._getConfig();
    try {
      const resp = await axios.get(`${PAKASIR_BASE_URL}/api/v1/transaction/detail/${reference}`, {
        params: { project: slug, sel_key: apiKey },
        timeout: 15000,
      });
      return resp.data;
    } catch (err) {
      logger.error({ err: err.message }, 'Pakasir checkTransaction failed');
      return null;
    }
  }

  verifyWebhook(body) {
    return !!(body && body.amount && body.order_id);
  }

  parseWebhook(body) {
    const status =
      body.status === 'COMPLETED' || body.status === 'paid' ? 'paid'
      : body.status === 'EXPIRED' || body.status === 'expired' ? 'expired'
      : body.status === 'FAILED' || body.status === 'failed' ? 'failed'
      : 'pending';
    return {
      reference: body.order_id,
      gatewayReference: body.trx_id || body.order_id,
      status,
      amount: parseFloat(body.amount) || 0,
      paymentMethod: body.payment_method || 'QRIS',
    };
  }
}

module.exports = PakasirGateway;
