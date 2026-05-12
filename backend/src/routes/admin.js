const { Router } = require('express');
const adminController = require('../controllers/adminController');
const { adminAuth } = require('../middleware/auth');
const { loginLimiter } = require('../middleware/rateLimiter');

const router = Router();

router.post('/login', loginLimiter, adminController.login);

router.use(adminAuth);

router.get('/dashboard', adminController.dashboard);
router.get('/users', adminController.getUsers);
router.patch('/users/:id/ban', adminController.banUser);
router.patch('/users/:id/balance', adminController.adjustBalance);
router.get('/orders', adminController.getOrders);
router.get('/deposits', adminController.getDeposits);
router.post('/refund', adminController.refund);
router.get('/services', adminController.getServicesList);
router.post('/services', adminController.createService);
router.put('/services/:id', adminController.updateService);
router.delete('/services/:id', adminController.deleteService);
router.get('/settings', adminController.getSettings);
router.put('/settings', adminController.updateSettings);
router.get('/activity-logs', adminController.getActivityLogs);

module.exports = router;
