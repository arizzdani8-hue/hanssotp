const axios = require('axios');
const BaseProvider = require('./BaseProvider');
const logger = require('../utils/logger');

class NokosmurahProvider extends BaseProvider {
  constructor(apiKey) {
    super('nokosmurah', apiKey, 'https://api.nokosmurah.com');
    this.client = axios.create({
      baseURL: this.baseUrl,
      headers: {
        Authorization: `Bearer ${apiKey}`,
        'Content-Type': 'application/json',
      },
      timeout: 30000,
    });
  }

  async createOrder(country, service, operator) {
    try {
      const resp = await this.client.post('/api/order', {
        country,
        service,
        operator: operator || undefined,
      });
      const data = resp.data;
      if (data.success) {
        return {
          orderId: String(data.data.order_id),
          phoneNumber: data.data.phone_number,
        };
      }
      throw new Error(data.message || 'Order failed');
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'Nokosmurah createOrder failed');
      throw new Error(err.response?.data?.message || err.message || 'Failed to order from Nokosmurah');
    }
  }

  async getOrderStatus(orderId) {
    try {
      const resp = await this.client.get(`/api/order/${orderId}`);
      const data = resp.data;
      if (data.success) {
        const statusMap = {
          pending: 'waiting',
          waiting: 'waiting',
          success: 'received',
          cancelled: 'cancelled',
          expired: 'expired',
        };
        return {
          status: statusMap[data.data.status] || 'waiting',
          otpCode: data.data.otp || null,
          phoneNumber: data.data.phone_number || null,
        };
      }
      return { status: 'waiting', otpCode: null, phoneNumber: null };
    } catch (err) {
      logger.error({ err: err.response?.data || err.message }, 'Nokosmurah getOrderStatus failed');
      throw new Error('Failed to check Nokosmurah order');
    }
  }

  async cancelOrder(orderId) {
    try {
      const resp = await this.client.post(`/api/order/${orderId}/cancel`);
      return { success: resp.data.success, message: resp.data.message || 'Cancelled' };
    } catch (err) {
      return { success: false, message: err.response?.data?.message || 'Cancel failed' };
    }
  }

  async resendOtp(orderId) {
    try {
      const resp = await this.client.post(`/api/order/${orderId}/resend`);
      return { success: resp.data.success, message: resp.data.message || 'Resend requested' };
    } catch (err) {
      return { success: false, message: err.response?.data?.message || 'Resend failed' };
    }
  }

  async getBalance() {
    try {
      const resp = await this.client.get('/api/balance');
      return { balance: resp.data.data?.balance || 0 };
    } catch (err) {
      throw new Error('Failed to get Nokosmurah balance');
    }
  }

  async listServices(country) {
    try {
      const resp = await this.client.get('/api/services', { params: { country } });
      if (resp.data.success && Array.isArray(resp.data.data)) {
        return resp.data.data.map((s) => ({
          code: s.code,
          name: s.name,
          price: s.price,
          quantity: s.stock,
        }));
      }
      return [];
    } catch (err) {
      return [];
    }
  }
}

module.exports = NokosmurahProvider;
