/**
 * Base Payment Gateway interface.
 */
class BaseGateway {
  constructor(name) {
    this.name = name;
  }

  /** Create a payment/deposit. Returns { reference, qrUrl, checkoutUrl, expiresAt } */
  async createPayment(amount, reference, description) {
    throw new Error(`${this.name}: createPayment not implemented`);
  }

  /** Verify webhook signature. Returns boolean */
  verifySignature(payload, signature) {
    throw new Error(`${this.name}: verifySignature not implemented`);
  }

  /** Parse webhook to get { reference, status, amount } */
  parseWebhook(body) {
    throw new Error(`${this.name}: parseWebhook not implemented`);
  }
}

module.exports = BaseGateway;
