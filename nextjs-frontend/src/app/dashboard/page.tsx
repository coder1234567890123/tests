'use client';
import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import Card from '@/components/ui/Card';
import { useAuth } from '@/contexts/AuthContext';

interface ReportStatusCounts {
  [key: string]: number;
}
interface RecentItem {
  id: string;
  createdAt: string;
  status?: string | null;
}
interface RecentSubject extends RecentItem {
  firstName: string | null;
  lastName: string | null;
}
interface RecentReport extends RecentItem {
  sequence: string;
  subject?: { firstName: string | null; lastName: string | null; };
}

interface DashboardStats {
  reportStatusCounts?: ReportStatusCounts;
  totalSubjects?: number;
  totalCompanies?: number;
  totalUsers?: number;
  recentSubjects?: RecentSubject[];
  recentReports?: RecentReport[];
  myAssignedReports?: number; // Added this field
}

const StatCard: React.FC<{title: string; value: string | number; linkTo?: string; icon?: React.ReactNode}> = ({ title, value, linkTo, icon }) => (
  <Card className="text-center p-6 hover:shadow-lg transition-shadow">
    {icon && <div className="text-3xl text-blue-500 mb-2 mx-auto w-fit">{icon}</div>}
    <h3 className="text-lg font-semibold text-gray-600">{title}</h3>
    <p className="text-4xl font-bold my-2 text-gray-800">{value}</p>
    {linkTo && <Link href={linkTo} className="text-blue-600 hover:underline text-sm font-medium">View all</Link>}
  </Card>
);


export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { user } = useAuth();

  useEffect(() => {
    const fetchStats = async () => {
      setLoading(true);
      setError(null);
      try {
        const response = await apiClient.get('/dashboard/stats');
        setStats(response.data);
      } catch (e: any) {
        setError(e.response?.data?.error || 'Failed to fetch dashboard stats');
      } finally {
        setLoading(false);
      }
    };
    if (user) {
         fetchStats();
    } else {
        setLoading(false); // If no user, don't attempt to load (ProtectedRoute should handle)
    }
  }, [user]);

  if (loading) return <ProtectedRoute><MainLayout><div className="p-6"><p>Loading dashboard...</p></div></MainLayout></ProtectedRoute>;
  if (error) return <ProtectedRoute><MainLayout><div className="p-6"><p className="text-red-500">{error}</p></div></MainLayout></ProtectedRoute>;
  if (!stats) return <ProtectedRoute><MainLayout><div className="p-6"><p>No dashboard data available.</p></div></MainLayout></ProtectedRoute>;

  const isSuperAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
  const isAdminUser = user?.roles?.includes('ROLE_ADMIN_USER');
  const canViewAllUsersCompanies = isSuperAdmin || isAdminUser;


  return (
    <ProtectedRoute>
      <MainLayout>
        <h1 className="text-3xl font-bold mb-8 text-gray-800">Dashboard</h1>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {stats.myAssignedReports !== undefined && stats.myAssignedReports > 0 && (
            <StatCard title="My Active Reports" value={stats.myAssignedReports} linkTo="/reports?assignedTo=me" />
          )}
          <StatCard title="Total Subjects" value={stats.totalSubjects ?? 0} linkTo="/subjects" />
          {canViewAllUsersCompanies && (
             <>
                 <StatCard title="Total Companies" value={stats.totalCompanies ?? 0} linkTo="/companies" />
                 <StatCard title="Total Users" value={stats.totalUsers ?? 0} linkTo="/users" />
             </>
          )}
        </div>

        {stats.reportStatusCounts && Object.keys(stats.reportStatusCounts).length > 0 && (
          <Card title="Reports by Status" className="mb-8 shadow-sm">
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
              {Object.entries(stats.reportStatusCounts).map(([status, count]) => (
                <div key={status} className="p-4 bg-gray-50 rounded-lg shadow hover:bg-gray-100 transition-colors">
                  <p className="text-gray-700 text-sm font-medium capitalize">{status.replace(/_/g, ' ')}</p>
                  <p className="text-3xl font-bold text-blue-600">{count}</p>
                  {/* Link to filtered list (actual filtering to be implemented later) */}
                  <Link href={`/reports?status=${status}`} className="text-xs text-blue-500 hover:underline">View</Link>
                </div>
              ))}
            </div>
          </Card>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {stats.recentSubjects && stats.recentSubjects.length > 0 && (
             <Card title="Recent Subjects" className="shadow-sm">
                 <ul className="divide-y divide-gray-200">{stats.recentSubjects.map(s => (
                     <li key={s.id} className="py-3 px-4 hover:bg-gray-50 transition-colors">
                         <Link href={`/subjects/${s.id}`} className="text-blue-600 hover:underline font-medium">{s.firstName} {s.lastName}</Link>
                         <div className="text-xs text-gray-500">
                            <span>Status: <span className="font-semibold capitalize">{s.status?.replace(/_/g, ' ') || 'New'}</span></span>
                            <span className="mx-1">|</span>
                            <span>Created: {new Date(s.createdAt).toLocaleDateString()}</span>
                         </div>
                     </li>))}
                 </ul>
             </Card>
          )}
          {stats.recentReports && stats.recentReports.length > 0 && (
             <Card title="Recent Reports" className="shadow-sm">
                  <ul className="divide-y divide-gray-200">{stats.recentReports.map(r => (
                     <li key={r.id} className="py-3 px-4 hover:bg-gray-50 transition-colors">
                         <Link href={`/reports/${r.id}`} className="text-blue-600 hover:underline font-medium">Report {r.sequence}</Link>
                         {r.subject && <span className="text-sm text-gray-700"> for {r.subject.firstName} {r.subject.lastName}</span>}
                         <div className="text-xs text-gray-500">
                            <span>Status: <span className="font-semibold capitalize">{r.status?.replace(/_/g, ' ') || 'New'}</span></span>
                            <span className="mx-1">|</span>
                            <span>Created: {new Date(r.createdAt).toLocaleDateString()}</span>
                         </div>
                     </li>))}
                 </ul>
             </Card>
          )}
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
