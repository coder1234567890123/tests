import express from 'express';
import * as proofController from '../controllers/proofController.js';
import { protect } from '../middleware/authMiddleware.js';

const router = express.Router();

// Apply multer middleware for file upload before createProof controller
router.post('/', protect, proofController.uploadProofFile, proofController.createProof);
router.get('/answer/:answerId', protect, proofController.getProofsForAnswer);
router.patch('/:id', protect, proofController.updateProof); // Can also have file upload if needed for updates
router.delete('/:id', protect, proofController.deleteProof);

export default router;
