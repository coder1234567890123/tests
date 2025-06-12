import express from 'express';
import * as userController from '../controllers/userController.js';
import { protect, authorize } from '../middleware/authMiddleware.js'; // Import authorize

const router = express.Router();
const superAdminOnly = authorize(['ROLE_SUPER_ADMIN']);

// Admin routes for managing any user
router.get('/', protect, superAdminOnly, userController.getAllUsers);
router.get('/:id', protect, superAdminOnly, userController.getUserById); // Admin getting any user
router.patch('/:id', protect, superAdminOnly, userController.updateUser);
router.delete('/:id', protect, superAdminOnly, userController.deleteUser);

// Routes for current user's own profile
router.get('/me/profile', protect, userController.getMyProfile);
router.patch('/me/profile', protect, userController.updateMyProfile);
router.post('/me/change-password', protect, userController.changeMyPassword);

export default router;
