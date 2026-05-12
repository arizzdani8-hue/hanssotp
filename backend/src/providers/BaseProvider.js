/**
 * Base OTP Provider interface.
 * All providers must implement these methods.
 */
class BaseProvider {
  constructor(name, apiKey, baseUrl) {
    this.name = name;
    this.apiKey = apiKey;
    this.baseUrl = baseUrl;
  }

  /** Create a new OTP order. Returns { orderId, phoneNumber } */
  async createOrder(country, service, operator) {
    throw new Error(`${this.name}: createOrder not implemented`);
  }

  /** Get order status + OTP. Returns { status, otpCode, phoneNumber } */
  async getOrderStatus(orderId) {
    throw new Error(`${this.name}: getOrderStatus not implemented`);
  }

  /** Cancel an order. Returns { success, message } */
  async cancelOrder(orderId) {
    throw new Error(`${this.name}: cancelOrder not implemented`);
  }

  /** Resend OTP if supported. Returns { success, message } */
  async resendOtp(orderId) {
    throw new Error(`${this.name}: resendOtp not implemented`);
  }

  /** Get provider balance. Returns { balance } */
  async getBalance() {
    throw new Error(`${this.name}: getBalance not implemented`);
  }

  /** List available services. Returns array of services */
  async listServices(country) {
    throw new Error(`${this.name}: listServices not implemented`);
  }
}

module.exports = BaseProvider;
