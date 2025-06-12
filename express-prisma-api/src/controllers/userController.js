import prisma from '../utils/prismaClient.js';
import bcrypt from 'bcryptjs'; // For password change

// createUser was moved to authController.registerUser

export const getAllUsers = async (req, res) => {
  const {
    email, firstName, lastName, role, companyId, // Filters
    page = 1, limit = 10,
    sortBy = 'createdAt', sortOrder = 'desc'
  } = req.query;

  const pageNum = parseInt(page as string, 10) || 1;
  const limitNum = parseInt(limit as string, 10) || 10;
  const offset = (pageNum - 1) * limitNum;

  const whereClause: any = {};
  const andConditions = [];

  if (email) andConditions.push({ email: { contains: email as string, mode: 'insensitive' } });
  if (firstName) andConditions.push({ firstName: { contains: firstName as string, mode: 'insensitive' } });
  if (lastName) andConditions.push({ lastName: { contains: lastName as string, mode: 'insensitive' } });
  if (role) andConditions.push({ roles: { has: role as string } });

  const reqUser = req.user;
  const isSuperAdmin = reqUser?.roles?.includes('ROLE_SUPER_ADMIN');
  const isAdminUser = reqUser?.roles?.includes('ROLE_ADMIN_USER');

  if ((isSuperAdmin || isAdminUser) && companyId) {
    andConditions.push({ companyId: companyId as string });
  } else if (!isSuperAdmin && !isAdminUser && reqUser?.companyId) {
    andConditions.push({ companyId: reqUser.companyId });
  } else if (!isSuperAdmin && !isAdminUser && !reqUser?.companyId) {
    andConditions.push({ id: reqUser.id });
  }

  if (andConditions.length > 0) {
     whereClause.AND = andConditions;
  }

  try {
    const users = await prisma.user.findMany({
      where: whereClause,
      select: {
         id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true, createdAt: true, updatedAt: true,
         company: { select: { id: true, name: true }},
         team: { select: { id: true, teamName: true }}
      },
      orderBy: { [sortBy as string]: sortOrder as string },
      skip: offset,
      take: limitNum,
    });

    const totalUsers = await prisma.user.count({ where: whereClause });
    const totalPages = Math.ceil(totalUsers / limitNum);

    res.status(200).json({
      data: users,
      total: totalUsers,
      page: pageNum,
      limit: limitNum,
      totalPages,
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    if (error.code === 'P2023' || error.message?.includes('Order Condition Error') || error.message?.includes('InvalidFieldName')) {
        return res.status(400).json({ error: `Invalid sort field provided: ${sortBy}`});
    }
    res.status(500).json({ error: 'Failed to fetch users', details: error.message });
  }
};

export const getUserById = async (req, res) => {
    const { id } = req.params;
    try {
        const user = await prisma.user.findUnique({
            where: { id },
            select: {
                id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true, createdAt: true, updatedAt: true,
                company: { select: { id: true, name: true }},
                team: { select: { id: true, teamName: true }}
            }
        });
        if (!user) return res.status(404).json({ error: 'User not found' });
        res.status(200).json(user);
    } catch (error) {
        console.error(`Error fetching user ${id}:`, error);
        res.status(500).json({ error: 'Failed to fetch user', details: error.message });
    }
};

export const updateUser = async (req, res) => {
    const { id } = req.params;
    const { email, firstName, lastName, roles, enabled, companyId, teamId } = req.body;
    try {
        const updatedUser = await prisma.user.update({
            where: { id },
            data: {
                email,
                firstName,
                lastName,
                roles: roles || undefined,
                enabled,
                companyId,
                teamId
            },
            select: {
                id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true, createdAt: true, updatedAt: true,
                company: { select: { id: true, name: true }},
                team: { select: { id: true, teamName: true }}
            }
        });
        res.status(200).json(updatedUser);
    } catch (error) {
        console.error(`Error updating user ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'User not found' });
        if (error.code === 'P2002' && error.meta?.target?.includes('email')) {
            return res.status(409).json({ error: 'Email already in use.' });
        }
        res.status(500).json({ error: 'Failed to update user', details: error.message });
    }
};

export const deleteUser = async (req, res) => {
    const { id } = req.params;
    try {
        await prisma.user.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        console.error(`Error deleting user ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'User not found' });
        res.status(500).json({ error: 'Failed to delete user', details: error.message });
    }
};

// New Profile Functions
export const getMyProfile = async (req, res) => {
  const userId = req.user?.id;
  if (!userId) return res.status(401).json({ error: 'Not authorized.' });

  try {
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true, email: true, firstName: true, lastName: true,
        roles: true, enabled: true, createdAt: true, updatedAt: true,
        primaryMobile: true, // Add this
        company: { select: { id: true, name: true } },
        team: { select: { id: true, teamName: true } }
      }
    });
    if (!user) return res.status(404).json({ error: 'User profile not found.' });
    res.status(200).json(user);
  } catch (error) {
    console.error(`Error fetching profile for user ${userId}:`, error);
    res.status(500).json({ error: 'Failed to fetch profile', details: error.message });
  }
};

export const updateMyProfile = async (req, res) => {
  const userId = req.user?.id;
  if (!userId) return res.status(401).json({ error: 'Not authorized.' });

  const { firstName, lastName, email, primaryMobile } = req.body;

  // Allow updating any subset of these fields
  if (Object.keys(req.body).filter(k => ['firstName', 'lastName', 'email', 'primaryMobile'].includes(k)).length === 0) {
     return res.status(400).json({ error: "No updatable fields provided."});
  }

  const dataToUpdate: any = {};
  if (firstName !== undefined) dataToUpdate.firstName = firstName;
  if (lastName !== undefined) dataToUpdate.lastName = lastName;
  if (email !== undefined) dataToUpdate.email = email; // Allow email update, validation handled by Prisma + DB
  if (primaryMobile !== undefined) dataToUpdate.primaryMobile = primaryMobile; // Allow sending empty string to clear

  try {
    const updatedUser = await prisma.user.update({
      where: { id: userId },
      data: dataToUpdate,
      select: { id: true, email: true, firstName: true, lastName: true, roles: true, enabled: true, primaryMobile: true, companyId: true, teamId: true }
    });
    res.status(200).json(updatedUser);
  } catch (error) {
    console.error(`Error updating profile for user ${userId}:`, error);
    if (error.code === 'P2002' && error.meta?.target?.includes('email')) {
         return res.status(409).json({ error: 'This email address is already in use.' });
    }
    if (error.code === 'P2025') return res.status(404).json({ error: 'User profile not found.' });
    res.status(500).json({ error: 'Failed to update profile', details: error.message });
  }
};

export const changeMyPassword = async (req, res) => {
  const userId = req.user?.id;
  if (!userId) return res.status(401).json({ error: 'Not authorized.' });

  const { currentPassword, newPassword } = req.body;
  if (!currentPassword || !newPassword) {
    return res.status(400).json({ error: 'Current password and new password are required.' });
  }
  if (newPassword.length < 6) {
     return res.status(400).json({ error: 'New password must be at least 6 characters long.' });
  }

  try {
    const user = await prisma.user.findUnique({ where: { id: userId } });
    if (!user) return res.status(404).json({ error: 'User not found.' });

    const isMatch = await bcrypt.compare(currentPassword, user.password);
    if (!isMatch) return res.status(400).json({ error: 'Incorrect current password.' });

    const hashedNewPassword = await bcrypt.hash(newPassword, 10);
    await prisma.user.update({
      where: { id: userId },
      data: { password: hashedNewPassword },
    });
    res.status(200).json({ message: 'Password changed successfully.' });
  } catch (error) {
    console.error(`Error changing password for user ${userId}:`, error);
    res.status(500).json({ error: 'Failed to change password', details: error.message });
  }
};
