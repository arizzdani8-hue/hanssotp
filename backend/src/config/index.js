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
  tripay: {
    apiKey: process.env.TRIPAY_API_KEY || '',
    privateKey: process.env.TRIPAY_PRIVATE_KEY || '',
    merchantCode: process.env.TRIPAY_MERCHANT_CODE || '',
    apiUrl: process.env.TRIPAY_API_URL || 'https://tripay.co.id/api',
  },
  qrispy: {
    apiKey: process.env.QRISPY_API_KEY || '',
    apiUrl: process.env.QRISPY_API_URL || 'https://api.qrispy.com',
  },
  fivesim: {
    apiKey: process.env.FIVESIM_API_KEY || '',
  },
  herosms: {
    apiKey: process.env.HEROSMS_API_KEY || '',
  },
  nokosmurah: {
    apiKey: process.env.NOKOSMURAH_API_KEY || '',
  },
  telegram: {
    botToken: process.env.TELEGRAM_BOT_TOKEN || '',
    adminChatId: process.env.TELEGRAM_ADMIN_CHAT_ID || '',
  },
};
