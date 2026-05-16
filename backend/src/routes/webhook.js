const { Router } = require('express');
const depositController = require('../controllers/depositController');

const router = Router();

router.post('/tripay', depositController.webhookTripay);
router.post('/qrispy', depositController.webhookQrispy);

module.exports = router;
