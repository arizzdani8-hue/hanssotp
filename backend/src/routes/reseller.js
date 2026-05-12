const { Router } = require('express');
const resellerController = require('../controllers/resellerController');
const { resellerAuth } = require('../middleware/auth');
const { resellerLimiter } = require('../middleware/rateLimiter');

const router = Router();

router.use(resellerAuth);
router.use(resellerLimiter);

router.get('/balance', resellerController.getBalance);
router.get('/services', resellerController.getServicesList);
router.post('/order', resellerController.createOrder);
router.get('/order/:id', resellerController.getOrder);
router.post('/order/:id/cancel', resellerController.cancelOrder);

module.exports = router;
