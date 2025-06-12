import express from 'express';
import * as authController from '../controllers/authController.js';

const router = express.Router();

router.post('/register', authController.registerUser);
router.post('/login', authController.loginUser);
router.post('/request-password-reset', authController.requestPasswordReset);
router.post('/reset-password/:token', authController.resetPasswordWithToken);

export default router;
