import express from 'express';
import * as messageSystemController from '../controllers/messageSystemController.js';
import { protect } from '../middleware/authMiddleware.js';

const router = express.Router();

router.get('/my', protect, messageSystemController.getUserMessages);
router.get('/my/unread-count', protect, messageSystemController.getUnreadMessageCount);
router.patch('/:messageId/read', protect, messageSystemController.markMessageAsRead);
// POST route for creating messages is removed as it's handled by the service internally for now

export default router;
