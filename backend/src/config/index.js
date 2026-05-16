module.exports = {
  port: parseInt(process.env.PORT || '5000', 10),
  nodeEnv: process.env.NODE_ENV || 'development',
  jwt: {
    secret: process.env.JWT_SECRET || 'change_this_secret',
    adminSecret: process.env.JWT_ADMIN_SECRET || 'change_this_admin_secret',
    expiresIn: process.env.JWT_EXPIRES_IN || '7d',
    adminExpiresIn: process.env.JWT_ADMIN_EXPIRES_IN || '24h',
  },
  cors: {
    origin: process.env.CORS_ORIGIN || 'http://localhost:5173',
  },
  pakasir: {
    slug: process.env.PAKASIR_SLUG || '',
    apiKey: process.env.PAKASIR_API_KEY || '',
    mode: process.env.PAKASIR_MODE || 'sandbox',
    callbackUrl: process.env.PAKASIR_CALLBACK_URL || 'https://nyooapp.shop/api/payment/pakasir/webhook',
  },
  herosms: {
    apiKey: process.env.HEROSMS_API_KEY || '',
    apiUrl: process.env.HEROSMS_API_URL || 'https://api.hero-sms.com',
  },
  telegram: {
    botToken: process.env.TELEGRAM_BOT_TOKEN || '',
    adminChatId: process.env.TELEGRAM_ADMIN_CHAT_ID || '',
  },
};
