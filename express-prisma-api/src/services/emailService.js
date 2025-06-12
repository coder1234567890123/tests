// In express-prisma-api/src/services/emailService.js
import sgMail from '@sendgrid/mail';
import dotenv from 'dotenv';

dotenv.config({ path: '../.env' });

sgMail.setApiKey(process.env.SENDGRID_API_KEY || '');

const APP_NAME = process.env.APP_NAME || 'YourAppName';
const FRONTEND_URL = process.env.FRONTEND_URL || 'http://localhost:3000';
const SENDER_EMAIL = process.env.SENDER_EMAIL || 'noreply@yourapp.com';

const sanitizeHTML = (text) => {
  if (text === null || text === undefined) return '';
  return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
};

// --- Existing Templates ---
const getRegistrationEmailHTML = (user) => {
  return `
    <!DOCTYPE html>
    <html>
    <head><title>Welcome to ${sanitizeHTML(APP_NAME)}!</title></head>
    <body>
      <h1>Welcome, ${sanitizeHTML(user.firstName || user.email)}!</h1>
      <p>Thank you for registering with ${sanitizeHTML(APP_NAME)}.</p>
      <p>You can now log in to your account using your email and the password you set during registration.</p>
      <p><a href="${sanitizeHTML(FRONTEND_URL)}/login">Click here to Login</a></p>
      <br/>
      <p>If you did not register for an account, please ignore this email.</p>
      <p>Thanks,<br/>The ${sanitizeHTML(APP_NAME)} Team</p>
    </body>
    </html>
  `;
};

const getPasswordResetEmailHTML = (user, token) => {
     return `
         <!DOCTYPE html><html><head><title>Password Reset Request</title></head><body>
         <h1>Password Reset Request</h1>
         <p>Hello ${sanitizeHTML(user.firstName || user.email)},</p>
         <p>You (or someone acting on your behalf) requested a password reset for your ${APP_NAME} account.</p>
         <p>If this was you, please click the link below to reset your password:</p>
         <p><a href="${FRONTEND_URL}/reset-password/${token}">Reset Your Password</a></p>
         <p>This link is valid for 1 hour.</p>
         <p>If you did not request a password reset, please ignore this email.</p>
         <p>Thanks,<br/>The ${sanitizeHTML(APP_NAME)} Team</p>
         </body></html>
     `;
};

const getReportAssignedEmailHTML = (report, assignee, subject) => {
  const subjectName = subject ? `${sanitizeHTML(subject.firstName) || ''} ${sanitizeHTML(subject.lastName) || ''} (ID: ${sanitizeHTML(subject.identification) || 'N/A'})` : 'N/A';
  return `
    <!DOCTYPE html><html><head><title>New Report Assignment</title></head><body>
      <h1>New Report Assignment: ${sanitizeHTML(report.sequence)}</h1>
      <p>Hello ${sanitizeHTML(assignee.firstName || assignee.email)},</p>
      <p>You have been assigned to a new report:</p>
      <ul>
        <li>Report ID: ${sanitizeHTML(report.sequence)}</li>
        <li>Subject: ${subjectName}</li>
        <li>Current Status: ${report.status ? sanitizeHTML(report.status.replace(/_/g, ' ')) : 'N/A'}</li>
        <li>Due Date: ${report.dueDate ? new Date(report.dueDate).toLocaleDateString() : 'N/A'}</li>
      </ul>
      <p><a href="${FRONTEND_URL}/reports/${report.id}">View Report Details</a></p>
      <p>Please begin your investigation at your earliest convenience.</p>
      <p>Thanks,<br/>The ${sanitizeHTML(APP_NAME)} Team</p>
    </body></html>
  `;
};

// Updated to be more flexible or use generic one
const getReportStatusUpdateEmailHTML = (report, recipient, subject, oldStatus, newStatus, customMessageBody) => {
  const subjectName = subject ? `${sanitizeHTML(subject.firstName) || ''} ${sanitizeHTML(subject.lastName) || ''} (ID: ${sanitizeHTML(subject.identification) || 'N/A'})` : 'N/A';
  const defaultMessageBody = `
    <p>The status of report '${sanitizeHTML(report.sequence)}' for subject ${subjectName} has been updated:</p>
    <ul>
      <li>Previous Status: ${oldStatus ? sanitizeHTML(oldStatus.replace(/_/g, ' ')) : 'N/A'}</li>
      <li>New Status: ${sanitizeHTML(newStatus.replace(/_/g, ' '))}</li>
    </ul>
  `;
  return `
    <!DOCTYPE html><html><head><title>Report Status Updated</title></head><body>
      <h1>Report Status Updated: ${sanitizeHTML(report.sequence)}</h1>
      <p>Hello ${sanitizeHTML(recipient.firstName || recipient.email)},</p>
      ${customMessageBody || defaultMessageBody}
      <p><a href="${FRONTEND_URL}/reports/${report.id}">View Report Details</a></p>
      <p>Thanks,<br/>The ${sanitizeHTML(APP_NAME)} Team</p>
    </body></html>
  `;
};

// New Generic Template
const getGenericNotificationEmailHTML = (recipient, subjectLine, messageBody, actionLink, actionText) => {
  return `
    <!DOCTYPE html><html><head><title>${sanitizeHTML(subjectLine)}</title></head><body>
      <p>Hello ${sanitizeHTML(recipient.firstName || recipient.email)},</p>
      ${messageBody}
      ${actionLink && actionText ? `<p><a href="${sanitizeHTML(actionLink)}">${sanitizeHTML(actionText)}</a></p>` : ''}
      <p>Thanks,<br/>The ${sanitizeHTML(APP_NAME)} Team</p>
    </body></html>
  `;
};

// --- Senders ---
const canSendEmail = () => {
    if (!process.env.SENDGRID_API_KEY || process.env.SENDGRID_API_KEY === "YOUR_SENDGRID_API_KEY_PLACEHOLDER" || process.env.SENDGRID_API_KEY === "") {
        console.warn('SENDGRID_API_KEY not set or is placeholder. Email sending disabled.');
        return false;
    }
    return true;
};

export const sendRegistrationEmail = async (user) => { /* ... (no change from prompt, but add canSendEmail check) ... */
    if (!canSendEmail()) return Promise.resolve();
    if (!user || !user.email) { console.warn('User or user email missing for registration email.'); return Promise.resolve(); }
    // ... rest of the function
    const msg = {
      to: user.email,
      from: { name: `${APP_NAME} Support`, email: SENDER_EMAIL },
      subject: `Welcome to ${APP_NAME}!`,
      html: getRegistrationEmailHTML(user),
    };
    try {
      await sgMail.send(msg);
      console.log(`Registration email sent to ${user.email}`);
    } catch (error) {
      console.error('Error sending registration email:', error.response?.body || error);
    }
};

export const sendPasswordResetEmail = async (user, token) => { /* ... (no change, but add canSendEmail check) ... */
    if (!canSendEmail()) return Promise.resolve();
    if (!user || !user.email) { console.warn('User or user email missing for password reset.'); return Promise.resolve(); }
    // ... rest of the function
    const msg = {
        to: user.email,
        from: { name: `${APP_NAME} Support`, email: SENDER_EMAIL },
        subject: `${APP_NAME} - Password Reset Request`,
        html: getPasswordResetEmailHTML(user, token),
    };
    try {
        await sgMail.send(msg);
        console.log(`Password reset email sent to ${user.email}`);
    } catch (error) {
        console.error('Error sending password reset email:', error.response?.body || error);
    }
};

export const sendReportAssignedEmail = async (assignee, report, subject) => {
  if (!canSendEmail()) return Promise.resolve();
  if (!assignee || !assignee.email) {
     console.warn('Assignee email not available. Skipping report assignment email.');
     return Promise.resolve();
  }
  const msg = {
    to: assignee.email,
    from: { name: `${APP_NAME} Notifications`, email: SENDER_EMAIL },
    subject: `New Report Assignment: ${report.sequence}`,
    html: getReportAssignedEmailHTML(report, assignee, subject),
  };
  try {
    await sgMail.send(msg);
    console.log(`Report assignment email sent to ${assignee.email}`);
  } catch (error) {
    console.error('Error sending report assignment email:', error.response?.body || error);
  }
};

// Updated to accept custom message and subject line, falling back to old behavior if not provided
export const sendReportStatusUpdateEmail = async (recipient, report, subject, oldStatus, newStatus, customMessageBody, customSubjectLine) => {
  if (!canSendEmail()) return Promise.resolve();
   if (!recipient || !recipient.email) {
     console.warn('Recipient email not available. Skipping report status update email.');
     return Promise.resolve();
  }
  const subjectLine = customSubjectLine || `Report Status Updated: ${report.sequence} is now ${newStatus.replace(/_/g, ' ')}`;
  const htmlBody = getReportStatusUpdateEmailHTML(report, recipient, subject, oldStatus, newStatus, customMessageBody); // Pass customMessageBody

  const msg = {
    to: recipient.email,
    from: { name: `${APP_NAME} Notifications`, email: SENDER_EMAIL },
    subject: subjectLine,
    html: htmlBody,
  };
  try {
    await sgMail.send(msg);
    console.log(`Report status update email sent to ${recipient.email}`);
  } catch (error) {
    console.error('Error sending report status update email:', error.response?.body || error);
  }
};

// New Generic Sender
export const sendGenericNotificationEmail = async (recipient, subjectLine, messageBody, actionLink, actionText) => {
  if (!canSendEmail()) return Promise.resolve();
  if (!recipient || !recipient.email) {
     console.warn('Recipient email not available for generic notification. Skipping.');
     return Promise.resolve();
  }
  const msg = {
    to: recipient.email,
    from: { name: `${APP_NAME} Notifications`, email: SENDER_EMAIL },
    subject: subjectLine,
    html: getGenericNotificationEmailHTML(recipient, subjectLine, messageBody, actionLink, actionText),
  };
  try {
    await sgMail.send(msg);
    console.log(`Generic notification email sent to ${recipient.email} with subject: ${subjectLine}`);
  } catch (error) {
    console.error('Error sending generic notification email:', error.response?.body || error);
  }
};
