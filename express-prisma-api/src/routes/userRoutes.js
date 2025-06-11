import express from 'express';
import * as userController from '../controllers/userController.js';
import { protect } from '../middleware/authMiddleware.js'; // Import protect

const router = express.Router();

// router.post('/', userController.createUser); // Should be removed/commented
router.get('/', protect, userController.getAllUsers); // Protect this route
router.get('/:id', protect, userController.getUserById); // Example: protect this too
router.patch('/:id', protect, userController.updateUser); // Example: protect this too
router.delete('/:id', protect, userController.deleteUser); // Example: protect this too

export default router;
