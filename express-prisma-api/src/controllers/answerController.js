import prisma from '../utils/prismaClient.js';
import * as reportScoreService from '../services/reportScoreService.js';

export const submitAnswer = async (req, res) => {
  const { reportId, questionId, answer, score, platform, sliderValue, defaultName, notApplicable, subjectId } = req.body;
  const userId = req.user?.id; // From protect middleware

  if (!reportId || !questionId || answer === undefined) { // answer can be an empty string, so check for undefined
    return res.status(400).json({ error: 'reportId, questionId, and answer are required.' });
  }

  try {
    const newAnswer = await prisma.answer.create({
      data: {
        reportId,
        questionId,
        userId, // User who answered
        subjectId: subjectId, // Optional: if answer is also directly tied to subject
        answer: answer.toString(), // Ensure answer is string
        score: score !== undefined ? score.toString() : null, // Ensure score is string if provided, else null
        platform,
        sliderValue: sliderValue !== undefined ? parseInt(sliderValue) : 0, // Ensure sliderValue is int or default
        defaultName,
        notApplicable: notApplicable || false, // Default to false if not provided
      },
    });

    // Trigger score recalculation (can be awaited or run in background)
    // Not awaiting it makes the answer submission faster, but scores might not be updated instantly for the response.
    // Awaiting it ensures scores are updated before responding, but might be slower.
    // For now, await it to ensure data consistency for subsequent requests if they rely on updated scores.
    await reportScoreService.calculateAndSaveReportScores(reportId);

    res.status(201).json(newAnswer);
  } catch (error) {
    console.error('Error submitting answer:', error);
    res.status(500).json({ error: 'Failed to submit answer', details: error.message });
  }
};

export const getAnswersForReport = async (req, res) => {
     const { reportId } = req.params;
     try {
         const answers = await prisma.answer.findMany({
             where: { reportId },
             include: { question: true, user: {select: {firstName:true, lastName:true}} }
         });
         res.status(200).json(answers);
     } catch (error) {
         console.error('Error fetching answers for report:', error);
         res.status(500).json({ error: 'Failed to fetch answers' });
     }
};
