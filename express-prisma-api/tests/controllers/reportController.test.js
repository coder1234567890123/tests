import request from 'supertest';
import app from '../../app';
import prisma from '../../src/utils/prismaClient';
import * as workflowService from '../../src/services/workflowService';
import * as pdfService from '../../src/services/pdfService';
import * as reportScoreService from '../../src/services/reportScoreService';
import jwt from 'jsonwebtoken';

jest.mock('../../src/utils/prismaClient');
jest.mock('../../src/services/workflowService');
jest.mock('../../src/services/pdfService');
jest.mock('../../src/services/reportScoreService');
// Email and SMS services are indirectly tested via workflow/report updates if those trigger them

const JWT_SECRET = process.env.JWT_SECRET || 'your-default-secret-key';
const generateToken = (userId, roles = ['USER'], companyId = null) => {
  return jwt.sign({ userId, roles, companyId }, JWT_SECRET, { expiresIn: '1h' });
};

describe('Report Controller', () => {
  let adminToken;
  let userToken;
  let mockReport;

  beforeEach(() => {
    jest.clearAllMocks();
    adminToken = generateToken('adminId', ['ROLE_SUPER_ADMIN']);
    userToken = generateToken('userId', ['USER'], 'comp1');
    mockReport = {
      id: 'report1', sequence: 'RPT-001', status: 'new_request', requestType: 'standard',
      subjectId: 'subj1', companyId: 'comp1', createdById: 'userId',
      subject: { id: 'subj1', firstName: 'Test', lastName: 'Subject' },
      company: { id: 'comp1', name: 'Test Company' },
      createdBy: { id: 'userId', firstName: 'Test', lastName: 'User' },
      assignedTo: null,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    };

    (prisma.report.create as jest.Mock).mockReset();
    (prisma.report.findMany as jest.Mock).mockReset();
    (prisma.report.count as jest.Mock).mockReset();
    (prisma.report.findUnique as jest.Mock).mockReset();
    (prisma.report.update as jest.Mock).mockReset();
    (prisma.report.delete as jest.Mock).mockReset();
    (workflowService.transitionReportStatus as jest.Mock).mockReset();
    (pdfService.generateReportPDF as jest.Mock).mockReset();
    (reportScoreService.calculateAndSaveReportScores as jest.Mock).mockReset();
  });

  describe('POST /api/reports', () => {
    it('should create a report successfully', async () => {
      (prisma.report.create as jest.Mock).mockResolvedValue(mockReport);
      const reportData = { sequence: 'RPT-002', subjectId: 'subj2', requestType: 'basic', companyId: 'comp1' };
      const res = await request(app)
        .post('/api/reports')
        .set('Authorization', `Bearer ${userToken}`)
        .send(reportData);
      expect(res.statusCode).toEqual(201);
      expect(res.body).toHaveProperty('sequence', 'RPT-002');
      expect(prisma.report.create).toHaveBeenCalledTimes(1);
    });
    // Add test for missing required fields
  });

  describe('GET /api/reports', () => {
    it('should get all reports with pagination and default sort', async () => {
      (prisma.report.findMany as jest.Mock).mockResolvedValue([mockReport]);
      (prisma.report.count as jest.Mock).mockResolvedValue(1);
      const res = await request(app)
        .get('/api/reports')
        .set('Authorization', `Bearer ${adminToken}`); // Admin can see all
      expect(res.statusCode).toEqual(200);
      expect(res.body.data[0]).toHaveProperty('id', mockReport.id);
      expect(res.body).toHaveProperty('totalPages', 1);
    });
    // Add test for filters (e.g., status, search)
    // Add test for role-based access (non-admin only sees their reports/company reports)
  });

  describe('GET /api/reports/:id', () => {
    it('should get a report by ID', async () => {
      (prisma.report.findUnique as jest.Mock).mockResolvedValue(mockReport);
      const res = await request(app)
        .get(`/api/reports/${mockReport.id}`)
        .set('Authorization', `Bearer ${userToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('id', mockReport.id);
    });
    // Add test for report not found
  });

  describe('PATCH /api/reports/:id', () => {
    it('should update a report successfully', async () => {
      const updatedData = { status: 'search_started' };
      // Mock findUnique for existingReport check, then update
      (prisma.report.findUnique as jest.Mock).mockResolvedValue(mockReport);
      (prisma.report.update as jest.Mock).mockResolvedValue({ ...mockReport, ...updatedData });

      const res = await request(app)
        .patch(`/api/reports/${mockReport.id}`)
        .set('Authorization', `Bearer ${userToken}`)
        .send(updatedData);
      expect(res.statusCode).toEqual(200);
      expect(res.body.status).toEqual('search_started');
    });
  });

  describe('DELETE /api/reports/:id', () => {
    it('should delete a report successfully', async () => {
      (prisma.report.delete as jest.Mock).mockResolvedValue({});
      const res = await request(app)
        .delete(`/api/reports/${mockReport.id}`)
        .set('Authorization', `Bearer ${adminToken}`); // Typically admin
      expect(res.statusCode).toEqual(204);
    });
  });

  describe('POST /api/reports/:id/status', () => {
    it('should update report status via workflow service', async () => {
      (workflowService.transitionReportStatus as jest.Mock).mockResolvedValue({ ...mockReport, status: 'search_started' });
      const res = await request(app)
        .post(`/api/reports/${mockReport.id}/status`)
        .set('Authorization', `Bearer ${userToken}`)
        .send({ newStatus: 'search_started' });
      expect(res.statusCode).toEqual(200);
      expect(res.body.status).toEqual('search_started');
      expect(workflowService.transitionReportStatus).toHaveBeenCalledWith(mockReport.id, 'search_started', 'userId');
    });
  });

  describe('GET /api/reports/:id/pdf', () => {
    it('should download a PDF report', async () => {
      (pdfService.generateReportPDF as jest.Mock).mockResolvedValue(Buffer.from('pdf content'));
      const res = await request(app)
        .get(`/api/reports/${mockReport.id}/pdf`)
        .set('Authorization', `Bearer ${userToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.headers['content-type']).toEqual('application/pdf');
    });
  });

  describe('POST /api/reports/:id/calculate-scores', () => {
    it('should trigger score calculation', async () => {
      (reportScoreService.calculateAndSaveReportScores as jest.Mock).mockResolvedValue({ ...mockReport, riskScore: 50.0 });
      const res = await request(app)
        .post(`/api/reports/${mockReport.id}/calculate-scores`)
        .set('Authorization', `Bearer ${userToken}`);
      expect(res.statusCode).toEqual(200);
      expect(res.body).toHaveProperty('riskScore', 50.0);
    });
  });
});
