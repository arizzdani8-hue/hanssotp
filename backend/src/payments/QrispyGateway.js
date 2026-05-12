const axios = require('axios');
const crypto = require('crypto');
const BaseGateway = require('./BaseGateway');
const config = require('../config');
const settingsService = require('../services/settingsService');
const logger = require('../utils/logger');

class QrispyGateway extends BaseGateway {
  constructor() {
    super('qrispy');
  }

  async _getKey() {
    const settings = await settingsService.getAll();
    return settings.api_key_qrispy || config.qrispy.apiKey;
  }

  async createPayment(amount, reference, description) {
    const apiKey = await this._getKey();
    const apiUrl = config.qrispy.apiUrl;

    try {
      const resp = await axios.post(
        `${apiUrl}/api/payment/create`,
        {
          amount: parseInt(amount, 10),
          reference,
          description: description || 'Deposit Saldo',
        },
        {
          headers: {
            Authorization: `Bearer ${apiKey}`,
            'Content-Type': 'application/json',
          },
        }
      );

      const data = resp.data.data || resp.data;
      return {
        reference: data.reference || reference,
        merchantRef: reference,
        qrUrl: data.qr_url || data.qr_image || null,
        checkoutUrl: data.checkout_url || null,
        expiresAt: data.expired_at || null,
        rawResponse: data,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'QRISPY createPayment failed');
      throw new Error(err.response?.data?.message || 'QRISPY payment creation failed');
    }
  }

  async verifySignature(payload, receivedSignature) {
    const apiKey = await this._getKey();
    const jsonStr = JSON.stringify(payload);
    const calculated = crypto
      .createHmac('sha256', apiKey)
      .update(jsonStr)
      .digest('hex');
    return calculated === receivedSignature;
  }

  parseWebhook(body) {
    const statusMap = { success: 'paid', paid: 'paid', expired: 'expired', failed: 'failed' };
    return {
      reference: body.reference,
      gatewayReference: body.transaction_id || body.reference,
      status: statusMap[body.status] || 'pending',
      amount: body.amount,
    };
  }
}

module.exports = QrispyGateway;
