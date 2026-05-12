const axios = require('axios');
const BaseProvider = require('./BaseProvider');
const logger = require('../utils/logger');

class FiveSimProvider extends BaseProvider {
  constructor(apiKey) {
    super('5sim', apiKey, 'https://5sim.net/v1');
    this.client = axios.create({
      baseURL: this.baseUrl,
      headers: {
        Authorization: `Bearer ${apiKey}`,
        Accept: 'application/json',
      },
      timeout: 30000,
    });
  }

  async createOrder(country, service, operator = 'any') {
    try {
      const resp = await this.client.get(
        `/user/buy/activation/${country}/${operator}/${service}`
      );
      return {
        orderId: String(resp.data.id),
        phoneNumber: resp.data.phone ? `+${resp.data.phone}` : null,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, '5sim createOrder failed');
      throw new Error(err.response?.data?.message || 'Failed to create order on 5sim');
    }
  }

  async getOrderStatus(orderId) {
    try {
      const resp = await this.client.get(`/user/check/${orderId}`);
      const data = resp.data;
      let otpCode = null;
      if (data.sms && data.sms.length > 0) {
        otpCode = data.sms[data.sms.length - 1].code;
      }
      const statusMap = {
        PENDING: 'waiting',
        RECEIVED: 'waiting',
        CANCELED: 'cancelled',
        TIMEOUT: 'expired',
        FINISHED: 'received',
      };
      return {
        status: statusMap[data.status] || 'waiting',
        otpCode,
        phoneNumber: data.phone ? `+${data.phone}` : null,
      };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, '5sim getOrderStatus failed');
      throw new Error('Failed to check order status on 5sim');
    }
  }

  async cancelOrder(orderId) {
    try {
      await this.client.get(`/user/cancel/${orderId}`);
      return { success: true, message: 'Order cancelled' };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, '5sim cancelOrder failed');
      return { success: false, message: err.response?.data?.message || 'Cancel failed' };
    }
  }

  async resendOtp(orderId) {
    return { success: false, message: '5sim does not support resend' };
  }

  async getBalance() {
    try {
      const resp = await this.client.get('/user/profile');
      return { balance: resp.data.balance };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, '5sim getBalance failed');
      throw new Error('Failed to get 5sim balance');
    }
  }

  async listServices(country = 'indonesia') {
    try {
      const resp = await this.client.get(`/guest/products/${country}/any`);
      const services = [];
      for (const [key, val] of Object.entries(resp.data)) {
        services.push({
          code: key,
          name: key,
          price: val.Price,
          quantity: val.Qty,
        });
      }
      return services;
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, '5sim listServices failed');
      return [];
    }
  }
}

module.exports = FiveSimProvider;
