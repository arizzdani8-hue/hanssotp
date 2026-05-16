const { Router } = require('express');
const depositController = require('../controllers/depositController');

const router = Router();

router.post('/pakasir', depositController.webhookPakasir);

module.exports = router;
