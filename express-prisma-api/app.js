import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import morgan from 'morgan';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs'; // For directory creation

// Route imports
import userRoutes from './src/routes/userRoutes.js';
import companyRoutes from './src/routes/companyRoutes.js';
import authRoutes from './src/routes/authRoutes.js';
import subjectRoutes from './src/routes/subjectRoutes.js';
import reportRoutes from './src/routes/reportRoutes.js';
import answerRoutes from './src/routes/answerRoutes.js';
import globalWeightsRoutes from './src/routes/globalWeightsRoutes.js';
import systemConfigRoutes from './src/routes/systemConfigRoutes.js';
import proofRoutes from './src/routes/proofRoutes.js';
import commentRoutes from './src/routes/commentRoutes.js';
import dashboardRoutes from './src/routes/dashboardRoutes.js';
import messageSystemRoutes from './src/routes/messageSystemRoutes.js';

// Load environment variables
dotenv.config();

// ES Module equivalent of __dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Ensure uploads directory exists
const uploadsDir = path.join(__dirname, 'uploads');
const proofsDir = path.join(uploadsDir, 'proofs');
const systemAssetsDir = path.join(uploadsDir, 'system-assets'); // For default branding assets
const companyImagesDir = path.join(uploadsDir, 'company-images'); // For company-specific assets

fs.mkdirSync(uploadsDir, { recursive: true }); // Ensure base uploads dir exists
fs.mkdirSync(proofsDir, { recursive: true });
fs.mkdirSync(systemAssetsDir, { recursive: true });
fs.mkdirSync(companyImagesDir, { recursive: true });

const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(morgan('dev'));

// Serve uploaded files statically
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// API routes
app.use('/api/auth', authRoutes);
app.use('/api/users', userRoutes);
app.use('/api/companies', companyRoutes);
app.use('/api/subjects', subjectRoutes);
app.use('/api/reports', reportRoutes);
app.use('/api/answers', answerRoutes);
app.use('/api/global-weights', globalWeightsRoutes);
app.use('/api/system-configs', systemConfigRoutes);
app.use('/api/proofs', proofRoutes);
app.use('/api/comments', commentRoutes);
app.use('/api/dashboard', dashboardRoutes);
app.use('/api/messages', messageSystemRoutes);

// Basic route
app.get('/', (req, res) => {
  res.send('Welcome to the Express App!');
});

// Start server
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
