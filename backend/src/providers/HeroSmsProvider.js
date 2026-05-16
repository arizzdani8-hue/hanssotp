const axios = require('axios');
const BaseProvider = require('./BaseProvider');
const logger = require('../utils/logger');

class HeroSmsProvider extends BaseProvider {
  constructor(apiKey, apiUrl) {
    super('herosms', apiKey, apiUrl || 'https://api.hero-sms.com');
    this.client = axios.create({
      baseURL: this.baseUrl,
      headers: { 'Content-Type': 'application/json' },
      timeout: 30000,
    });
  }

  async createOrder(country, service, operator) {
    try {
      const resp = await this.client.post('/stubs/handler_api.php', null, {
        params: {
          api_key: this.apiKey,
          action: 'getNumber',
          service,
          country,
          operator: operator || undefined,
        },
      });
      const data = resp.data;
      if (data.startsWith('ACCESS_NUMBER')) {
        const parts = data.split(':');
        return {
          orderId: parts[1],
          phoneNumber: `+${parts[2]}`,
        };
      }
      throw new Error(data);
    } catch (err) {
      logger.error({ err: err.message }, 'HeroSMS createOrder failed');
      throw new Error(err.message || 'Failed to create order on HeroSMS');
    }
  }

  async getOrderStatus(orderId) {
    try {
      const resp = await this.client.get('/stubs/handler_api.php', {
        params: { api_key: this.apiKey, action: 'getStatus', id: orderId },
      });
      const data = resp.data;
      if (data.startsWith('STATUS_OK')) {
        return { status: 'received', otpCode: data.split(':')[1], phoneNumber: null };
      }
      if (data === 'STATUS_WAIT_CODE') {
        return { status: 'waiting', otpCode: null, phoneNumber: null };
      }
      if (data === 'STATUS_CANCEL') {
        return { status: 'cancelled', otpCode: null, phoneNumber: null };
      }
      return { status: 'waiting', otpCode: null, phoneNumber: null };
    } catch (err) {
      logger.error({ err: err.message }, 'HeroSMS getOrderStatus failed');
      throw new Error('Failed to check status on HeroSMS');
    }
  }

  async cancelOrder(orderId) {
    try {
      const resp = await this.client.get('/stubs/handler_api.php', {
        params: { api_key: this.apiKey, action: 'setStatus', id: orderId, status: 8 },
      });
      return { success: resp.data === 'ACCESS_CANCEL', message: resp.data };
    } catch (err) {
      return { success: false, message: err.message };
    }
  }

  async resendOtp(orderId) {
    try {
      const resp = await this.client.get('/stubs/handler_api.php', {
        params: { api_key: this.apiKey, action: 'setStatus', id: orderId, status: 3 },
      });
      return { success: resp.data === 'ACCESS_RETRY_GET', message: resp.data };
    } catch (err) {
      return { success: false, message: err.message };
    }
  }

  async getBalance() {
    try {
      const resp = await this.client.get('/stubs/handler_api.php', {
        params: { api_key: this.apiKey, action: 'getBalance' },
      });
      const balance = parseFloat(resp.data.replace('ACCESS_BALANCE:', ''));
      return { balance };
    } catch (err) {
      throw new Error('Failed to get HeroSMS balance');
    }
  }

  async listServices(country) {
    try {
      const resp = await this.client.get('/stubs/handler_api.php', {
        params: { api_key: this.apiKey, action: 'getPrices', country },
      });
      const services = [];
      if (typeof resp.data === 'object') {
        for (const [countryCode, svcs] of Object.entries(resp.data)) {
          for (const [svcCode, info] of Object.entries(svcs)) {
            services.push({ code: svcCode, name: svcCode, price: info.cost, quantity: info.count });
          }
        }
      }
      return services;
    } catch (err) {
      return [];
    }
  }
}

module.exports = HeroSmsProvider;
