import prisma from '../utils/prismaClient.js';

export const createCompany = async (req, res) => {
    const { name, registrationNumber, vatNumber, email, phone, street1, city, countryId } = req.body;
    try {
        const newCompany = await prisma.company.create({
            data: { name, registrationNumber, vatNumber, email, phone, street1, city, countryId },
        });
        res.status(201).json(newCompany);
    } catch (error) {
        console.error('Error creating company:', error);
        res.status(500).json({ error: 'Failed to create company', details: error.message });
    }
};

export const getAllCompanies = async (req, res) => {
    try {
        const companies = await prisma.company.findMany({ include: { country: true } });
        res.status(200).json(companies);
    } catch (error) {
        console.error('Error fetching companies:', error);
        res.status(500).json({ error: 'Failed to fetch companies', details: error.message });
    }
};

export const getCompanyById = async (req, res) => {
    const { id } = req.params;
    try {
        const company = await prisma.company.findUnique({
            where: { id },
            include: { country: true, users: true, team: true }
        });
        if (!company) return res.status(404).json({ error: 'Company not found' });
        res.status(200).json(company);
    } catch (error) {
        console.error(`Error fetching company ${id}:`, error);
        res.status(500).json({ error: 'Failed to fetch company', details: error.message });
    }
};

export const updateCompany = async (req, res) => {
    const { id } = req.params;
    try {
        const updatedCompany = await prisma.company.update({
            where: { id },
            data: req.body, // Update with all provided fields in req.body
        });
        res.status(200).json(updatedCompany);
    } catch (error) {
        console.error(`Error updating company ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Company not found' });
        res.status(500).json({ error: 'Failed to update company', details: error.message });
    }
};

export const deleteCompany = async (req, res) => {
    const { id } = req.params;
    try {
        await prisma.company.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        console.error(`Error deleting company ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Company not found' });
        res.status(500).json({ error: 'Failed to delete company', details: error.message });
    }
};
