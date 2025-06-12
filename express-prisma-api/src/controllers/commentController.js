import prisma from '../utils/prismaClient.js';

export const createComment = async (req, res) => {
  const { reportId, answerId, comment, commentType, approval, private: isPrivate, hidden } = req.body;
  const commentById = req.user?.id;

  if (!comment || (!reportId && !answerId)) { // Enforce comment text and at least one link (report or answer)
    return res.status(400).json({ error: 'Comment text and either reportId or answerId are required.' });
  }
  if (reportId && answerId) {
    // Decide if comments can be linked to both, or if one takes precedence, or if it's an error.
    // For now, let's assume it's an error or prefer reportId if both are given.
    // Or, the schema should restrict to one or the other.
    // Current schema only has reportId on Comment.
    if (answerId) {
        console.warn("Comment received both reportId and answerId. Using reportId as primary link. answerId link ignored as it's not in current Comment schema.");
    }
  }


  try {
    const newComment = await prisma.comment.create({
      data: {
        comment,
        reportId: reportId || undefined,
        // answerId: answerId || undefined, // Not in current Comment schema. Add to schema if comments can link directly to answers.
        commentById,
        commentType: commentType || 'normal',
        approval,
        private: isPrivate || false,
        hidden: hidden || false,
      },
      include: { commentBy: { select: { id: true, firstName: true, lastName: true, email: true }} }
    });
    res.status(201).json(newComment);
  } catch (error) {
    console.error('Error creating comment:', error);
    // P2003 indicates a foreign key constraint failure (e.g. reportId or commentById doesn't exist)
    if (error.code === 'P2003') return res.status(400).json({ error: 'Invalid reportId or commentById.' });
    res.status(500).json({ error: 'Failed to create comment', details: error.message });
  }
};

export const getCommentsForReport = async (req, res) => {
  const { reportId } = req.params;
  try {
    const comments = await prisma.comment.findMany({
      where: { reportId },
      orderBy: { createdAt: 'asc' },
      include: { commentBy: { select: { id: true, firstName: true, lastName: true, email: true }} }
    });
    res.status(200).json(comments);
  } catch (error) {
    console.error(`Error fetching comments for report ${reportId}:`, error);
    res.status(500).json({ error: 'Failed to fetch comments', details: error.message });
  }
};

// Get comments for a specific answer (if you implement direct Answer-Comment link in schema)
// export const getCommentsForAnswer = async (req, res) => {
//   const { answerId } = req.params;
//   try {
//     const comments = await prisma.comment.findMany({
//       where: { answerId }, // This requires answerId field on Comment model
//       orderBy: { createdAt: 'asc' },
//       include: { commentBy: { select: { id: true, firstName: true, lastName: true, email: true }} }
//     });
//     res.status(200).json(comments);
//   } catch (error) {
//     console.error(`Error fetching comments for answer ${answerId}:`, error);
//     res.status(500).json({ error: 'Failed to fetch comments for answer', details: error.message });
//   }
// };

export const updateComment = async (req, res) => {
  const { id } = req.params;
  const { comment, private: isPrivate, hidden } = req.body; // Only allow updating these fields by general users
  const userId = req.user?.id;

  try {
     const existingComment = await prisma.comment.findUnique({ where: { id }});
     if (!existingComment) return res.status(404).json({ error: "Comment not found."});

     // Optional: Check if user is owner or admin to allow edit
     // This requires roles to be available on req.user
     // if (existingComment.commentById !== userId && !req.user.roles?.includes('ROLE_SUPER_ADMIN')) {
     //    return res.status(403).json({ error: "Not authorized to edit this comment."});
     // }

    const updatedComment = await prisma.comment.update({
      where: { id },
      data: {
        comment: comment !== undefined ? comment : undefined,
        private: isPrivate !== undefined ? isPrivate : undefined,
        hidden: hidden !== undefined ? hidden : undefined
      },
      include: { commentBy: { select: { id: true, firstName: true, lastName: true, email: true }} }
    });
    res.status(200).json(updatedComment);
  } catch (error) {
    console.error(`Error updating comment ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Comment not found' });
    res.status(500).json({ error: 'Failed to update comment', details: error.message });
  }
};

export const deleteComment = async (req, res) => {
  const { id } = req.params;
  // Optional: Add ownership/admin check before deletion
  // const userId = req.user?.id;
  // const commentToDelete = await prisma.comment.findUnique({ where: { id }});
  // if (!commentToDelete) return res.status(404).json({ error: "Comment not found."});
  // if (commentToDelete.commentById !== userId && !req.user.roles?.includes('ROLE_SUPER_ADMIN')) {
  //    return res.status(403).json({ error: "Not authorized to delete this comment."});
  // }

  try {
    await prisma.comment.delete({ where: { id } });
    res.status(204).send();
  } catch (error) {
    console.error(`Error deleting comment ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Comment not found' });
    res.status(500).json({ error: 'Failed to delete comment', details: error.message });
  }
};
