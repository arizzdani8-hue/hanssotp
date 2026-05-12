require('dotenv').config();

const http = require('http');
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const { apiLimiter } = require('./src/middleware/rateLimiter');
const config = require('./src/config');
const { testConnection } = require('./src/config/database');
const routes = require('./src/routes');
const { init: initWebSocket } = require('./src/websocket');
const { startOtpPoller } = require('./src/jobs/otpPoller');
const { startAutoCancel } = require('./src/jobs/autoCancel');
const { startAutoPricing } = require('./src/jobs/autoPricing');
const logger = require('./src/utils/logger');

const app = express();
const server = http.createServer(app);

app.use(helmet({ crossOriginResourcePolicy: { policy: 'cross-origin' } }));
app.use(cors({ origin: config.cors.origin, credentials: true }));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

app.use('/api', apiLimiter);

app.use('/api', routes);

app.use((err, req, res, _next) => {
  logger.error({ err }, 'Unhandled error');
  res.status(500).json({ success: false, message: 'Internal server error' });
});

async function start() {
  await testConnection();

  initWebSocket(server);

  startOtpPoller();
  startAutoCancel();
  startAutoPricing();

  server.listen(config.port, () => {
    logger.info({ port: config.port, env: config.nodeEnv }, 'HanssOTP server started');
  });
}

start().catch((err) => {
  logger.error({ err }, 'Failed to start server');
  process.exit(1);
});
