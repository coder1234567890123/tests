import request from 'supertest';
import app from '../../app';
import prisma from '../../src/utils/prismaClient';
import jwt from 'jsonwebtoken';

jest.mock('../../src/utils/prismaClient');

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key';
const generateToken = (userId, roles = ['USER']) => {
  return jwt.sign({ userId, roles }, JWT_SECRET, { expiresIn: '1h' });
};

describe('GlobalWeights Controller', () => {
  let adminToken;
  let userToken;
  let mockWeight;

  beforeEach(() => {
    jest.clearAllMocks();
    adminToken = generateToken('adminId', ['ROLE_SUPER_ADMIN']);
    userToken = generateToken('userId', ['USER']);
    mockWeight = {
      id: 'weight1',
      socialPlatform: 'facebook',
      globalUsageWeighting: 1.5,
      ordering: 1,
      version: 1,
    };
    (prisma.globalWeights.create as jest.Mock).mockReset();
    (prisma.globalWeights.findMany as jest.Mock).mockReset();
    (prisma.globalWeights.findUnique as jest.Mock).mockReset();
    (prisma.globalWeights.update as jest.Mock).mockReset();
    (prisma.globalWeights.delete as jest.Mock).mockReset();
  });

  describe('POST /api/global-weights', () => {
    it('admin should create a global weight successfully', async () => {
      (prisma.globalWeights.create as jest.Mock).mockResolvedValue(mockWeight);
      const res = await request(app)
        .post('/api/global-weights')
        .set('Authorization', `Bearer ${adminToken}`)
        .send({ socialPlatform: 'facebook', globalUsageWeighting: 1.5 });
      expect(res.statusCode).toEqual(201);
      expect(res.body.socialPlatform).toEqual('facebook');
    });

    it('non-admin should be forbidden to create', async () => {
      const res = await request(app)
        .post('/api/global-weights')
        .set('Authorization', `Bearer ${userToken}`)
        .send({ socialPlatform: 'twitter', globalUsageWeighting: 1.0 });
      expect(res.statusCode).toEqual(403);
    });
  });

  describe('GET /api/global-weights', () => {
    it('admin should get all global weights', async () => {
      (prisma.globalWeights.findMany as jest.Mock).mockResolvedValue([mockWeight]);
      const res = await request(app)
        .get('/api/global-weights')
        .set('Authorization', `Bearer ${adminToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body.length).toBe(1);
      expect(res.body[0].socialPlatform).toEqual('facebook');
    });
  });

  // Add tests for GET by ID, PATCH, DELETE, ensuring admin access and handling not found etc.
});
