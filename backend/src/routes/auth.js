const { Router } = require('express');
const authController = require('../controllers/authController');
const { userAuth } = require('../middleware/auth');
const { loginLimiter } = require('../middleware/rateLimiter');

const router = Router();

router.post('/register', authController.register);
router.post('/login', loginLimiter, authController.login);
router.get('/me', userAuth, authController.me);

module.exports = router;
