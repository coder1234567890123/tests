import request from 'supertest';
import app from '../../app'; // Assuming app.js default exports the express app
import prisma from '../../src/utils/prismaClient'; // Path to the actual client for mocking
import bcrypt from 'bcryptjs';
import { sendRegistrationEmail, sendPasswordResetEmail } from '../../src/services/emailService'; // For mocking

// Mock Prisma Client
jest.mock('../../src/utils/prismaClient', () => ({
  user: {
    findUnique: jest.fn(),
    create: jest.fn(),
    update: jest.fn(), // Added for password reset flow
    findFirst: jest.fn(), // Added for password reset flow
  },
  // Add other models as needed by other tests, or mock them dynamically
}));

// Mock Email Service
jest.mock('../../src/services/emailService', () => ({
  sendRegistrationEmail: jest.fn(),
  sendPasswordResetEmail: jest.fn(),
}));


describe('Auth Controller', () => {
  beforeEach(() => {
     jest.clearAllMocks();
     // Reset mock implementations if they were changed in a specific test
     prisma.user.findUnique.mockReset();
     prisma.user.create.mockReset();
     prisma.user.update.mockReset();
     prisma.user.findFirst.mockReset();
     sendRegistrationEmail.mockReset();
     sendPasswordResetEmail.mockReset();

  });

  describe('POST /api/auth/register', () => {
    it('should register a new user successfully and send a registration email', async () => {
      prisma.user.findUnique.mockResolvedValue(null);
      const mockNewUser = { id: 'user1', email: 'test@example.com', firstName: 'Test', roles: ['USER'], enabled: true };
      prisma.user.create.mockResolvedValue(mockNewUser);
      sendRegistrationEmail.mockResolvedValue(undefined);

      const res = await request(app)
        .post('/api/auth/register')
        .send({ email: 'test@example.com', password: 'password123', firstName: 'Test' });

      expect(res.statusCode).toEqual(201);
      expect(res.body).toHaveProperty('email', 'test@example.com');
      expect(res.body).not.toHaveProperty('password'); // Ensure password is not returned
      expect(prisma.user.create).toHaveBeenCalledTimes(1);
      expect(sendRegistrationEmail).toHaveBeenCalledWith(mockNewUser);
      expect(sendRegistrationEmail).toHaveBeenCalledTimes(1);
    });

    it('should return 409 if email already exists', async () => {
        prisma.user.create.mockRejectedValue({ code: 'P2002', meta: { target: ['email'] } });

        const res = await request(app)
            .post('/api/auth/register')
            .send({ email: 'existing@example.com', password: 'password123', firstName: 'Test' });
        expect(res.statusCode).toEqual(409);
        expect(res.body).toHaveProperty('error', 'Email already exists.');
    });

    it('should return 400 for missing email or password', async () => {
        const res = await request(app)
            .post('/api/auth/register')
            .send({ firstName: 'Test' }); // Missing email and password
        expect(res.statusCode).toEqual(400);
        expect(res.body).toHaveProperty('error', 'Email and password are required.');
    });
  });

  describe('POST /api/auth/login', () => {
     it('should login an existing user successfully', async () => {
         const hashedPassword = await bcrypt.hash('password123', 10);
         prisma.user.findUnique.mockResolvedValue({
             id: 'user1', email: 'test@example.com', password: hashedPassword,
             roles: ['USER'], enabled: true, companyId: 'comp1', teamId: 'team1'
         });

         const res = await request(app)
             .post('/api/auth/login')
             .send({ email: 'test@example.com', password: 'password123' });
         expect(res.statusCode).toEqual(200);
         expect(res.body).toHaveProperty('token');
         expect(res.body.user).toHaveProperty('email', 'test@example.com');
         expect(res.body.user).not.toHaveProperty('password');
     });

     it('should return 401 for non-existent email', async () => {
        prisma.user.findUnique.mockResolvedValue(null);
        const res = await request(app)
            .post('/api/auth/login')
            .send({ email: 'unknown@example.com', password: 'password123' });
        expect(res.statusCode).toEqual(401);
        expect(res.body).toHaveProperty('error', 'Invalid credentials or account disabled.');
     });

     it('should return 401 for incorrect password', async () => {
        const hashedPassword = await bcrypt.hash('password123', 10);
        prisma.user.findUnique.mockResolvedValue({ id: 'user1', email: 'test@example.com', password: hashedPassword, roles: ['USER'], enabled: true });
        const res = await request(app)
            .post('/api/auth/login')
            .send({ email: 'test@example.com', password: 'wrongpassword' });
        expect(res.statusCode).toEqual(401);
        expect(res.body).toHaveProperty('error', 'Invalid credentials.');
    });

    it('should return 401 for disabled user', async () => {
        const hashedPassword = await bcrypt.hash('password123', 10);
        prisma.user.findUnique.mockResolvedValue({ id: 'user1', email: 'test@example.com', password: hashedPassword, roles: ['USER'], enabled: false });
        const res = await request(app)
            .post('/api/auth/login')
            .send({ email: 'test@example.com', password: 'password123' });
        expect(res.statusCode).toEqual(401);
        expect(res.body).toHaveProperty('error', 'Invalid credentials or account disabled.');
    });
  });

  });

  describe('POST /api/auth/request-password-reset', () => {
    it('should return success message and send email if user exists and is enabled', async () => {
      const mockUser = { id: 'user1', email: 'reset@example.com', firstName: 'Reset', enabled: true };
      prisma.user.findUnique.mockResolvedValue(mockUser);
      prisma.user.update.mockResolvedValue({}); // Mock update for token
      sendPasswordResetEmail.mockResolvedValue(undefined);

      const res = await request(app)
        .post('/api/auth/request-password-reset')
        .send({ email: 'reset@example.com' });

      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('message', 'If your email is registered and account is active, you will receive a password reset link.');
      expect(prisma.user.update).toHaveBeenCalledTimes(1);
      expect(sendPasswordResetEmail).toHaveBeenCalledTimes(1);
    });

    it('should return success message even if user does not exist (to prevent email enumeration)', async () => {
      prisma.user.findUnique.mockResolvedValue(null);

      const res = await request(app)
        .post('/api/auth/request-password-reset')
        .send({ email: 'nonexistent@example.com' });

      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('message', 'If your email is registered and account is active, you will receive a password reset link.');
      expect(prisma.user.update).not.toHaveBeenCalled();
      expect(sendPasswordResetEmail).not.toHaveBeenCalled();
    });

    it('should return success message if user is disabled (to prevent account status enumeration)', async () => {
        prisma.user.findUnique.mockResolvedValue({ id: 'userDisabled', email: 'disabled@example.com', enabled: false });

        const res = await request(app)
            .post('/api/auth/request-password-reset')
            .send({ email: 'disabled@example.com' });

        expect(res.statusCode).toEqual(200);
        expect(res.body).toHaveProperty('message', 'If your email is registered and account is active, you will receive a password reset link.');
        expect(prisma.user.update).not.toHaveBeenCalled(); // Token should not be generated for disabled user
        expect(sendPasswordResetEmail).not.toHaveBeenCalled();
      });

    it('should return 400 if email is not provided', async () => {
        const res = await request(app)
            .post('/api/auth/request-password-reset')
            .send({});
        expect(res.statusCode).toEqual(400);
        expect(res.body).toHaveProperty('error', 'Email is required.');
    });
  });

  describe('POST /api/auth/reset-password/:token', () => {
    const validToken = 'validresettoken123';
    const newPassword = 'newPassword123';

    it('should reset password successfully with a valid token', async () => {
      prisma.user.findFirst.mockResolvedValue({ id: 'user1', email: 'reset@example.com', token: validToken, tokenRequested: new Date(Date.now() + 3600000), enabled: true }); // Token not expired
      (bcrypt.hash as jest.Mock) = jest.fn().mockResolvedValue('hashedNewPassword'); // bcrypt.hash is a function, so this mock assignment is fine
      prisma.user.update.mockResolvedValue({});

      const res = await request(app)
        .post(`/api/auth/reset-password/${validToken}`)
        .send({ newPassword });

      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('message', 'Password has been reset successfully.');
      expect(prisma.user.update).toHaveBeenCalledWith(expect.objectContaining({
        data: { password: 'hashedNewPassword', token: null, tokenRequested: null }
      }));
    });

    it('should return 400 for invalid or expired token', async () => {
      prisma.user.findFirst.mockResolvedValue(null); // Token not found or expired

      const res = await request(app)
        .post('/api/auth/reset-password/invalidtoken')
        .send({ newPassword });

      expect(res.statusCode).toEqual(400);
      expect(res.body).toHaveProperty('error', 'Invalid or expired password reset token.');
    });

    it('should return 400 if new password is too short', async () => {
        const res = await request(app)
            .post(`/api/auth/reset-password/${validToken}`)
            .send({ newPassword: 'short' });
        expect(res.statusCode).toEqual(400);
        expect(res.body).toHaveProperty('error', 'New password must be at least 6 characters long.');
    });

    it('should return 403 if user account is disabled', async () => {
        prisma.user.findFirst.mockResolvedValue({ id: 'user1', email: 'reset@example.com', token: validToken, tokenRequested: new Date(Date.now() + 3600000), enabled: false });

        const res = await request(app)
            .post(`/api/auth/reset-password/${validToken}`)
            .send({ newPassword });

        expect(res.statusCode).toEqual(403);
        expect(res.body).toHaveProperty('error', 'Account is disabled. Cannot reset password.');
    });
  });
});
