import prisma from '../utils/prismaClient.js';

export const createSystemConfig = async (req, res) => {
  const { opt, val, systemType } = req.body;
  if (!opt || val === undefined) {
    return res.status(400).json({ error: 'Option key (opt) and value (val) are required.' });
  }
  try {
    const newConfig = await prisma.systemConfig.create({
      data: {
        opt,
        val, // Prisma handles type based on schema (String for val @db.Text)
        systemType: systemType !== undefined ? parseInt(systemType) : null // systemType is Int?
      },
    });
    res.status(201).json(newConfig);
  } catch (error) {
    if (error.code === 'P2002') return res.status(409).json({ error: 'SystemConfig for this option key (opt) already exists.' });
    console.error('Error creating SystemConfig:', error);
    res.status(500).json({ error: 'Failed to create SystemConfig', details: error.message });
  }
};

export const getAllSystemConfigs = async (req, res) => {
  try {
    const configs = await prisma.systemConfig.findMany({ orderBy: { opt: 'asc' } });
    res.status(200).json(configs);
  } catch (error) {
    console.error('Error fetching SystemConfigs:', error);
    res.status(500).json({ error: 'Failed to fetch SystemConfigs', details: error.message });
  }
};

export const getSystemConfigByKey = async (req, res) => { // Using 'key' as param for 'opt'
  const { key } = req.params;
  try {
    const config = await prisma.systemConfig.findUnique({ where: { opt: key } });
    if (!config) return res.status(404).json({ error: 'SystemConfig not found for key: ' + key });
    res.status(200).json(config);
  } catch (error) {
    console.error('Error fetching SystemConfig by key:', error);
    res.status(500).json({ error: 'Failed to fetch SystemConfig', details: error.message });
  }
};

export const updateSystemConfig = async (req, res) => {
  const { key } = req.params; // 'opt' field
  const { val, systemType } = req.body;
  try {
    const updatedConfig = await prisma.systemConfig.update({
      where: { opt: key },
      data: {
        val,
        systemType: systemType !== undefined ? parseInt(systemType) : null
      },
    });
    res.status(200).json(updatedConfig);
  } catch (error) {
    if (error.code === 'P2025') return res.status(404).json({ error: 'SystemConfig not found for key: ' + key });
    console.error('Error updating SystemConfig:', error);
    res.status(500).json({ error: 'Failed to update SystemConfig', details: error.message });
  }
};

export const deleteSystemConfig = async (req, res) => {
  const { key } = req.params; // 'opt' field
  try {
    await prisma.systemConfig.delete({ where: { opt: key } });
    res.status(204).send();
  } catch (error) {
    if (error.code === 'P2025') return res.status(404).json({ error: 'SystemConfig not found for key: ' + key });
    console.error('Error deleting SystemConfig:', error);
    res.status(500).json({ error: 'Failed to delete SystemConfig', details: error.message });
  }
};
