const { Router } = require('express');
const depositController = require('../controllers/depositController');
const { userAuth } = require('../middleware/auth');

const router = Router();

router.post('/create', userAuth, depositController.createDeposit);
router.get('/:id', userAuth, depositController.getDeposit);

module.exports = router;
