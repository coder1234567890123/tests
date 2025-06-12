import express from 'express';
import * as dashboardController from '../controllers/dashboardController.js';
import { protect } from '../middleware/authMiddleware.js'; // All dashboard access is protected

const router = express.Router();

router.get('/stats', protect, dashboardController.getDashboardStats);

export default router;
