const { Server } = require('socket.io');
const jwt = require('jsonwebtoken');
const config = require('../config');
const logger = require('../utils/logger');

let io;

function init(server) {
  io = new Server(server, {
    cors: {
      origin: config.cors.origin,
      methods: ['GET', 'POST'],
    },
    path: '/socket.io',
  });

  io.use((socket, next) => {
    const token = socket.handshake.auth?.token || socket.handshake.query?.token;
    if (!token) {
      return next(new Error('Authentication required'));
    }
    try {
      const decoded = jwt.verify(token, config.jwt.secret);
      socket.userId = decoded.id;
      next();
    } catch {
      next(new Error('Invalid token'));
    }
  });

  io.on('connection', (socket) => {
    const userId = socket.userId;
    socket.join(`user:${userId}`);
    logger.info({ userId }, 'WebSocket client connected');

    socket.on('subscribe:order', (orderId) => {
      socket.join(`order:${orderId}`);
    });

    socket.on('unsubscribe:order', (orderId) => {
      socket.leave(`order:${orderId}`);
    });

    socket.on('disconnect', () => {
      logger.info({ userId }, 'WebSocket client disconnected');
    });
  });

  logger.info('WebSocket server initialized');
  return io;
}

function getIO() {
  return io;
}

function emitOtpReceived(userId, orderId, data) {
  if (!io) return;
  io.to(`user:${userId}`).emit('otp:received', { orderId, ...data });
  io.to(`order:${orderId}`).emit('otp:received', { orderId, ...data });
}

function emitOrderUpdate(userId, orderId, data) {
  if (!io) return;
  io.to(`user:${userId}`).emit('order:update', { orderId, ...data });
  io.to(`order:${orderId}`).emit('order:update', { orderId, ...data });
}

function emitBalanceUpdate(userId, balance) {
  if (!io) return;
  io.to(`user:${userId}`).emit('balance:update', { balance });
}

function emitDepositUpdate(userId, depositId, data) {
  if (!io) return;
  io.to(`user:${userId}`).emit('deposit:update', { depositId, ...data });
}

module.exports = { init, getIO, emitOtpReceived, emitOrderUpdate, emitBalanceUpdate, emitDepositUpdate };
