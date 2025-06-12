import prisma from '../utils/prismaClient.js';
import multer from 'multer';
import path from 'path';
import fs from 'fs';

// ES Module equivalent for __dirname if not globally available (standard in Node ESM)
import { fileURLToPath } from 'url';
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Multer storage configuration
const proofStorageSetup = multer.diskStorage({
  destination: (req, file, cb) => {
    // Path should be relative to the project root where 'uploads' will be created by app.js
    // app.js creates 'uploads/proofs' at the root of express-prisma-api project
    const proofsDir = path.join('uploads', 'proofs');
    fs.mkdirSync(proofsDir, { recursive: true }); // Ensure it exists
    cb(null, proofsDir);
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + '-' + file.originalname.replace(/\s+/g, '_'));
  }
});
export const uploadProofFile = multer({ storage: proofStorageSetup }).single('proofFile');

export const createProof = async (req, res) => {
  const { answerId, comment, behaviourScoresJson, trait } = req.body;
  // const createdById = req.user?.id; // Not in schema yet

  let behaviourScores;
  if (behaviourScoresJson) {
     try { behaviourScores = JSON.parse(behaviourScoresJson); }
     catch(e) { return res.status(400).json({ error: 'Invalid JSON for behaviourScores.'}); }
  }

  if (!answerId || (!comment && !req.file)) {
    return res.status(400).json({ error: 'answerId and either comment or a proofFile are required.' });
  }

  try {
    const newProof = await prisma.proof.create({
      data: {
        answerId,
        comment: comment || req.file?.originalname || 'Uploaded File',
        behaviourScores: behaviourScores || undefined,
        trait: trait ? (trait === 'true' || trait === true) : false,
        filePath: req.file ? req.file.path.replace(/\\/g, '/') : undefined,
        originalFilename: req.file ? req.file.originalname : undefined,
        mimeType: req.file ? req.file.mimetype : undefined,
        // createdById: createdById,
      },
      include: { answer: { include: { question: true } } }
    });
    res.status(201).json(newProof);
  } catch (error) {
    console.error('Error creating proof:', error);
    if (error.code === 'P2003') return res.status(400).json({ error: 'Invalid answerId provided.' });
    res.status(500).json({ error: 'Failed to create proof', details: error.message });
  }
};

export const getProofsForAnswer = async (req, res) => {
    const { answerId } = req.params;
    try {
    const proofs = await prisma.proof.findMany({
        where: { answerId },
        orderBy: { createdAt: 'asc' },
    });
    res.status(200).json(proofs);
    } catch (error) {
        console.error(`Error fetching proofs for answer ${answerId}:`, error);
        res.status(500).json({ error: 'Failed to fetch proofs' });
    }
};

export const updateProof = async (req, res) => {
    const { id } = req.params;
    // For now, updateProof does not handle file replacement to keep it simple.
    // If a file needs to be changed, current workflow would be delete old proof, create new.
    const { comment, behaviourScoresJson, trait } = req.body;
    let behaviourScores;
    if (behaviourScoresJson) {
        try { behaviourScores = JSON.parse(behaviourScoresJson); }
        catch(e) { return res.status(400).json({ error: 'Invalid JSON for behaviourScores.'}); }
    }
    try {
        const updatedProof = await prisma.proof.update({
            where: { id },
            data: {
                comment,
                behaviourScores: behaviourScores || undefined,
                trait: trait ? (trait === 'true' || trait === true) : undefined
            }
        });
        res.status(200).json(updatedProof);
    } catch (error) {
        console.error(`Error updating proof ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Proof not found.' });
        res.status(500).json({ error: 'Failed to update proof.', details: error.message });
    }
};

export const deleteProof = async (req, res) => {
    const { id } = req.params;
    try {
        const proofToDelete = await prisma.proof.findUnique({ where: {id}});
        if (proofToDelete?.filePath) {
            // __dirname in controller is express-prisma-api/src/controllers
            // filePath is like "uploads/proofs/filename.ext"
            // So path.join(__dirname, '../../', proofToDelete.filePath) should be correct to reach from /app/express-prisma-api/src/controllers to /app/express-prisma-api/uploads/proofs/filename.ext
            const fullFilePath = path.join(__dirname, '..', '..', proofToDelete.filePath);
            fs.unlink(fullFilePath, err => {
                if (err) console.error("Error deleting proof file:", proofToDelete.filePath, err);
                else console.log("Successfully deleted proof file:", proofToDelete.filePath);
            });
        }
        await prisma.proof.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        console.error(`Error deleting proof ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Proof not found' });
        res.status(500).json({ error: 'Failed to delete proof', details: error.message });
    }
};
