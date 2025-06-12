import express from 'express';
import * as reportController from '../controllers/reportController.js';
import { protect } from '../middleware/authMiddleware.js';

const router = express.Router();

router.post('/', protect, reportController.createReport);
router.get('/', protect, reportController.getAllReports);
router.get('/:id', protect, reportController.getReportById);
router.patch('/:id', protect, reportController.updateReport);
router.delete('/:id', protect, reportController.deleteReport);
router.get('/:id/pdf', protect, reportController.downloadReportPDF);
router.post('/:id/calculate-scores', protect, reportController.calculateScoresForReport);
router.post('/:id/status', protect, reportController.updateReportStatus); // New route for status changes

export default router;
