const { Router } = require('express');
const otpController = require('../controllers/otpController');
const { userAuth } = require('../middleware/auth');

const router = Router();

router.get('/countries', otpController.getCountries);
router.get('/services', otpController.getServices);
router.get('/operators', otpController.getOperators);
router.get('/pricing', userAuth, otpController.getPricing);
router.post('/order', userAuth, otpController.createOrder);
router.get('/order/:id', userAuth, otpController.getOrder);
router.post('/order/:id/cancel', userAuth, otpController.cancelOrder);
router.post('/order/:id/resend', userAuth, otpController.resendOtp);

module.exports = router;
