import prisma from '../utils/prismaClient.js';
import { sendGenericNotificationEmail } from '../services/emailService.js';

export const createSubject = async (req, res) => {
  const {
    identification, firstName, lastName, reportType, email, phone,
    middleName, maidenName, nickname, handles, gender, dateOfBirth,
    primaryEmail, secondaryEmail, primaryMobile, secondaryMobile,
    educationInstitutes, province, imageFile, status, allowTrait, rushReport,
    companyId, countryId
  } = req.body;
  const createdById = req.user?.id; // Authenticated user

  if (!identification || !firstName || !lastName || !reportType) {
    return res.status(400).json({ error: 'Identification, firstName, lastName, and reportType are required.' });
  }

  try {
    const newSubject = await prisma.subject.create({
      data: {
        identification, firstName, lastName, reportType,
        middleName, maidenName, nickname, handles, gender,
        dateOfBirth: dateOfBirth ? new Date(dateOfBirth) : null,
        primaryEmail: primaryEmail || email,
        secondaryEmail,
        primaryMobile: primaryMobile || phone,
        secondaryMobile,
        educationInstitutes, province, imageFile,
        status: status || 'new_subject',
        allowTrait, rushReport,
        createdById: createdById,
        companyId,
        countryId,
      },
      include: { company: true, country: true, createdBy: { select: { id:true, email:true, firstName:true, lastName:true }} }
    });

    // Notify admins/team leads about new subject (example logic)
    const admins = await prisma.user.findMany({
      where: { roles: { has: 'ROLE_SUPER_ADMIN' } } // Or ROLE_TEAM_LEAD based on company etc.
    });
    admins.forEach(admin => {
      if (admin.id !== createdById && admin.email) { // Don't notify self, ensure email exists
        const subjectLine = `New Subject Created: ${newSubject.firstName} ${newSubject.lastName}`;
        const messageBody = `
          <p>A new subject has been created in the system:</p>
          <ul>
            <li>Name: ${newSubject.firstName} ${newSubject.lastName}</li>
            <li>Identification: ${newSubject.identification}</li>
            <li>Report Type: ${newSubject.reportType}</li>
            <li>Created By: ${req.user?.firstName || 'System'} ${req.user?.lastName || ''}</li>
          </ul>
        `;
        sendGenericNotificationEmail(admin, subjectLine, messageBody, `${process.env.FRONTEND_URL}/subjects/${newSubject.id}`, "View Subject").catch(console.error);
      }
    });

    res.status(201).json(newSubject);
  } catch (error) {
    console.error('Error creating subject:', error);
    // Consider specific error codes, e.g., P2002 for unique constraint if identification should be unique
    res.status(500).json({ error: 'Failed to create subject', details: error.message });
  }
};

export const getAllSubjects = async (req, res) => {
  const {
    firstName, lastName, identification, status, reportType, companyId, countryId, // Filters
    page = 1, limit = 10,
    sortBy = 'createdAt', sortOrder = 'desc'
  } = req.query;

  let pageNum = parseInt(String(page), 10);
  if (isNaN(pageNum) || pageNum < 1) pageNum = 1;
  let limitNum = parseInt(String(limit), 10);
  if (isNaN(limitNum) || limitNum < 1) limitNum = 10;
  const offset = (pageNum - 1) * limitNum;

  const whereClause: any = {};
  const andConditions = [];

  if (firstName) andConditions.push({ firstName: { contains: String(firstName), mode: 'insensitive' } });
  if (lastName) andConditions.push({ lastName: { contains: String(lastName), mode: 'insensitive' } });
  if (identification) andConditions.push({ identification: { contains: String(identification), mode: 'insensitive' } });
  if (status) andConditions.push({ status: String(status) });
  if (reportType) andConditions.push({ reportType: String(reportType) });
  if (countryId) andConditions.push({ countryId: String(countryId) });

  const user = req.user;
  const isSuperAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
  const isAdminUser = user?.roles?.includes('ROLE_ADMIN_USER');
  // const isTeamLead = user?.roles?.includes('ROLE_TEAM_LEAD');

  if ((isSuperAdmin || isAdminUser) && companyId) {
    andConditions.push({ companyId: String(companyId) });
  } else if (!isSuperAdmin && !isAdminUser && user?.companyId) {
    andConditions.push({ companyId: user.companyId });
  } else if (!isSuperAdmin && !isAdminUser && !user?.companyId) {
    // Non-Admins without a company: restricted to subjects they created.
    andConditions.push({ createdById: user.id });
  }
  // Further enhancement for TeamLead:
  // if (isTeamLead && !companyId && user?.teamId) {
  //   const teamCompanies = await prisma.company.findMany({ where: { teamId: user.teamId }, select: { id: true } });
  //   const teamCompanyIds = teamCompanies.map(c => c.id);
  //   if (teamCompanyIds.length > 0) {
  //     andConditions.push({ companyId: { in: teamCompanyIds } });
  //   } else {
  //      andConditions.push({ createdById: user.id }); // Or show none if team has no companies
  //   }
  // }

  if (andConditions.length > 0) {
     whereClause.AND = andConditions;
  }

  try {
    const subjects = await prisma.subject.findMany({
      where: whereClause,
      include: {
        company: { select: { id: true, name: true } },
        country: { select: { name: true } },
        createdBy: { select: { id: true, email: true, firstName: true, lastName: true } },
      },
      orderBy: { [String(sortBy)]: String(sortOrder) },
      skip: offset,
      take: limitNum,
    });

    const totalSubjects = await prisma.subject.count({ where: whereClause });
    const totalPages = Math.ceil(totalSubjects / limitNum);

    res.status(200).json({
      data: subjects,
      total: totalSubjects,
      page: pageNum,
      limit: limitNum,
      totalPages,
    });
  } catch (error) {
    console.error('Error fetching subjects:', error);
    if (error.code === 'P2023' || error.message?.includes('Order Condition Error') || error.message?.includes('InvalidFieldName')) {
        return res.status(400).json({ error: `Invalid sort field provided: ${sortBy}`});
    }
    res.status(500).json({ error: 'Failed to fetch subjects', details: error.message });
  }
};

export const getSubjectById = async (req, res) => {
  const { id } = req.params;
  try {
    const subject = await prisma.subject.findUnique({
      where: { id },
      include: {
        company: true, country: true, createdBy: { select: { id:true, email:true, firstName:true, lastName:true }},
        address: true, profiles: true, qualifications: true, employments: true,
        reports: { include: { createdBy: {select: {firstName: true, lastName: true}} , assignedTo: {select: {firstName: true, lastName: true}} }}
      }
    });
    if (!subject) return res.status(404).json({ error: 'Subject not found' });
    res.status(200).json(subject);
  } catch (error) {
    console.error(`Error fetching subject ${id}:`, error);
    res.status(500).json({ error: 'Failed to fetch subject', details: error.message });
  }
};

export const updateSubject = async (req, res) => {
  const { id } = req.params;
  const subjectData = req.body;

  if (subjectData.dateOfBirth && typeof subjectData.dateOfBirth === 'string') {
     subjectData.dateOfBirth = new Date(subjectData.dateOfBirth);
  } else if (subjectData.dateOfBirth === null || subjectData.dateOfBirth === '') { // Allow unsetting or if empty string from form
    subjectData.dateOfBirth = null;
  }

  // Convert handles and educationInstitutes from comma-separated strings to arrays if needed by schema (Prisma handles Json)
  if (typeof subjectData.handles === 'string') {
    subjectData.handles = subjectData.handles.split(',').map(s => s.trim()).filter(s => s);
  }
  if (typeof subjectData.educationInstitutes === 'string') {
    subjectData.educationInstitutes = subjectData.educationInstitutes.split(',').map(s => s.trim()).filter(s => s);
  }


  try {
    const updatedSubject = await prisma.subject.update({
      where: { id },
      data: {
        ...subjectData,
      },
      include: { company: true, country: true, createdBy: { select: { id:true, email:true, firstName:true, lastName:true }} }
    });
    res.status(200).json(updatedSubject);
  } catch (error) {
    console.error(`Error updating subject ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Subject not found' });
    res.status(500).json({ error: 'Failed to update subject', details: error.message });
  }
};

export const deleteSubject = async (req, res) => {
  const { id } = req.params;
  try {
    await prisma.subject.delete({ where: { id } });
    res.status(204).send();
  } catch (error) {
    console.error(`Error deleting subject ${id}:`, error);
    if (error.code === 'P2025') return res.status(404).json({ error: 'Subject not found' });
    if (error.code === 'P2003') return res.status(409).json({ error: 'Cannot delete subject, it has related records (e.g., reports). Consider archiving instead.' });
    res.status(500).json({ error: 'Failed to delete subject', details: error.message });
  }
};
