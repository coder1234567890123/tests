import jwt from 'jsonwebtoken';
import prisma from '../utils/prismaClient.js';
import dotenv from 'dotenv';

// Load .env from the root of express-prisma-api project
dotenv.config({ path: '../../.env' });

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key-authmiddleware';

export const protect = async (req, res, next) => {
    let token;
    if (req.headers.authorization && req.headers.authorization.startsWith('Bearer')) {
        try {
            token = req.headers.authorization.split(' ')[1];
            const decoded = jwt.verify(token, JWT_SECRET);

            // Attach user to request object (excluding password)
            req.user = await prisma.user.findUnique({
                where: { id: decoded.userId },
                select: { id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true }
            });

            if (!req.user) {
                return res.status(401).json({ error: 'Not authorized, user not found.' });
            }
            next();
        } catch (error) {
            console.error('Token verification error:', error);
            res.status(401).json({ error: 'Not authorized, token failed.' });
        }
    }
    if (!token) {
        res.status(401).json({ error: 'Not authorized, no token.' });
    }
};
