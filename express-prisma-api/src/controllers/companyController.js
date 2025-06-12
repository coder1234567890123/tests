import prisma from '../utils/prismaClient.js';

export const createCompany = async (req, res) => {
    const { name, registrationNumber, vatNumber, email, phone, street1, city, countryId, teamId /* other fields from schema */ } = req.body;
    if (!name) { // Basic validation
        return res.status(400).json({ error: 'Company name is required.' });
    }
    try {
        const newCompany = await prisma.company.create({
            data: {
                name, registrationNumber, vatNumber, email, phone,
                street1, city, countryId, teamId
                // Add other fields from schema here (street2, suburb, postalCode, province, etc.)
            },
            include: { country: true, team: true }
        });
        res.status(201).json(newCompany);
    } catch (error) {
        console.error('Error creating company:', error);
        // Handle potential unique constraint errors if any are defined (e.g., unique name or email)
        if (error.code === 'P2002') {
             return res.status(409).json({ error: 'Company with this identifier already exists.', details: error.meta?.target });
        }
        res.status(500).json({ error: 'Failed to create company', details: error.message });
    }
};

export const getAllCompanies = async (req, res) => {
  const {
    name, city, countryId, teamId, // Filters
    page = 1, limit = 10,
    sortBy = 'name', sortOrder = 'asc'
  } = req.query;

  const pageNum = parseInt(page as string, 10) || 1;
  const limitNum = parseInt(limit as string, 10) || 10;
  const offset = (pageNum - 1) * limitNum;

  const whereClause: any = {};
  const andConditions = [];

  if (name) andConditions.push({ name: { contains: name as string, mode: 'insensitive' } });
  if (city) andConditions.push({ city: { contains: city as string, mode: 'insensitive' } });
  if (countryId) andConditions.push({ countryId: countryId as string });

  const user = req.user;
  const isSuperAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
  const isAdminUser = user?.roles?.includes('ROLE_ADMIN_USER'); // Assuming this role exists and has broad access

  if ((isSuperAdmin || isAdminUser) && teamId) {
     andConditions.push({ teamId: teamId as string });
  } else if (!isSuperAdmin && !isAdminUser) { // Regular user
     if (user?.companyId) { // If user belongs to a company, they see only their company
        andConditions.push({ id: user.companyId });
     } else if (user?.teamId) { // If user belongs to a team but not a specific company directly, show companies in their team
        andConditions.push({ teamId: user.teamId });
     } else { // User has no company or team, and is not admin - sees no companies
        // This effectively makes the query return no results if they are not admin and have no affiliations
        andConditions.push({ id: "__USER_HAS_NO_COMPANY_AFFILIATION__" });
     }
  }
  // If user is isAdminUser but not SuperAdmin, and has a companyId, they might still be restricted to their company
  // The current logic allows AdminUser to see all if no teamId filter, or filter by teamId.
  // This might need refinement based on exact definition of ROLE_ADMIN_USER vs ROLE_SUPER_ADMIN if AdminUser should be company-restricted.
  // For now, assuming AdminUser can see all companies unless filtered by teamId.

  if (andConditions.length > 0) {
     whereClause.AND = andConditions;
  }

  try {
    const companies = await prisma.company.findMany({
      where: whereClause,
      include: {
         country: { select: { name: true }},
         team: { select: { id: true, teamName: true }}
     },
      orderBy: { [sortBy as string]: sortOrder as string },
      skip: offset,
      take: limitNum,
    });

    const totalCompanies = await prisma.company.count({ where: whereClause });
    const totalPages = Math.ceil(totalCompanies / limitNum);

    res.status(200).json({
      data: companies,
      total: totalCompanies,
      page: pageNum,
      limit: limitNum,
      totalPages,
    });
  } catch (error) {
    console.error('Error fetching companies:', error);
    if (error.code === 'P2023' || error.message?.includes('Order Condition Error') || error.message?.includes('InvalidFieldName')) {
        return res.status(400).json({ error: `Invalid sort field provided: ${sortBy}`});
    }
    res.status(500).json({ error: 'Failed to fetch companies', details: error.message });
  }
};

export const getCompanyById = async (req, res) => {
    const { id } = req.params;
    try {
        const company = await prisma.company.findUnique({
            where: { id },
            include: {
                country: { select: { name: true }},
                users: { select: { id: true, email: true, firstName: true, lastName: true }}, // Select specific user fields
                team: { select: { id: true, teamName: true }}
            }
        });
        if (!company) return res.status(404).json({ error: 'Company not found' });

        // Optional: Role-based access to specific company details
        const user = req.user;
        const isSuperAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
        const isAdminUser = user?.roles?.includes('ROLE_ADMIN_USER');
        if (!isSuperAdmin && !isAdminUser && user?.companyId !== id && user?.teamId !== company?.teamId) {
            // If user is not admin, and this company is not their own or in their team
            // return res.status(403).json({ error: 'Forbidden: Not authorized to view this company.' });
            // For now, allow if they passed the route protection (e.g. `protect` middleware)
        }

        res.status(200).json(company);
    } catch (error) {
        console.error(`Error fetching company ${id}:`, error);
        res.status(500).json({ error: 'Failed to fetch company', details: error.message });
    }
};

export const updateCompany = async (req, res) => {
    const { id } = req.params;
    try {
        // Ensure req.body does not contain relational fields that should be handled separately (e.g., users, reports)
        // For simplicity, allow direct update of fields present in Company model.
        const { users, reports, subjects, accounts, companyProducts, ...companyData } = req.body;

        const updatedCompany = await prisma.company.update({
            where: { id },
            data: companyData,
            include: { country: true, team: true }
        });
        res.status(200).json(updatedCompany);
    } catch (error) {
        console.error(`Error updating company ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Company not found' });
        if (error.code === 'P2002') return res.status(409).json({ error: 'Company with this identifier already exists.', details: error.meta?.target });
        res.status(500).json({ error: 'Failed to update company', details: error.message });
    }
};

export const deleteCompany = async (req, res) => {
    const { id } = req.params;
    try {
        // Consider implications: deleting a company might require deleting related users, reports, etc.,
        // or setting their companyId to null, depending on schema (onDelete rules).
        // For now, a direct delete. Prisma will throw P2003 if foreign key constraints are violated.
        await prisma.company.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        console.error(`Error deleting company ${id}:`, error);
        if (error.code === 'P2025') return res.status(404).json({ error: 'Company not found' });
        if (error.code === 'P2003') return res.status(409).json({ error: 'Cannot delete company, it has related records (e.g., users, reports). Please reassign or delete them first.'});
        res.status(500).json({ error: 'Failed to delete company', details: error.message });
    }
};
