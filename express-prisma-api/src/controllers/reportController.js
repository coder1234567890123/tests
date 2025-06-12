import prisma from '../utils/prismaClient.js';
import * as pdfService from '../services/pdfService.js';
import * as reportScoreService from '../services/reportScoreService.js';
import * as workflowService from '../services/workflowService.js';
import { sendSms } from '../services/twilioService.js';
import { sendReportAssignedEmail, sendReportStatusUpdateEmail } from '../services/emailService.js'; // New import

export const createReport = async (req, res) => {
  const {
    sequence, subjectId, companyId, requestType, status,
    riskScore, dueDate, completedDate, optionValue, riskComment, assignedToId
  } = req.body;

  if (!sequence || !subjectId || !requestType) {
    return res.status(400).json({ error: 'Sequence, subjectId, and requestType are required.' });
  }

  try {
    const newReport = await prisma.report.create({
      data: {
        sequence, subjectId, companyId, requestType,
        status: status || 'new_request',
        riskScore,
        dueDate: dueDate ? new Date(dueDate) : null,
        completedDate: completedDate ? new Date(completedDate) : null,
        optionValue, riskComment,
        createdById: req.user?.id,
        assignedToId,
      },
      include: {
          subject: { select: { id: true, firstName: true, lastName: true, identification: true }},
          company: { select: { id: true, name: true }},
          assignedTo: { select: { id: true, email: true, firstName: true, primaryMobile: true }},
          createdBy: { select: { id: true, email: true, firstName: true }}
      }
    });

    // Send notifications for new report assignment if assignedToId is present
    if (newReport.assignedToId && newReport.assignedTo && newReport.subject) {
        const assignee = newReport.assignedTo;
        const subjectInfo = newReport.subject;
        const smsMessage = `Hi ${assignee.firstName || 'Analyst'}, you've been assigned Report ${newReport.sequence} for Subject: ${subjectInfo.firstName || ''} ${subjectInfo.lastName || ''}.`;
        if (assignee.primaryMobile) {
            sendSms(assignee.primaryMobile, smsMessage).catch(console.error);
        }
        if (assignee.email) {
            sendReportAssignedEmail(assignee, newReport, subjectInfo).catch(console.error);
        }
    }

    res.status(201).json(newReport);
  } catch (error) {
    console.error('Error creating report:', error);
    if (error.code === 'P2002' && error.meta?.target?.includes('sequence')) {
        return res.status(409).json({ error: 'Report sequence already exists.' });
    }
    res.status(500).json({ error: 'Failed to create report', details: error.message });
  }
};

export const getAllReports = async (req, res) => {
  const {
    status, requestType, companyId, dateFrom, dateTo, search,
    page = 1, limit = 10, sortBy = 'createdAt', sortOrder = 'desc'
  } = req.query;
  const pageNum = parseInt(page as string, 10) || 1;
  const limitNum = parseInt(limit as string, 10) || 10;
  const offset = (pageNum - 1) * limitNum;
  const whereClause: any = {};
  const andConditions = [];
  if (status) andConditions.push({ status: status as string });
  if (requestType) andConditions.push({ requestType: requestType as string });
  if (companyId) andConditions.push({ companyId: companyId as string });
  if (dateFrom) {
    andConditions.push({ createdAt: { ...((andConditions.find(c => c.createdAt)?.createdAt) || {}), gte: new Date(dateFrom as string) } });
  }
  if (dateTo) {
    const existingCreatedAt = andConditions.find(c => c.createdAt)?.createdAt || {};
    const otherConditions = andConditions.filter(c => !c.createdAt);
    andConditions.length = 0;
    andConditions.push(...otherConditions);
    andConditions.push({ createdAt: { ...existingCreatedAt, lte: new Date(new Date(dateTo as string).setHours(23,59,59,999)) } });
  }
  if (search) {
    const searchString = search as string;
    andConditions.push({
      OR: [
        { sequence: { contains: searchString, mode: 'insensitive' } },
        { subject: { OR: [ { firstName: { contains: searchString, mode: 'insensitive' } },{ lastName: { contains: searchString, mode: 'insensitive' } },]}}
      ]
    });
  }
  const user = req.user;
  const isSuperAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
  const isAdminUser = user?.roles?.includes('ROLE_ADMIN_USER');
  if (!isSuperAdmin && !isAdminUser) {
    if (user?.companyId) {
      if (companyId && companyId !== user.companyId) {
        andConditions.push({ companyId: "__USER_DOES_NOT_BELONG_TO_THIS_COMPANY__" });
      } else { andConditions.push({ companyId: user.companyId }); }
    } else {
      andConditions.push({ OR: [{ createdById: user.id },{ assignedToId: user.id }]});
    }
  }
  if (andConditions.length > 0) whereClause.AND = andConditions;
  try {
    const reports = await prisma.report.findMany({
      where: whereClause,
      include: {
         subject: { select: { id: true, firstName: true, lastName: true }},
         company: { select: { id: true, name: true }},
         assignedTo: { select: { id:true, email:true, firstName:true, lastName:true }},
         createdBy: { select: { id:true, email:true, firstName:true, lastName:true }}
      },
      orderBy: { [sortBy as string]: sortOrder as string },
      skip: offset, take: limitNum,
    });
    const totalReports = await prisma.report.count({ where: whereClause });
    const totalPages = Math.ceil(totalReports / limitNum);
    res.status(200).json({
      data: reports, total: totalReports, page: pageNum, limit: limitNum, totalPages,
    });
  } catch (error) {
    console.error('Error fetching reports:', error);
    if (error.code === 'P2023' || error.message?.includes('Order Condition Error')) {
        return res.status(400).json({ error: 'Invalid sort field provided.' });
    }
    res.status(500).json({ error: 'Failed to fetch reports', details: error.message });
  }
};

export const getReportById = async (req, res) => {
  const { id } = req.params;
  try {
    const report = await prisma.report.findUnique({
      where: { id },
      include: {
        subject: { include: { profiles: true, company: true } },
        company: true,
        assignedTo: { select: { id: true, email: true, firstName: true, lastName: true, primaryMobile: true } }, // Added primaryMobile
        approvedBy: { select: { id: true, email: true, firstName: true, lastName: true } },
        createdBy: { select: { id: true, email: true, firstName: true, lastName: true, primaryMobile: true } }, // Added primaryMobile
        answers: {
          orderBy: { createdAt: 'asc' }, // Order answers
          include: {
            question: { orderBy: [{ platform: 'asc'}, {orderNumber: 'asc'}] }, // Order questions if needed
            user: { select: { firstName: true, lastName: true } },
            proofs: { orderBy: { createdAt: 'asc' }, include: { proofStorage: true } } // Order proofs
          }
        },
        questions: { orderBy: [{platform: 'asc'}, {orderNumber: 'asc'}] },
        comments: { // Include comments, ordered
            orderBy: { createdAt: 'asc'},
            include: { commentBy: { select: { id: true, firstName: true, lastName: true, email: true}}}
        }
      }
    });
    if (!report) return res.status(404).json({ error: 'Report not found' });
    res.status(200).json(report);
  } catch (error) {
    console.error(`Error fetching report ${id}:`, error);
    res.status(500).json({ error: 'Failed to fetch report', details: error.message });
  }
};

export const updateReport = async (req, res) => {
  const { id } = req.params;
  const { dueDate, completedDate, assignedToId, ...reportData } = req.body;
  const currentUserId = req.user?.id;

  if (dueDate && typeof dueDate === 'string') reportData.dueDate = new Date(dueDate);
  else if (dueDate === null) reportData.dueDate = null;

  if (completedDate && typeof completedDate === 'string') reportData.completedDate = new Date(completedDate);
  else if (completedDate === null) reportData.completedDate = null;

  try {
    const existingReport = await prisma.report.findUnique({
        where: { id },
        include: {
            assignedTo: { select: { id: true, email: true, firstName: true, primaryMobile: true }},
            subject: {select: {id: true, firstName: true, lastName: true, identification: true}} // For email content
        }
    });
    if (!existingReport) return res.status(404).json({ error: 'Report not found' });

    const updatedReport = await prisma.report.update({
      where: { id },
      data: { ...reportData, assignedToId: assignedToId === undefined ? existingReport.assignedToId : (assignedToId || null) },
      include: {
         subject: { select: { id: true, firstName: true, lastName: true, identification: true }},
         assignedTo: { select: { id: true, email: true, firstName: true, primaryMobile: true }},
         createdBy: { select: { id: true, email: true, firstName: true, primaryMobile: true }}
     }
    });

    if (assignedToId !== undefined && existingReport.assignedToId !== assignedToId && updatedReport.assignedTo && updatedReport.subject) {
      const assignee = updatedReport.assignedTo;
      const subjectInfo = updatedReport.subject;
      const smsMessage = `Hi ${assignee.firstName || 'Analyst'}, you've been assigned Report ${updatedReport.sequence} for Subject: ${subjectInfo.firstName || ''} ${subjectInfo.lastName || ''}.`;
      if (assignee.primaryMobile) {
         sendSms(assignee.primaryMobile, smsMessage).catch(console.error);
      }
      if (assignee.email) {
         sendReportAssignedEmail(assignee, updatedReport, subjectInfo).catch(console.error);
      }
    }

    if (reportData.status && existingReport.status !== reportData.status && updatedReport.createdBy && updatedReport.subject) {
         const creator = updatedReport.createdBy;
         const subjectInfo = updatedReport.subject;
         const statusChangeMsg = `Report ${updatedReport.sequence} for ${subjectInfo.firstName} ${subjectInfo.lastName} status changed from ${existingReport.status} to ${reportData.status}.`;
         if (creator.primaryMobile && creator.id !== currentUserId) { // Don't SMS user for their own action
             sendSms(creator.primaryMobile, statusChangeMsg).catch(console.error);
         }
         if (creator.email && creator.id !== currentUserId) { // Don't email user for their own action
            sendReportStatusUpdateEmail(creator, updatedReport, subjectInfo, existingReport.status || "N/A", reportData.status).catch(console.error);
         }
    }

    res.status(200).json(updatedReport);
  } catch (error) {
    console.error(`Error updating report ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Report not found' });
    res.status(500).json({ error: 'Failed to update report', details: error.message });
  }
};

export const deleteReport = async (req, res) => {
  const { id } = req.params;
  try {
    await prisma.report.delete({ where: { id } });
    res.status(204).send();
  } catch (error) {
    console.error(`Error deleting report ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Report not found' });
    if (error.code === 'P2003') return res.status(409).json({ error: 'Cannot delete report, it has related records (e.g., answers/comments). Consider archiving instead.'});
    res.status(500).json({ error: 'Failed to delete report', details: error.message });
  }
};

export const downloadReportPDF = async (req, res) => {
  const { id } = req.params;
  try {
    const pdfBuffer = await pdfService.generateReportPDF(id);
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader('Content-Disposition', `attachment; filename=report-${id}.pdf`);
    res.send(pdfBuffer);
  } catch (error) {
    console.error(`Error generating PDF for report ${id}:`, error);
    if (error.message.includes('not found')) {
         return res.status(404).json({ error: error.message });
    }
    res.status(500).json({ error: 'Failed to generate PDF report', details: error.message });
  }
};

export const calculateScoresForReport = async (req, res) => {
  const { id } = req.params;
  try {
    const updatedReportWithScores = await reportScoreService.calculateAndSaveReportScores(id);
    res.status(200).json(updatedReportWithScores);
  } catch (error) {
    console.error(`Error calculating scores for report ${id}:`, error);
    if (error.message.includes('not found')) return res.status(404).json({ error: error.message });
    res.status(500).json({ error: 'Failed to calculate scores', details: error.message });
  }
};

export const updateReportStatus = async (req, res) => {
  const { id } = req.params;
  const { newStatus } = req.body;
  const userId = req.user?.id;

  if (!newStatus) {
    return res.status(400).json({ error: 'New status is required.' });
  }

  try {
    const updatedReport = await workflowService.transitionReportStatus(id, newStatus, userId);
    res.status(200).json(updatedReport);
  } catch (error) {
    console.error(`Error transitioning report ${id} to status ${newStatus}:`, error);
    if (error.message.includes('not found') || error.message.includes('not allowed')) {
      return res.status(400).json({ error: error.message });
    }
    res.status(500).json({ error: 'Failed to update report status', details: error.message });
  }
};
