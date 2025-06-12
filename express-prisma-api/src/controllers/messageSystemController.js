import prisma from '../utils/prismaClient.js';
// No need to import createSystemMessage here if it's only called internally by other services for now.

export const getUserMessages = async (req, res) => {
  const userId = req.user?.id;
  let { page = 1, limit = 10, read } = req.query;

  const pageNum = parseInt(page as string, 10) || 1; // Added type assertion for safety, though JS is dynamic
  const limitNum = parseInt(limit as string, 10) || 10;
  const offset = (pageNum - 1) * limitNum;

  const whereClause: any = { messageForId: userId };
  if (read === 'true') whereClause.messageRead = true;
  if (read === 'false') whereClause.messageRead = false;

  try {
    const messages = await prisma.messageSystem.findMany({
      where: whereClause,
      orderBy: { createdAt: 'desc' },
      skip: offset,
      take: limitNum,
      include: {
         subject: { select: { id: true, firstName: true, lastName: true }},
         report: { select: { id: true, sequence: true }}
      }
    });
    const totalMessages = await prisma.messageSystem.count({ where: whereClause });
    const totalPages = Math.ceil(totalMessages / limitNum);

    const unreadCount = await prisma.messageSystem.count({ where: { messageForId: userId, messageRead: false }});

    res.status(200).json({
      data: messages,
      total: totalMessages,
      page: pageNum,
      limit: limitNum,
      totalPages,
      unreadCount
    });
  } catch (error) {
    console.error(`Error fetching messages for user ${userId}:`, error);
    res.status(500).json({ error: 'Failed to fetch messages', details: error.message });
  }
};

export const markMessageAsRead = async (req, res) => {
  const { messageId } = req.params;
  const userId = req.user?.id;
  try {
    const message = await prisma.messageSystem.findUnique({ where: { id: messageId }});
    // Ensure the message belongs to the user trying to mark it as read
    if (!message || message.messageForId !== userId) {
      return res.status(404).json({ error: 'Message not found or not authorized to mark as read.' });
    }
    const updatedMessage = await prisma.messageSystem.update({
      where: { id: messageId },
      data: { messageRead: true, status: 'read', updatedAt: new Date() },
    });
    res.status(200).json(updatedMessage);
  } catch (error) {
    console.error(`Error marking message ${messageId} as read:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Message not found.' });
    res.status(500).json({ error: 'Failed to mark message as read', details: error.message });
  }
};

export const getUnreadMessageCount = async (req, res) => {
    const userId = req.user?.id;
    if (!userId) return res.status(401).json({error: "User not authenticated"}); // Added check for userId
    try {
        const unreadCount = await prisma.messageSystem.count({
            where: { messageForId: userId, messageRead: false }
        });
        res.status(200).json({ unreadCount });
    } catch (error) {
        console.error('Error fetching unread message count:', error);
        res.status(500).json({ error: 'Failed to fetch unread count' });
    }
};
