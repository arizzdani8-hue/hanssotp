const { Router } = require('express');
const userController = require('../controllers/userController');
const { userAuth } = require('../middleware/auth');

const router = Router();

router.use(userAuth);

router.get('/dashboard', userController.dashboard);
router.get('/transactions', userController.transactions);
router.get('/orders', userController.orders);
router.get('/profile', userController.profile);
router.put('/profile', userController.updateProfile);

module.exports = router;
