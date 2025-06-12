import prisma from '../utils/prismaClient.js';

export const createSystemMessage = async (data) => {
  const {
    messageForId, // Required: ID of the user the message is for
    messageHeader,
    message,
    messageType, // e.g., 'REPORT_STATUS_CHANGE', 'NEW_ASSIGNMENT'
    companyId,
    subjectId,
    reportId,
    // userId, // User who triggered the event, if different from messageForId & relevant
    // assignedToId,
    // teamLeadId
  } = data;

  if (!messageForId || !messageHeader || !message) {
    console.error("Message creation failed: messageForId, messageHeader, and message are required.");
    // In a real app, you might want to throw an error or return a more specific error object
    return null;
  }

  try {
    const newMessage = await prisma.messageSystem.create({
      data: {
        messageForId,
        messageHeader,
        message,
        messageType: messageType || 'GENERAL_NOTIFICATION', // Default type if not provided
        companyId: companyId || undefined,
        subjectId: subjectId || undefined,
        reportId: reportId || undefined,
        status: 'unread', // Default status explicitly set
        messageRead: false,
        // Any other relevant IDs based on your schema and message context
      }
    });
    return newMessage;
  } catch (error) {
    console.error('Error creating system message in service:', error);
    // Depending on usage, might re-throw or return null/error object
    return null;
  }
};
