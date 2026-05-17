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
        `${PAKASIR_BASE_URL}/api/transactioncreate/qris`,
        { project: slug, order_id: reference, amount: parseInt(amount, 10), api_key: apiKey },
        { headers: { 'Content-Type': 'application/json' }, timeout: 30000 }
      );
      const data = resp.data;
      const payment = data.payment;
      if (!payment) {
        throw new Error(data.message || 'Pakasir: gagal membuat transaksi');
      }

      const qrString = payment.payment_number || '';
      const qrImageUrl = qrString
        ? `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qrString)}`
        : null;

      const checkoutUrl = `${PAKASIR_BASE_URL}/pay/${slug}/${parseInt(amount, 10)}?order_id=${encodeURIComponent(reference)}&qris_only=1`;

      return {
        reference,
        merchantRef: payment.order_id || reference,
        qrUrl: qrImageUrl,
        qrString,
        checkoutUrl,
        totalPayment: payment.total_payment || parseInt(amount, 10),
        expiresAt: payment.expired_at || null,
        rawResponse: data,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'Pakasir createPayment failed');
      throw new Error(err.response?.data?.message || err.message || 'Gagal membuat pembayaran Pakasir');
    }
  }

  async checkTransaction(reference, amount) {
    const { slug, apiKey } = await this._getConfig();
    try {
      const resp = await axios.get(`${PAKASIR_BASE_URL}/api/transactiondetail`, {
        params: { project: slug, order_id: reference, amount, api_key: apiKey },
        timeout: 15000,
      });
      return resp.data;
    } catch (err) {
      logger.error({ err: err.message }, 'Pakasir checkTransaction failed');
      return null;
    }
  }

  async simulatePayment(reference, amount) {
    const { slug, apiKey } = await this._getConfig();
    try {
      const resp = await axios.post(
        `${PAKASIR_BASE_URL}/api/paymentsimulation`,
        { project: slug, order_id: reference, amount: parseInt(amount, 10), api_key: apiKey },
        { headers: { 'Content-Type': 'application/json' }, timeout: 15000 }
      );
      return resp.data;
    } catch (err) {
      logger.error({ err: err.message }, 'Pakasir simulatePayment failed');
      return null;
    }
  }

  async cancelTransaction(reference, amount) {
    const { slug, apiKey } = await this._getConfig();
    try {
      const resp = await axios.post(
        `${PAKASIR_BASE_URL}/api/transactioncancel`,
        { project: slug, order_id: reference, amount: parseInt(amount, 10), api_key: apiKey },
        { headers: { 'Content-Type': 'application/json' }, timeout: 15000 }
      );
      return resp.data;
    } catch (err) {
      logger.error({ err: err.message }, 'Pakasir cancelTransaction failed');
      return null;
    }
  }

  verifyWebhook(body) {
    return !!(body && body.amount && body.order_id && body.status && body.project);
  }

  parseWebhook(body) {
    const status =
      body.status === 'completed' ? 'paid'
      : body.status === 'expired' ? 'expired'
      : body.status === 'failed' ? 'failed'
      : 'pending';
    return {
      reference: body.order_id,
      gatewayReference: body.order_id,
      status,
      amount: parseFloat(body.amount) || 0,
      paymentMethod: body.payment_method || 'qris',
    };
  }
}

module.exports = PakasirGateway;
