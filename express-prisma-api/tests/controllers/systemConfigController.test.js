import request from 'supertest';
import app from '../../app';
import prisma from '../../src/utils/prismaClient';
import jwt from 'jsonwebtoken';

jest.mock('../../src/utils/prismaClient');

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key';
const generateToken = (userId, roles = ['USER']) => {
  return jwt.sign({ userId, roles }, JWT_SECRET, { expiresIn: '1h' });
};

describe('SystemConfig Controller', () => {
  let adminToken;
  let userToken;
  let mockConfig;

  beforeEach(() => {
    jest.clearAllMocks();
    adminToken = generateToken('adminId', ['ROLE_SUPER_ADMIN']);
    userToken = generateToken('userId', ['USER']);
    mockConfig = {
      id: 'config1',
      opt: 'test_option',
      val: 'test_value',
      systemType: 1,
    };
    (prisma.systemConfig.create as jest.Mock).mockReset();
    (prisma.systemConfig.findMany as jest.Mock).mockReset();
    (prisma.systemConfig.findUnique as jest.Mock).mockReset();
    (prisma.systemConfig.update as jest.Mock).mockReset();
    (prisma.systemConfig.delete as jest.Mock).mockReset();
  });

  describe('POST /api/system-configs', () => {
    it('admin should create a system config successfully', async () => {
      (prisma.systemConfig.create as jest.Mock).mockResolvedValue(mockConfig);
      const res = await request(app)
        .post('/api/system-configs')
        .set('Authorization', `Bearer ${adminToken}`)
        .send({ opt: 'test_option', val: 'test_value', systemType: 1 });
      expect(res.statusCode).toEqual(201);
      expect(res.body.opt).toEqual('test_option');
    });

    it('non-admin should be forbidden to create', async () => {
      const res = await request(app)
        .post('/api/system-configs')
        .set('Authorization', `Bearer ${userToken}`)
        .send({ opt: 'another_option', val: 'value' });
      expect(res.statusCode).toEqual(403);
    });
  });

  describe('GET /api/system-configs', () => {
    it('admin should get all system configs', async () => {
      (prisma.systemConfig.findMany as jest.Mock).mockResolvedValue([mockConfig]);
      const res = await request(app)
        .get('/api/system-configs')
        .set('Authorization', `Bearer ${adminToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body.length).toBe(1);
      expect(res.body[0].opt).toEqual('test_option');
    });
  });

  // Add tests for GET by Key, PATCH by Key, DELETE by Key, ensuring admin access and handling not found etc.
});
