const axios = require('axios');
const { pool } = require('../config/database');
const settingsService = require('./settingsService');
const logger = require('../utils/logger');

async function sendMessage(chatId, message) {
  const botToken = await settingsService.get('telegram_bot_token');
  if (!botToken) return false;

  try {
    await axios.post(`https://api.telegram.org/bot${botToken}/sendMessage`, {
      chat_id: chatId,
      text: message,
      parse_mode: 'HTML',
    });
    return true;
  } catch (err) {
    logger.error({ err, chatId }, 'Failed to send Telegram message');
    return false;
  }
}

async function notify(userId, type, message) {
  const [rows] = await pool.query(
    'INSERT INTO telegram_notifications (user_id, type, message) VALUES (?, ?, ?)',
    [userId, type, message]
  );
  const notifId = rows.insertId;

  const settings = await settingsService.getAll();
  const adminChatId = settings.telegram_admin_chat_id;

  if (adminChatId) {
    const sent = await sendMessage(adminChatId, message);
    if (sent) {
      await pool.query('UPDATE telegram_notifications SET is_sent = 1, sent_at = NOW() WHERE id = ?', [notifId]);
    }
  }
}

async function notifyDepositSuccess(userId, username, amount) {
  const msg = `<b>Deposit Berhasil</b>\nUser: ${username}\nJumlah: Rp ${amount.toLocaleString('id-ID')}`;
  await notify(userId, 'deposit_success', msg);
}

async function notifyOtpReceived(userId, username, service, phone, otp) {
  const msg = `<b>OTP Diterima</b>\nUser: ${username}\nLayanan: ${service}\nNomor: ${phone}\nOTP: <code>${otp}</code>`;
  await notify(userId, 'otp_received', msg);
}

async function notifyOrderFailed(userId, username, service, reason) {
  const msg = `<b>Order Gagal</b>\nUser: ${username}\nLayanan: ${service}\nAlasan: ${reason}`;
  await notify(userId, 'order_failed', msg);
}

module.exports = { sendMessage, notify, notifyDepositSuccess, notifyOtpReceived, notifyOrderFailed };
