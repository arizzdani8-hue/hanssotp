const axios = require('axios');
const crypto = require('crypto');
const BaseGateway = require('./BaseGateway');
const config = require('../config');
const settingsService = require('../services/settingsService');
const logger = require('../utils/logger');

class TripayGateway extends BaseGateway {
  constructor() {
    super('tripay');
  }

  async _getKeys() {
    const settings = await settingsService.getAll();
    return {
      apiKey: settings.api_key_tripay || config.tripay.apiKey,
      privateKey: settings.api_key_tripay_private || config.tripay.privateKey,
      merchantCode: settings.api_key_tripay_merchant || config.tripay.merchantCode,
    };
  }

  async createPayment(amount, reference, description) {
    const { apiKey, privateKey, merchantCode } = await this._getKeys();
    const apiUrl = config.tripay.apiUrl;

    const signature = crypto
      .createHmac('sha256', privateKey)
      .update(merchantCode + reference + amount)
      .digest('hex');

    try {
      const resp = await axios.post(
        `${apiUrl}/transaction/create`,
        {
          method: 'QRIS',
          merchant_ref: reference,
          amount: parseInt(amount, 10),
          customer_name: 'User',
          customer_email: 'user@hanssotp.com',
          order_items: [
            {
              name: description || 'Deposit Saldo',
              price: parseInt(amount, 10),
              quantity: 1,
            },
          ],
          signature,
        },
        {
          headers: { Authorization: `Bearer ${apiKey}` },
        }
      );

      const data = resp.data.data;
      return {
        reference: data.reference,
        merchantRef: data.merchant_ref,
        qrUrl: data.qr_url || null,
        checkoutUrl: data.checkout_url || null,
        expiresAt: data.expired_time
          ? new Date(data.expired_time * 1000).toISOString()
          : null,
        rawResponse: data,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'Tripay createPayment failed');
      throw new Error(err.response?.data?.message || 'Tripay payment creation failed');
    }
  }

  async verifySignature(payload, receivedSignature) {
    const { privateKey } = await this._getKeys();
    const jsonStr = JSON.stringify(payload);
    const calculated = crypto
      .createHmac('sha256', privateKey)
      .update(jsonStr)
      .digest('hex');
    return calculated === receivedSignature;
  }

  parseWebhook(body) {
    const statusMap = { PAID: 'paid', EXPIRED: 'expired', FAILED: 'failed' };
    return {
      reference: body.merchant_ref,
      gatewayReference: body.reference,
      status: statusMap[body.status] || 'pending',
      amount: body.total_amount || body.amount,
    };
  }
}

module.exports = TripayGateway;
