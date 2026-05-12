const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { z } = require('zod');
const { pool } = require('../config/database');
const config = require('../config');
const { generateReferralCode, generateApiKey } = require('../utils/helpers');
const activityService = require('../services/activityService');
const logger = require('../utils/logger');

const registerSchema = z.object({
  username: z.string().min(3).max(50).regex(/^[a-zA-Z0-9_]+$/),
  email: z.string().email().max(100),
  password: z.string().min(6).max(100),
  referral_code: z.string().optional(),
});

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});

async function register(req, res) {
  try {
    const data = registerSchema.parse(req.body);

    const [existing] = await pool.query(
      'SELECT id FROM users WHERE email = ? OR username = ?',
      [data.email, data.username]
    );
    if (existing.length) {
      return res.status(409).json({ success: false, message: 'Email atau username sudah terdaftar' });
    }

    let referredBy = null;
    if (data.referral_code) {
      const [referrer] = await pool.query(
        'SELECT id FROM users WHERE referral_code = ?',
        [data.referral_code]
      );
      if (referrer.length) referredBy = referrer[0].id;
    }

    const hashedPassword = await bcrypt.hash(data.password, 10);
    const referralCode = generateReferralCode();
    const apiKey = generateApiKey();

    const [result] = await pool.query(
      `INSERT INTO users (username, email, password, referral_code, referred_by, api_key)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [data.username, data.email, hashedPassword, referralCode, referredBy, apiKey]
    );

    const userId = result.insertId;

    await pool.query(
      'INSERT INTO affiliates (user_id) VALUES (?)',
      [userId]
    );

    if (referredBy) {
      await pool.query(
        'UPDATE affiliates SET total_referrals = total_referrals + 1 WHERE user_id = ?',
        [referredBy]
      );
    }

    await activityService.log({
      actorType: 'user',
      actorId: userId,
      action: 'register',
      ipAddress: req.ip,
    });

    const token = jwt.sign({ id: userId, role: 'user' }, config.jwt.secret, {
      expiresIn: config.jwt.expiresIn,
    });

    res.status(201).json({
      success: true,
      message: 'Registrasi berhasil',
      data: { token, user: { id: userId, username: data.username, email: data.email } },
    });
  } catch (err) {
    if (err instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: err.errors });
    }
    logger.error({ err }, 'Register error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function login(req, res) {
  try {
    const data = loginSchema.parse(req.body);

    const [users] = await pool.query('SELECT * FROM users WHERE email = ?', [data.email]);
    if (!users.length) {
      return res.status(401).json({ success: false, message: 'Email atau password salah' });
    }

    const user = users[0];
    if (user.is_banned) {
      return res.status(403).json({ success: false, message: 'Akun Anda telah dibanned', reason: user.ban_reason });
    }

    const valid = await bcrypt.compare(data.password, user.password);
    if (!valid) {
      return res.status(401).json({ success: false, message: 'Email atau password salah' });
    }

    await pool.query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [user.id]);

    await activityService.log({
      actorType: 'user',
      actorId: user.id,
      action: 'login',
      ipAddress: req.ip,
    });

    const token = jwt.sign({ id: user.id, role: user.role }, config.jwt.secret, {
      expiresIn: config.jwt.expiresIn,
    });

    res.json({
      success: true,
      data: {
        token,
        user: {
          id: user.id,
          username: user.username,
          email: user.email,
          balance: user.balance,
          role: user.role,
          language: user.language,
          referral_code: user.referral_code,
        },
      },
    });
  } catch (err) {
    if (err instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: err.errors });
    }
    logger.error({ err }, 'Login error');
    res.status(500).json({ success: false, message: 'Server error' });
  }
}

async function me(req, res) {
  res.json({ success: true, data: { user: req.user } });
}

module.exports = { register, login, me };
