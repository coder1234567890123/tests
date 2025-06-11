import prisma from '../utils/prismaClient.js';

// export const createUser = async (req, res) => {
//     const { email, password, firstName, lastName, roles } = req.body;
//     try {
//         const newUser = await prisma.user.create({
//             data: { email, password, firstName, lastName, roles },
//         });
//         res.status(201).json(newUser);
//     } catch (error) {
//         console.error('Error creating user:', error);
//         res.status(500).json({ error: 'Failed to create user', details: error.message });
//     }
// };

export const getAllUsers = async (req, res) => {
    try {
        const users = await prisma.user.findMany();
        res.status(200).json(users);
    } catch (error) {
        console.error('Error fetching users:', error);
        res.status(500).json({ error: 'Failed to fetch users', details: error.message });
    }
};

export const getUserById = async (req, res) => {
    const { id } = req.params;
    try {
        const user = await prisma.user.findUnique({ where: { id } });
        if (!user) return res.status(404).json({ error: 'User not found' });
        res.status(200).json(user);
    } catch (error) {
        console.error(`Error fetching user ${id}:`, error);
        res.status(500).json({ error: 'Failed to fetch user', details: error.message });
    }
};

export const updateUser = async (req, res) => {
    const { id } = req.params;
    const { email, firstName, lastName, roles, enabled } = req.body;
    try {
        const updatedUser = await prisma.user.update({
            where: { id },
            data: { email, firstName, lastName, roles, enabled },
        });
        res.status(200).json(updatedUser);
    } catch (error) {
        console.error(`Error updating user ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'User not found' });
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
