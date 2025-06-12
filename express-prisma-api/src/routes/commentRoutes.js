import express from 'express';
import * as commentController from '../controllers/commentController.js';
import { protect } from '../middleware/authMiddleware.js';

const router = express.Router();

router.post('/', protect, commentController.createComment);
router.get('/report/:reportId', protect, commentController.getCommentsForReport);
// router.get('/answer/:answerId', protect, commentController.getCommentsForAnswer); // If implemented in controller & schema
router.patch('/:id', protect, commentController.updateComment);
router.delete('/:id', protect, commentController.deleteComment);

export default router;
