const { Router } = require('express');
const authRoutes = require('./auth');
const userRoutes = require('./user');
const depositRoutes = require('./deposit');
const webhookRoutes = require('./webhook');
const otpRoutes = require('./otp');
const adminRoutes = require('./admin');
const resellerRoutes = require('./reseller');

const router = Router();

router.use('/auth', authRoutes);
router.use('/user', userRoutes);
router.use('/deposits', depositRoutes);
router.use('/payment', webhookRoutes);
router.use('/otp', otpRoutes);
router.use('/admin', adminRoutes);
router.use('/reseller', resellerRoutes);

router.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

module.exports = router;
