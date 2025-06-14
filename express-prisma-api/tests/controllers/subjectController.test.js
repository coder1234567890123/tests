import request from 'supertest';
import app from '../../app';
import prisma from '../../src/utils/prismaClient';
import { sendGenericNotificationEmail } from '../../src/services/emailService';
import jwt from 'jsonwebtoken';

jest.mock('../../src/utils/prismaClient');
jest.mock('../../src/services/emailService');

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key';
const generateToken = (userId, roles = ['USER'], companyId = null) => {
  return jwt.sign({ userId, roles, companyId }, JWT_SECRET, { expiresIn: '1h' });
};

describe('Subject Controller', () => {
  let adminToken;
  let userToken;
  let mockSubject;

  beforeEach(() => {
    jest.clearAllMocks();
    adminToken = generateToken('adminId', ['ROLE_SUPER_ADMIN']);
    userToken = generateToken('userId', ['USER'], 'comp1');
    mockSubject = {
      id: 'subj1', firstName: 'John', lastName: 'Doe', identification: 'ID123',
      reportType: 'standard', status: 'new_subject', companyId: 'comp1', createdById: 'userId',
      company: { id: 'comp1', name: 'TestCo' },
      createdBy: { id: 'userId', firstName: 'Test', lastName: 'User' },
      createdAt: new Date().toISOString(),
    };

    (prisma.subject.create as jest.Mock).mockReset();
    (prisma.subject.findMany as jest.Mock).mockReset();
    (prisma.subject.count as jest.Mock).mockReset();
    (prisma.subject.findUnique as jest.Mock).mockReset();
    (prisma.subject.update as jest.Mock).mockReset();
    (prisma.subject.delete as jest.Mock).mockReset();
    (sendGenericNotificationEmail as jest.Mock).mockReset();
    (prisma.user.findMany as jest.Mock).mockReset(); // For admin notification in createSubject
  });

  describe('POST /api/subjects', () => {
    it('should create a subject successfully and notify admins', async () => {
      (prisma.subject.create as jest.Mock).mockResolvedValue(mockSubject);
      (prisma.user.findMany as jest.Mock).mockResolvedValue([{ id: 'admin1', email: 'admin@example.com' }]); // Mock admin for notification
      (sendGenericNotificationEmail as jest.Mock).mockResolvedValue(undefined);

      const subjectData = {
        firstName: 'John', lastName: 'Doe', identification: 'ID123', reportType: 'standard',
        email: 'john.doe@example.com', phone: '+12345' // Example additional fields from createSubject body
      };
      const res = await request(app)
        .post('/api/subjects')
        .set('Authorization', `Bearer ${userToken}`)
        .send(subjectData);

      expect(res.statusCode).toEqual(201);
      expect(res.body).toHaveProperty('identification', 'ID123');
      expect(prisma.subject.create).toHaveBeenCalledTimes(1);
      expect(sendGenericNotificationEmail).toHaveBeenCalledTimes(1); // Assuming admin is different from creator
    });
    // Add test for missing required fields
  });

  describe('GET /api/subjects', () => {
    it('admin should get all subjects with pagination', async () => {
      (prisma.subject.findMany as jest.Mock).mockResolvedValue([mockSubject]);
      (prisma.subject.count as jest.Mock).mockResolvedValue(1);
      const res = await request(app)
        .get('/api/subjects')
        .set('Authorization', `Bearer ${adminToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body.data[0]).toHaveProperty('id', mockSubject.id);
      expect(res.body).toHaveProperty('totalPages', 1);
    });
    // Add test for non-admin restricted view
  });

  describe('GET /api/subjects/:id', () => {
    it('should get a subject by ID', async () => {
      (prisma.subject.findUnique as jest.Mock).mockResolvedValue(mockSubject);
      const res = await request(app)
        .get(`/api/subjects/${mockSubject.id}`)
        .set('Authorization', `Bearer ${userToken}`); // Assuming user can fetch if related or admin
      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('id', mockSubject.id);
    });
  });

  describe('PATCH /api/subjects/:id', () => {
    it('should update a subject successfully', async () => {
      const updatedData = { firstName: "Jane" };
      (prisma.subject.update as jest.Mock).mockResolvedValue({ ...mockSubject, ...updatedData });
      const res = await request(app)
        .patch(`/api/subjects/${mockSubject.id}`)
        .set('Authorization', `Bearer ${adminToken}`) // Assuming admin/creator can update
        .send(updatedData);
      expect(res.statusCode).toEqual(200);
      expect(res.body.firstName).toEqual("Jane");
    });
  });

  describe('DELETE /api/subjects/:id', () => {
    it('should delete a subject successfully', async () => {
      (prisma.subject.delete as jest.Mock).mockResolvedValue({});
      const res = await request(app)
        .delete(`/api/subjects/${mockSubject.id}`)
        .set('Authorization', `Bearer ${adminToken}`);
      expect(res.statusCode).toEqual(204);
    });
  });
});
