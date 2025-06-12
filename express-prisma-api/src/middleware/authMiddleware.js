import jwt from 'jsonwebtoken';
import prisma from '../utils/prismaClient.js';
import dotenv from 'dotenv';

// .env is in express-prisma-api root, this file is in src/middleware/
dotenv.config({ path: '../.env' });

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key'; // Fallback if not in .env

export const protect = async (req, res, next) => {
    let token;
    if (req.headers.authorization && req.headers.authorization.startsWith('Bearer')) {
        try {
            token = req.headers.authorization.split(' ')[1];
            const decoded = jwt.verify(token, JWT_SECRET);

            req.user = await prisma.user.findUnique({
                where: { id: decoded.userId },
                // Include roles and companyId for authorization logic
                select: { id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true, companyId: true }
            });

            if (!req.user) {
                return res.status(401).json({ error: 'Not authorized, user not found.' });
            }
            if (!req.user.enabled) { // Check if user is enabled
                return res.status(403).json({ error: 'Forbidden: User account is disabled.' });
            }
            next();
        } catch (error) {
            console.error('Token verification error:', error.name, error.message);
            if (error.name === 'TokenExpiredError') {
                return res.status(401).json({ error: 'Not authorized, token expired.' });
            }
            if (error.name === 'JsonWebTokenError') {
                return res.status(401).json({ error: 'Not authorized, invalid token.' });
            }
            res.status(401).json({ error: 'Not authorized, token failed.' });
        }
    }
    if (!token) {
        res.status(401).json({ error: 'Not authorized, no token.' });
    }
};

export const authorize = (allowedRoles) => {
  return (req, res, next) => {
    if (!req.user || !Array.isArray(req.user.roles)) { // Ensure roles is an array
      return res.status(403).json({ error: 'Forbidden: User roles not available or malformed.' });
    }
    // Check if user has at least one of the allowed roles
    const hasRole = allowedRoles.some(role => req.user.roles.includes(role));
    if (!hasRole) {
      return res.status(403).json({ error: 'Forbidden: You do not have the required role(s).' });
    }
    next();
  };
};
