import express from 'express';
import * as systemConfigController from '../controllers/systemConfigController.js';
import { protect, authorize } from '../middleware/authMiddleware.js';

const router = express.Router();
const adminOnly = authorize(['ROLE_SUPER_ADMIN']);

router.post('/', protect, adminOnly, systemConfigController.createSystemConfig);
router.get('/', protect, adminOnly, systemConfigController.getAllSystemConfigs);
router.get('/:key', protect, adminOnly, systemConfigController.getSystemConfigByKey); // Using 'key' for 'opt'
router.patch('/:key', protect, adminOnly, systemConfigController.updateSystemConfig);
router.delete('/:key', protect, adminOnly, systemConfigController.deleteSystemConfig);

export default router;
