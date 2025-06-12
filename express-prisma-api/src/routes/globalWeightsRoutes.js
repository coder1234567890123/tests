import express from 'express';
import * as globalWeightsController from '../controllers/globalWeightsController.js';
import { protect, authorize } from '../middleware/authMiddleware.js';

const router = express.Router();
const adminOnly = authorize(['ROLE_SUPER_ADMIN']); // Define reusable middleware instance

router.post('/', protect, adminOnly, globalWeightsController.createGlobalWeight);
router.get('/', protect, adminOnly, globalWeightsController.getAllGlobalWeights);
router.get('/:id', protect, adminOnly, globalWeightsController.getGlobalWeightById);
router.patch('/:id', protect, adminOnly, globalWeightsController.updateGlobalWeight);
router.delete('/:id', protect, adminOnly, globalWeightsController.deleteGlobalWeight);

export default router;
