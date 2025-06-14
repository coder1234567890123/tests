import request from 'supertest';
import app from '../../app';
import prisma from '../../src/utils/prismaClient';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken'; // To generate mock tokens

jest.mock('../../src/utils/prismaClient');

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key';

const generateToken = (userId, roles = ['USER'], companyId = null, teamId = null) => {
  return jwt.sign({ userId, roles, companyId, teamId }, JWT_SECRET, { expiresIn: '1h' });
};

describe('User Controller', () => {
  let mockUser;
  let adminToken;
  let userToken;

  beforeEach(() => {
    jest.clearAllMocks();
    mockUser = {
        id: 'user1', email: 'user@example.com', firstName: 'Test', lastName: 'User',
        roles: ['USER'], enabled: true, companyId: 'comp1', teamId: 'team1',
        password: 'hashedPassword' // For changeMyPassword test
    };
    adminToken = generateToken('adminUserId', ['ROLE_SUPER_ADMIN']);
    userToken = generateToken(mockUser.id, mockUser.roles, mockUser.companyId, mockUser.teamId);

    (prisma.user.findUnique as jest.Mock).mockReset();
    (prisma.user.findMany as jest.Mock).mockReset();
    (prisma.user.count as jest.Mock).mockReset();
    (prisma.user.update as jest.Mock).mockReset();
    (prisma.user.delete as jest.Mock).mockReset();
  });

  describe('GET /api/users/me/profile', () => {
    it('should get current user profile successfully', async () => {
      (prisma.user.findUnique as jest.Mock).mockResolvedValue(mockUser);
      const res = await request(app)
        .get('/api/users/me/profile')
        .set('Authorization', `Bearer ${userToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('id', mockUser.id);
      expect(res.body).not.toHaveProperty('password');
    });

    it('should return 401 if not authenticated', async () => {
      const res = await request(app).get('/api/users/me/profile');
      expect(res.statusCode).toEqual(401);
    });
  });

  describe('PATCH /api/users/me/profile', () => {
    it('should update current user profile successfully', async () => {
      const updatedData = { firstName: 'UpdatedFirst', lastName: 'UpdatedLast' };
      (prisma.user.update as jest.Mock).mockResolvedValue({ ...mockUser, ...updatedData });

      const res = await request(app)
        .patch('/api/users/me/profile')
        .set('Authorization', `Bearer ${userToken}`)
        .send(updatedData);

      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('firstName', 'UpdatedFirst');
      expect(prisma.user.update).toHaveBeenCalledWith({
        where: { id: mockUser.id },
        data: updatedData,
        select: expect.any(Object)
      });
    });
    // Add test for no fields provided, email already in use
  });

  describe('POST /api/users/me/change-password', () => {
    it('should change current user password successfully', async () => {
        (prisma.user.findUnique as jest.Mock).mockResolvedValue(mockUser);
        (bcrypt.compare as jest.Mock) = jest.fn().mockResolvedValue(true);
        (bcrypt.hash as jest.Mock) = jest.fn().mockResolvedValue('newHashedPassword');
        (prisma.user.update as jest.Mock).mockResolvedValue({});

        const res = await request(app)
            .post('/api/users/me/change-password')
            .set('Authorization', `Bearer ${userToken}`)
            .send({ currentPassword: 'password123', newPassword: 'newPassword456' });

        expect(res.statusCode).toEqual(200);
        expect(res.body).toHaveProperty('message', 'Password changed successfully.');
    });
    // Add test for incorrect current password, password too short
  });

  describe('Admin User Management (GET /, GET /:id, PATCH /:id, DELETE /:id)', () => {
    it('GET /api/users - admin should get all users', async () => {
        (prisma.user.findMany as jest.Mock).mockResolvedValue([mockUser]);
        (prisma.user.count as jest.Mock).mockResolvedValue(1);
        const res = await request(app)
            .get('/api/users')
            .set('Authorization', `Bearer ${adminToken}`);
        expect(res.statusCode).toEqual(200);
        expect(res.body.data[0]).toHaveProperty('id', mockUser.id);
    });

    it('GET /api/users - non-admin should be forbidden', async () => {
        const res = await request(app)
            .get('/api/users')
            .set('Authorization', `Bearer ${userToken}`); // Regular user token
        expect(res.statusCode).toEqual(403); // Forbidden due to authorize middleware
    });

    it('PATCH /api/users/:id - admin should update any user', async () => {
        const updates = { firstName: "AdminUpdated" };
        (prisma.user.update as jest.Mock).mockResolvedValue({ ...mockUser, ...updates });
        const res = await request(app)
            .patch(`/api/users/${mockUser.id}`)
            .set('Authorization', `Bearer ${adminToken}`)
            .send(updates);
        expect(res.statusCode).toEqual(200);
        expect(res.body.firstName).toEqual("AdminUpdated");
    });

    it('DELETE /api/users/:id - admin should delete any user', async () => {
        (prisma.user.delete as jest.Mock).mockResolvedValue({});
        const res = await request(app)
            .delete(`/api/users/${mockUser.id}`)
            .set('Authorization', `Bearer ${adminToken}`);
        expect(res.statusCode).toEqual(204);
    });
  });
});
