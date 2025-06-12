import { transitionReportStatus, REPORT_STATUSES } from '../../src/services/workflowService';
import prisma from '../../src/utils/prismaClient';
import { createSystemMessage } from '../../src/services/messageSystemService';
import { sendReportStatusUpdateEmail } from '../../src/services/emailService';
import { sendSms } from '../../src/services/twilioService';

jest.mock('../../src/utils/prismaClient');
jest.mock('../../src/services/messageSystemService');
jest.mock('../../src/services/emailService');
jest.mock('../../src/services/twilioService');

describe('Workflow Service - transitionReportStatus', () => {
  const mockReport = {
    id: 'report1',
    status: REPORT_STATUSES.NEW_REQUEST,
    subjectId: 'subj1',
    sequence: 'RPT-001',
    subject: { id: 'subj1', firstName: 'Test', lastName: 'Subject'},
    createdBy: { id: 'creator1', email: 'creator@test.com', primaryMobile: '+1234567890'},
    assignedTo: null, // Not assigned yet
  };

  beforeEach(() => {
    (prisma.report.findUnique as jest.Mock).mockReset();
    (prisma.report.update as jest.Mock).mockReset();
    (prisma.subject.update as jest.Mock).mockReset();
    (createSystemMessage as jest.Mock).mockReset();
    (sendReportStatusUpdateEmail as jest.Mock).mockReset();
    (sendSms as jest.Mock).mockReset();
  });

  it('should allow a valid status transition and update report and subject status', async () => {
    (prisma.report.findUnique as jest.Mock).mockResolvedValue(mockReport);
    (prisma.report.update as jest.Mock).mockImplementation(async ({ where, data }) => ({
      ...mockReport,
      status: data.status,
      updatedAt: data.updatedAt,
      // Return the full report structure expected by notification logic
      createdBy: mockReport.createdBy,
      assignedTo: mockReport.assignedTo,
      subject: mockReport.subject,
      sequence: mockReport.sequence,
    }));
    (prisma.subject.update as jest.Mock).mockResolvedValue({});

    const newStatus = REPORT_STATUSES.SEARCH_STARTED;
    const actingUserId = 'userAct1';
    const result = await transitionReportStatus('report1', newStatus, actingUserId);

    expect(result.status).toBe(newStatus);
    expect(prisma.report.update).toHaveBeenCalledWith(expect.objectContaining({
      where: { id: 'report1' },
      data: expect.objectContaining({ status: newStatus }),
    }));
    expect(prisma.subject.update).toHaveBeenCalledWith(expect.objectContaining({
        where: { id: 'subj1' },
        data: expect.objectContaining({ status: newStatus }),
    }));
  });

  it('should prevent an invalid status transition', async () => {
    (prisma.report.findUnique as jest.Mock).mockResolvedValue({ ...mockReport, status: REPORT_STATUSES.COMPLETED });
    const newStatus = REPORT_STATUSES.UNDER_INVESTIGATION;
    const actingUserId = 'userAct2';

    await expect(transitionReportStatus('report1', newStatus, actingUserId))
      .rejects.toThrow(`Transition from ${REPORT_STATUSES.COMPLETED} to ${newStatus} is not allowed.`);

    expect(prisma.report.update).not.toHaveBeenCalled();
    expect(prisma.subject.update).not.toHaveBeenCalled();
  });

  it('should send notifications if recipient is different from acting user', async () => {
    const reportWithCreator = {
        ...mockReport,
        status: REPORT_STATUSES.INVESTIGATION_COMPLETED, // A status that triggers notification to creator
        createdBy: { id: 'creatorUserId', email: 'creator@example.com', primaryMobile: '+15551234567', firstName: 'Creator' },
        subject: { id: 'subj1', firstName: 'Test', lastName: 'Subject', identification: 'ID123' }
    };
    (prisma.report.findUnique as jest.Mock).mockResolvedValue(reportWithCreator);
    (prisma.report.update as jest.Mock).mockImplementation(async ({data}) => ({...reportWithCreator, status: data.status}));
    (prisma.subject.update as jest.Mock).mockResolvedValue({});

    const newStatus = REPORT_STATUSES.TEAM_LEAD_APPROVED;
    const actingUserId = 'teamLeadUser'; // Different from creator

    await transitionReportStatus('report1', newStatus, actingUserId);

    expect(createSystemMessage).toHaveBeenCalledTimes(1);
    expect(sendReportStatusUpdateEmail).toHaveBeenCalledTimes(1);
    expect(sendSms).toHaveBeenCalledTimes(1);
  });

  it('should NOT send notifications if recipient is the acting user', async () => {
    const reportWithCreatorAsActor = {
        ...mockReport,
        status: REPORT_STATUSES.INVESTIGATION_COMPLETED,
        createdBy: { id: 'actingUserId', email: 'actor@example.com', primaryMobile: '+15551234567', firstName: 'Actor' },
        subject: { id: 'subj1', firstName: 'Test', lastName: 'Subject', identification: 'ID123' }
    };
    (prisma.report.findUnique as jest.Mock).mockResolvedValue(reportWithCreatorAsActor);
    (prisma.report.update as jest.Mock).mockImplementation(async ({data}) => ({...reportWithCreatorAsActor, status: data.status}));
    (prisma.subject.update as jest.Mock).mockResolvedValue({});

    const newStatus = REPORT_STATUSES.TEAM_LEAD_APPROVED;
    const actingUserId = 'actingUserId'; // Same as creator

    await transitionReportStatus('report1', newStatus, actingUserId);

    expect(createSystemMessage).not.toHaveBeenCalled();
    expect(sendReportStatusUpdateEmail).not.toHaveBeenCalled();
    expect(sendSms).not.toHaveBeenCalled();
  });

  it('should update completedDate when transitioning to COMPLETED status', async () => {
    const reportWithoutCompletedDate = { ...mockReport, status: REPORT_STATUSES.TEAM_LEAD_APPROVED, completedDate: null };
    (prisma.report.findUnique as jest.Mock).mockResolvedValue(reportWithoutCompletedDate);
    (prisma.report.update as jest.Mock).mockImplementation(async ({data}) => ({...reportWithoutCompletedDate, ...data}));

    await transitionReportStatus('report1', REPORT_STATUSES.COMPLETED, 'user1');

    expect(prisma.report.update).toHaveBeenCalledWith(expect.objectContaining({
        data: expect.objectContaining({
            status: REPORT_STATUSES.COMPLETED,
            completedDate: expect.any(Date)
        })
    }));
  });

});
