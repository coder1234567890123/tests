import express from 'express';
import * as subjectController from '../controllers/subjectController.js';
import { protect } from '../middleware/authMiddleware.js'; // Assuming you want to protect these

const router = express.Router();

router.post('/', protect, subjectController.createSubject);
router.get('/', protect, subjectController.getAllSubjects);
router.get('/:id', protect, subjectController.getSubjectById);
router.patch('/:id', protect, subjectController.updateSubject);
router.delete('/:id', protect, subjectController.deleteSubject);

export default router;
