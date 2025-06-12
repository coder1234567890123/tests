import express from 'express';
import * as answerController from '../controllers/answerController.js';
import { protect } from '../middleware/authMiddleware.js';

const router = express.Router();

router.post('/', protect, answerController.submitAnswer);
router.get('/report/:reportId', protect, answerController.getAnswersForReport);

export default router;
