import prisma from '../utils/prismaClient.js';

export const createGlobalWeight = async (req, res) => {
  const { socialPlatform, globalUsageWeighting, version, ordering, stdComments } = req.body;
  if (!socialPlatform || globalUsageWeighting === undefined) {
    return res.status(400).json({ error: 'socialPlatform and globalUsageWeighting are required.' });
  }
  try {
    const newWeight = await prisma.globalWeights.create({
      data: {
        socialPlatform,
        globalUsageWeighting: parseFloat(globalUsageWeighting),
        version: version !== undefined ? parseInt(version) : undefined,
        ordering: ordering !== undefined ? parseInt(ordering) : undefined,
        stdComments
      },
    });
    res.status(201).json(newWeight);
  } catch (error) {
    if (error.code === 'P2002') return res.status(409).json({ error: 'GlobalWeight for this socialPlatform already exists.' });
    console.error('Error creating GlobalWeight:', error);
    res.status(500).json({ error: 'Failed to create GlobalWeight', details: error.message });
  }
};

export const getAllGlobalWeights = async (req, res) => {
  try {
    const weights = await prisma.globalWeights.findMany({ orderBy: { ordering: 'asc' } });
    res.status(200).json(weights);
  } catch (error) {
    console.error('Error fetching GlobalWeights:', error);
    res.status(500).json({ error: 'Failed to fetch GlobalWeights', details: error.message });
  }
};

export const getGlobalWeightById = async (req, res) => { // Or by socialPlatform if unique and preferred
  const { id } = req.params;
  try {
    const weight = await prisma.globalWeights.findUnique({ where: { id } });
    if (!weight) return res.status(404).json({ error: 'GlobalWeight not found' });
    res.status(200).json(weight);
  } catch (error) {
    console.error('Error fetching GlobalWeight by ID:', error);
    res.status(500).json({ error: 'Failed to fetch GlobalWeight', details: error.message });
  }
};

export const updateGlobalWeight = async (req, res) => {
  const { id } = req.params;
  const { globalUsageWeighting, version, ordering, stdComments, socialPlatform } = req.body;
  try {
    const updatedWeight = await prisma.globalWeights.update({
      where: { id },
      data: {
         socialPlatform, // Allow updating socialPlatform name if needed, though it's unique
         globalUsageWeighting: globalUsageWeighting !== undefined ? parseFloat(globalUsageWeighting) : undefined,
         version: version !== undefined ? parseInt(version) : undefined,
         ordering: ordering !== undefined ? parseInt(ordering) : undefined,
         stdComments
     },
    });
    res.status(200).json(updatedWeight);
  } catch (error) {
    if (error.code === 'P2025') return res.status(404).json({ error: 'GlobalWeight not found' });
    if (error.code === 'P2002') return res.status(409).json({ error: 'GlobalWeight for this socialPlatform already exists.' });
    console.error('Error updating GlobalWeight:', error);
    res.status(500).json({ error: 'Failed to update GlobalWeight', details: error.message });
  }
};

export const deleteGlobalWeight = async (req, res) => {
  const { id } = req.params;
  try {
    await prisma.globalWeights.delete({ where: { id } });
    res.status(204).send();
  } catch (error) {
    if (error.code === 'P2025') return res.status(404).json({ error: 'GlobalWeight not found' });
    console.error('Error deleting GlobalWeight:', error);
    res.status(500).json({ error: 'Failed to delete GlobalWeight', details: error.message });
  }
};
