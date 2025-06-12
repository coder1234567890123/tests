// In express-prisma-api/src/services/workflowService.js
import prisma from '../utils/prismaClient.js';
import { createSystemMessage } from './messageSystemService.js';
import { sendReportStatusUpdateEmail } from './emailService.js'; // Import email service
import { sendSms } from './twilioService.js';
// These should align with your Prisma schema enums or string constants if you define them
export const REPORT_STATUSES = { // Export for potential use in other services/controllers
  NEW_REQUEST: 'new_request',
  NEEDS_APPROVAL: 'needs_approval', // For rush/test reports
  REPORT_TYPE_APPROVED: 'report_type_approved', // Rush/test approved, becomes like new_request
  UNASSIGNED: 'unassigned', // After approval or if normal & ready for assignment
  SEARCH_STARTED: 'search_started',
  SEARCH_COMPLETED: 'search_completed',
  VALIDATED: 'validated', // Subject profiles validated
  UNDER_INVESTIGATION: 'under_investigation',
  INVESTIGATION_COMPLETED: 'investigation_completed',
  TEAM_LEAD_APPROVED: 'team_lead_approved', // Quality check by team lead
  COMPLETED: 'completed', // Final state, PDF ready
  ABANDONED_REQUEST: 'abandoned_request', // User/Client requests to abandon before completion
  ABANDONED: 'abandoned', // System/Admin marks as abandoned
};

// Simplified transition rules: { currentStatus: [allowedNextStatuses] }
const reportTransitions = {
  [REPORT_STATUSES.NEW_REQUEST]: [REPORT_STATUSES.NEEDS_APPROVAL, REPORT_STATUSES.UNASSIGNED, REPORT_STATUSES.SEARCH_STARTED, REPORT_STATUSES.ABANDONED_REQUEST],
  [REPORT_STATUSES.NEEDS_APPROVAL]: [REPORT_STATUSES.REPORT_TYPE_APPROVED, REPORT_STATUSES.ABANDONED_REQUEST, REPORT_STATUSES.NEW_REQUEST], // Added NEW_REQUEST for rejection
  [REPORT_STATUSES.REPORT_TYPE_APPROVED]: [REPORT_STATUSES.UNASSIGNED, REPORT_STATUSES.SEARCH_STARTED],
  [REPORT_STATUSES.UNASSIGNED]: [REPORT_STATUSES.SEARCH_STARTED, REPORT_STATUSES.ABANDONED_REQUEST],
  [REPORT_STATUSES.SEARCH_STARTED]: [REPORT_STATUSES.SEARCH_COMPLETED, REPORT_STATUSES.ABANDONED_REQUEST], // Can be abandoned during search
  [REPORT_STATUSES.SEARCH_COMPLETED]: [REPORT_STATUSES.VALIDATED, REPORT_STATUSES.UNDER_INVESTIGATION, REPORT_STATUSES.ABANDONED_REQUEST], // Can go to under_investigation if validation is quick/skipped
  [REPORT_STATUSES.VALIDATED]: [REPORT_STATUSES.UNDER_INVESTIGATION, REPORT_STATUSES.ABANDONED_REQUEST],
  [REPORT_STATUSES.UNDER_INVESTIGATION]: [REPORT_STATUSES.INVESTIGATION_COMPLETED, REPORT_STATUSES.ABANDONED_REQUEST],
  [REPORT_STATUSES.INVESTIGATION_COMPLETED]: [REPORT_STATUSES.TEAM_LEAD_APPROVED, REPORT_STATUSES.COMPLETED, REPORT_STATUSES.ABANDONED_REQUEST], // Can go directly to COMPLETED
  [REPORT_STATUSES.TEAM_LEAD_APPROVED]: [REPORT_STATUSES.COMPLETED],
  // COMPLETED and ABANDONED are typically final states from active work.
  // ABANDONED_REQUEST can transition to ABANDONED (handled by an admin/system process perhaps)
  [REPORT_STATUSES.ABANDONED_REQUEST]: [REPORT_STATUSES.ABANDONED],
};

// Subject statuses might mirror report or have their own simpler flow
// For now, primarily focus on Report status which drives most actions.

export const transitionReportStatus = async (reportId, newStatus, userId /* acting user */) => {
  const report = await prisma.report.findUnique({
     where: { id: reportId },
     include: {
         subject: { select: {id: true, firstName: true, lastName: true, identification: true}},
         createdBy: { select: {id: true, email: true, firstName: true, primaryMobile: true }},
         assignedTo: { select: {id: true, email: true, firstName: true, primaryMobile: true }},
         company: { select: {id: true, name: true}} // For companyId in createSystemMessage
     }
 });
  if (!report) throw new Error('Report not found.');

  const currentStatus = report.status;
  // If currentStatus is null or undefined, treat it as NEW_REQUEST for transition validation purposes
  const effectiveCurrentStatus = currentStatus || REPORT_STATUSES.NEW_REQUEST;

  const allowedTransitions = reportTransitions[effectiveCurrentStatus] || [];

  if (!allowedTransitions.includes(newStatus) && effectiveCurrentStatus !== newStatus) {
    throw new Error(`Transition from ${effectiveCurrentStatus} to ${newStatus} is not allowed.`);
  }

  const dataToUpdate: any = {
    status: newStatus,
    updatedAt: new Date(),
  };

  if (newStatus === REPORT_STATUSES.COMPLETED && !report.completedDate) {
    dataToUpdate.completedDate = new Date();
  }
  // Add other status-specific field updates here if needed
  // e.g., if moving to SEARCH_STARTED, maybe update a searchStartedAt field.

  const updatedReport = await prisma.report.update({
    where: { id: reportId },
    data: updatedReportData, // Use the data object with status and completedDate
    include: { // Re-include for full object in return and notifications
         subject: { select: {id: true, firstName: true, lastName: true, identification: true}},
         createdBy: { select: {id: true, email: true, firstName: true, primaryMobile: true }},
         assignedTo: { select: {id: true, email: true, firstName: true, primaryMobile: true }},
         company: { select: {id: true, name: true}}
    }
  });

  // --- Enhanced Notification Logic ---
  // const actingUser = await prisma.user.findUnique({where: {id: userId}}); // For sender context if needed

  const subjectInfo = updatedReport.subject;
  const reportCreator = updatedReport.createdBy;
  const reportAssignee = updatedReport.assignedTo;
  // Potentially fetch Team Lead based on report.company.team.teamLeader or report.assignedTo.team.teamLeader

  let emailRecipients = new Map<string, any>(); // Use Map to avoid duplicate emails to same person for different roles

  const addUserToRecipients = (userToAdd) => {
     if (userToAdd && userToAdd.email && userToAdd.id !== userId) { // Don't notify self, ensure email
         emailRecipients.set(userToAdd.id, userToAdd);
     }
  };

  let emailSubjectLine = `Report Update: ${updatedReport.sequence}`;
  let specificMessageBody = `<p>The status of report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) has changed from <strong>${currentStatus || 'None'}</strong> to <strong>${newStatus.replace(/_/g, ' ')}</strong>.</p>`;
  let smsMessage = `Report ${updatedReport.sequence} (${subjectInfo?.firstName?.[0]}.${subjectInfo?.lastName}) status: ${newStatus.replace(/_/g, ' ')}`;

  // Determine recipients and customize messages based on newStatus
  switch (newStatus) {
    case REPORT_STATUSES.UNDER_INVESTIGATION:
      if (reportAssignee) addUserToRecipients(reportAssignee);
      emailSubjectLine = `Investigation Started: Report ${updatedReport.sequence}`;
      specificMessageBody = `<p>The investigation for report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) has started (or been re-opened) and is now <strong>${newStatus.replace(/_/g, ' ')}</strong>.</p><p>You are assigned to this report.</p>`;
      smsMessage = `Investigation for report ${updatedReport.sequence} has started. You are assigned.`;
      break;
    case REPORT_STATUSES.INVESTIGATION_COMPLETED:
      if (reportCreator) addUserToRecipients(reportCreator);
      // TODO: Add logic to find and notify Team Lead if applicable
      emailSubjectLine = `Investigation Completed: ${updatedReport.sequence}`;
      specificMessageBody = `<p>Investigation for report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) is complete and ready for review.</p>`;
      smsMessage = `Investigation for ${updatedReport.sequence} is complete.`;
      break;
    case REPORT_STATUSES.TEAM_LEAD_APPROVED:
      if (reportCreator) addUserToRecipients(reportCreator);
      emailSubjectLine = `Report Approved (Team Lead): ${updatedReport.sequence}`;
      specificMessageBody = `<p>Report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) has been approved by the Team Lead and is pending final completion.</p>`;
      smsMessage = `Report ${updatedReport.sequence} approved by Team Lead.`;
      break;
    case REPORT_STATUSES.COMPLETED:
      if (reportCreator) addUserToRecipients(reportCreator);
      emailSubjectLine = `Report Completed: ${updatedReport.sequence}`;
      specificMessageBody = `<p>Report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) has been completed.</p>`;
      smsMessage = `Report ${updatedReport.sequence} completed.`;
      break;
    case REPORT_STATUSES.ABANDONED:
    case REPORT_STATUSES.ABANDONED_REQUEST:
      if (reportCreator) addUserToRecipients(reportCreator);
      if (reportAssignee && reportAssignee.id !== reportCreator?.id) addUserToRecipients(reportAssignee); // Also notify assignee if different
      emailSubjectLine = `Report Abandoned: ${updatedReport.sequence}`;
      specificMessageBody = `<p>Report '${updatedReport.sequence}' (${subjectInfo?.firstName} ${subjectInfo?.lastName}) has been marked as ${newStatus.replace(/_/g, ' ')}.</p>`;
      smsMessage = `Report ${updatedReport.sequence} marked ${newStatus.replace(/_/g, ' ')}.`;
      break;
    default:
      if (reportCreator) addUserToRecipients(reportCreator);
      if (reportAssignee && reportAssignee.id !== reportCreator?.id) addUserToRecipients(reportAssignee);
  }

  for (const recipient of emailRecipients.values()) {
    // In-App Message
    createSystemMessage({
        messageForId: recipient.id,
        messageHeader: emailSubjectLine, // Use the more specific email subject line
        message: specificMessageBody.replace(/<p>|<\/p>|<ul>|<\/ul>|<li>|<\/li>/g, ' ').replace(/\s\s+/g, ' ').trim(), // Basic text version of message
        messageType: 'REPORT_STATUS_UPDATE',
        reportId: report.id,
        subjectId: report.subjectId,
        companyId: report.companyId
    }).catch(console.error);

    // Email Notification
    if (recipient.email && subjectInfo) {
      sendReportStatusUpdateEmail(recipient, updatedReport, subjectInfo, currentStatus || 'None', newStatus, specificMessageBody, emailSubjectLine).catch(console.error);
    }
    // SMS Notification
    if (recipient.primaryMobile) {
      sendSms(recipient.primaryMobile, smsMessage).catch(console.error);
    }
  }

  // If subject status should mirror report status (common scenario)
  // Avoid changing subject status if report is just requested to be abandoned
  if (report.subjectId && newStatus !== REPORT_STATUSES.ABANDONED_REQUEST && newStatus !== REPORT_STATUSES.ABANDONED) {
     try {
        await prisma.subject.update({
            where: { id: report.subjectId },
            // Ensure subject status aligns with report status (map if necessary, or use directly if same)
            data: { status: newStatus, updatedAt: new Date() }
        });
     } catch (subjectUpdateError) {
        console.error(`Failed to update subject ${report.subjectId} status:`, subjectUpdateError);
        // Decide if this error should affect the main operation's success.
        // For now, we log it but don't throw, as report status update was successful.
     }
  }

  return updatedReport;
};
