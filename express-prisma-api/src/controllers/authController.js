import prisma from '../utils/prismaClient.js';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
import { sendRegistrationEmail, sendPasswordResetEmail } from '../services/emailService.js'; // Import email service
import { randomBytes } from 'crypto'; // For generating password reset token

// .env is in express-prisma-api root, this file is in src/controllers/
dotenv.config({ path: '../.env' });

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key-authcontroller';

export const registerUser = async (req, res) => {
    const { email, password, firstName, lastName, roles } = req.body;
    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password are required.' });
    }
    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        const newUser = await prisma.user.create({
            data: {
                email,
                password: hashedPassword,
                firstName,
                lastName,
                roles: roles || ['USER'] // Default role
            },
        });

        // Send registration email (fire and forget, don't block response)
        sendRegistrationEmail(newUser).catch(console.error);

        // Exclude password from the response
        const { password: _, ...userWithoutPassword } = newUser;
        res.status(201).json(userWithoutPassword);
    } catch (error) {
        console.error('Error registering user:', error);
        if (error.code === 'P2002' && error.meta?.target?.includes('email')) {
            return res.status(409).json({ error: 'Email already exists.' });
        }
        res.status(500).json({ error: 'Failed to register user', details: error.message });
    }
};

export const loginUser = async (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password are required.' });
    }
    try {
        const user = await prisma.user.findUnique({ where: { email } });
        if (!user || !user.enabled) { // Also check if user is enabled
            return res.status(401).json({ error: 'Invalid credentials or account disabled.' });
        }

        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) {
            return res.status(401).json({ error: 'Invalid credentials.' });
        }

        const token = jwt.sign({ userId: user.id, roles: user.roles, companyId: user.companyId, teamId: user.teamId }, JWT_SECRET, { expiresIn: '1h' });

        const { password: _, ...userWithoutPassword } = user;
        res.status(200).json({ token, user: userWithoutPassword });

    } catch (error) {
        console.error('Error logging in user:', error);
        res.status(500).json({ error: 'Failed to login', details: error.message });
    }
};

export const requestPasswordReset = async (req, res) => {
   const { email } = req.body;
   if (!email) return res.status(400).json({ error: 'Email is required.' });

   try {
       const user = await prisma.user.findUnique({ where: { email } });
       if (user && user.enabled) { // Only if user exists and is enabled
           const token = randomBytes(32).toString('hex');
           const expires = new Date(Date.now() + 3600000); // 1 hour from now

           await prisma.user.update({
               where: { id: user.id },
               data: {
                   token: token,
                   tokenRequested: expires
               },
           });

           sendPasswordResetEmail(user, token).catch(console.error);
       }
       res.status(200).json({ message: 'If your email is registered and account is active, you will receive a password reset link.' });
   } catch (error) {
       console.error('Error requesting password reset:', error);
       // Generic error to avoid leaking info, but log specific error
       res.status(500).json({ error: 'Failed to process password reset request.' });
   }
};

export const resetPasswordWithToken = async (req, res) => {
   const { token } = req.params;
   const { newPassword } = req.body;

   if (!newPassword || newPassword.length < 6) {
       return res.status(400).json({ error: 'New password must be at least 6 characters long.' });
   }

   try {
       const user = await prisma.user.findFirst({
           where: {
               token: token,
               tokenRequested: { gte: new Date() }
           }
       });

       if (!user) {
           return res.status(400).json({ error: 'Invalid or expired password reset token.' });
       }
       if (!user.enabled) {
           return res.status(403).json({ error: 'Account is disabled. Cannot reset password.' });
       }

       const hashedNewPassword = await bcrypt.hash(newPassword, 10);
       await prisma.user.update({
           where: { id: user.id },
           data: {
               password: hashedNewPassword,
               token: null,
               tokenRequested: null
           },
       });
       res.status(200).json({ message: 'Password has been reset successfully.' });
   } catch (error) {
       console.error('Error resetting password:', error);
       res.status(500).json({ error: 'Failed to reset password.' });
   }
};
