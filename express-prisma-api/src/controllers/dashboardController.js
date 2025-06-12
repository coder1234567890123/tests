import prisma from '../utils/prismaClient.js';

export const getDashboardStats = async (req, res) => {
  try {
    const user = req.user; // from protect middleware
    const userRoles = user.roles || [];
    // Define roles based on your system. Assuming ROLE_SUPER_ADMIN has broadest view.
    const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
    const isAdminUser = userRoles.includes('ROLE_ADMIN_USER'); // Example of another admin-like role
    const isTeamLead = userRoles.includes('ROLE_TEAM_LEAD');
    // const isAnalyst = userRoles.includes('ROLE_ANALYST'); // Not used in current query logic but good to have

    // Counts for reports by status
    const reportStatusCounts = await prisma.report.groupBy({
      by: ['status'],
      _count: { status: true },
      orderBy: { _count: { status: 'desc' } }
    });

    const reportCountsFormatted = reportStatusCounts.reduce((acc, item) => {
      if (item.status) acc[item.status] = item._count.status;
      else acc['unknown_status'] = item._count.status; // Handle null statuses if any
      return acc;
    }, {});

    let subjectCount = 0;
    let companyCount = 0;
    let userCount = 0;

    // Define a base filter for records based on user's company, if applicable and not super admin
    const companyFilter = (isSuperAdmin || isAdminUser) ? {} : (user.companyId ? { companyId: user.companyId } : { id: "__NEVER_MATCH_ANYTHING__" }); // If not admin and no company, show nothing unless createdBy is used
    const userCompanyFilter = (isSuperAdmin || isAdminUser) ? {} : (user.companyId ? { companyId: user.companyId } : { id: user.id }); // For users, match self if no company

    subjectCount = await prisma.subject.count({ where: companyFilter });
    if (isSuperAdmin || isAdminUser) {
        companyCount = await prisma.company.count(); // Super admins see all companies
        userCount = await prisma.user.count(); // Super admins see all users
    } else if (user.companyId) {
        companyCount = 1; // Belongs to one company
        userCount = await prisma.user.count({ where: { companyId: user.companyId }});
    } else { // Regular user not tied to a company
        companyCount = 0;
        userCount = 1; // Themselves
    }

    // Recent Subjects
    const recentSubjectsWhere = isSuperAdmin || isAdminUser ? {} :
        (user.companyId ? { companyId: user.companyId } : { createdById: user.id });
    const recentSubjects = await prisma.subject.findMany({
      take: 5,
      orderBy: { createdAt: 'desc' },
      select: { id: true, firstName: true, lastName: true, createdAt: true, status: true },
      where: recentSubjectsWhere
    });

    // Recent Reports
    let recentReportsWhere = {};
    if (!isSuperAdmin && !isAdminUser) {
        if (user.companyId) {
            recentReportsWhere = { companyId: user.companyId };
        } else { // User not in a company, and not a general admin
            recentReportsWhere = { OR: [{createdById: user.id}, {assignedToId: user.id}] };
        }
    }
    // If isSuperAdmin or isAdminUser, recentReportsWhere remains {} to fetch all.

    const recentReports = await prisma.report.findMany({
         take: 5,
         orderBy: { createdAt: 'desc'},
         select: { id: true, sequence: true, status: true, createdAt: true, subject: { select: { firstName: true, lastName: true}}},
         where: recentReportsWhere
    });

    // Example for myAssignedReports count for team leads or analysts
    let myAssignedReportsCount = 0;
    if (isTeamLead || userRoles.includes('ROLE_ANALYST')) { // Assuming analysts also have assigned reports
        myAssignedReportsCount = await prisma.report.count({
            where: { assignedToId: user.id, NOT: { status: 'COMPLETED' } }
        });
    }

    res.status(200).json({
      reportStatusCounts: reportCountsFormatted,
      totalSubjects: subjectCount,
      totalCompanies: companyCount,
      totalUsers: userCount,
      recentSubjects,
      recentReports,
      myAssignedReports: myAssignedReportsCount > 0 ? myAssignedReportsCount : undefined, // Only show if relevant
    });

  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
    res.status(500).json({ error: 'Failed to fetch dashboard stats', details: error.message });
  }
};
