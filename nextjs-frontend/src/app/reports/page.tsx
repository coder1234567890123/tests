'use client';
import React, { useEffect, useState, useCallback, ChangeEvent } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams, usePathname } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import Input from '@/components/ui/Input';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';
import Modal from '@/components/ui/Modal'; // Keep Modal if delete functionality is still here


// REPORT_STATUSES should ideally be shared or fetched from a common source/API
const REPORT_STATUSES = {
  NEW_REQUEST: 'new_request', NEEDS_APPROVAL: 'needs_approval', REPORT_TYPE_APPROVED: 'report_type_approved',
  UNASSIGNED: 'unassigned', SEARCH_STARTED: 'search_started', SEARCH_COMPLETED: 'search_completed',
  VALIDATED: 'validated', UNDER_INVESTIGATION: 'under_investigation',
  INVESTIGATION_COMPLETED: 'investigation_completed', TEAM_LEAD_APPROVED: 'team_lead_approved',
  COMPLETED: 'completed', ABANDONED_REQUEST: 'abandoned_request', ABANDONED: 'abandoned',
};
const REQUEST_TYPES = ['normal', 'rush', 'test'];

interface Report {
  id: string; sequence: string; status: string | null; requestType: string | null;
  subject: { id: string; firstName: string; lastName: string; } | null;
  company: { id: string; name: string; } | null;
  dueDate: string | null; createdAt: string;
}
interface Company { id: string; name: string; }

export default function ReportsPage() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const { user } = useAuth();

  const [reports, setReports] = useState<Report[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [statusFilter, setStatusFilter] = useState(searchParams.get('status') || '');
  const [requestTypeFilter, setRequestTypeFilter] = useState(searchParams.get('requestType') || '');
  const [companyFilter, setCompanyFilter] = useState(searchParams.get('companyId') || '');
  const [dateFromFilter, setDateFromFilter] = useState(searchParams.get('dateFrom') || '');
  const [dateToFilter, setDateToFilter] = useState(searchParams.get('dateTo') || '');
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
  const [companiesList, setCompaniesList] = useState<Company[]>([]);

  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page') || '1', 10));
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(parseInt(searchParams.get('limit') || '10', 10));

  const [sortBy, setSortBy] = useState(searchParams.get('sortBy') || 'createdAt');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>(searchParams.get('sortOrder') === 'asc' ? 'asc' : 'desc');

  // States for delete functionality (if keeping from previous version)
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [reportToDelete, setReportToDelete] = useState<Report | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);

  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN') || user?.roles?.includes('ROLE_ADMIN_USER');

  useEffect(() => {
    if (isAdmin) {
      apiClient.get('/companies?limit=1000')
        .then(res => {
            // Adapt to potential pagination in company list response
            if (res.data && Array.isArray(res.data.data)) {
                setCompaniesList(res.data.data);
            } else if (Array.isArray(res.data)) {
                setCompaniesList(res.data);
            }
        })
        .catch(console.error);
    }
  }, [isAdmin]);

  const fetchReports = useCallback(async (pageToFetch = 1) => { // Default to page 1 if not specified
    setLoading(true); setError(null);
    const params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (requestTypeFilter) params.append('requestType', requestTypeFilter);
    if (isAdmin && companyFilter) params.append('companyId', companyFilter); // companyFilter only applied if admin
    if (dateFromFilter) params.append('dateFrom', dateFromFilter);
    if (dateToFilter) params.append('dateTo', dateToFilter);
    if (searchTerm) params.append('search', searchTerm);
    params.append('page', pageToFetch.toString());
    params.append('limit', itemsPerPage.toString());
    params.append('sortBy', sortBy);
    params.append('sortOrder', sortOrder);

    const queryString = params.toString();
    router.replace(`${pathname}?${queryString}`, { scroll: false });

    try {
      const response = await apiClient.get(`/reports?${queryString}`);
      setReports(response.data.data);
      setTotalPages(response.data.totalPages);
      setCurrentPage(response.data.page); // Ensure currentPage is updated from response
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to fetch reports');
    } finally {
      setLoading(false);
    }
  // Removed currentPage from dependency array to prevent potential loop with useEffect below
  // It's passed as an argument to fetchReports when needed
  }, [statusFilter, requestTypeFilter, companyFilter, dateFromFilter, dateToFilter, searchTerm, itemsPerPage, sortBy, sortOrder, isAdmin, pathname, router]);

  useEffect(() => {
    // This useEffect will trigger fetchReports whenever any of its dependencies change.
    // currentPage changes will be handled by explicit calls to fetchReports(newPage) from pagination handlers.
    fetchReports(currentPage);
  }, [fetchReports, currentPage]);

 const handleApplyFilters = () => {
     setCurrentPage(1); // Reset to first page
     fetchReports(1); // Call fetchReports with page 1
 };

 const handleSort = (field: string) => {
     const newSortOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
     setSortBy(field);
     setSortOrder(newSortOrder);
     setCurrentPage(1); // Reset to first page
     // fetchReports will be called by the useEffect above due to sortBy/sortOrder change in its dependency (fetchReports itself)
 };

 const handleDeleteReport = async () => { // Keep if delete functionality is on this page
    if (!reportToDelete) return;
    setIsDeleting(true); setError(null);
    try {
      await apiClient.delete(`/reports/${reportToDelete.id}`);
      fetchReports(currentPage); // Re-fetch current page after delete
      setShowDeleteModal(false); setReportToDelete(null);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to delete report.');
    } finally {
      setIsDeleting(false);
    }
  };

  if (loading && reports.length === 0 && currentPage === 1) return <ProtectedRoute><MainLayout><p>Loading reports...</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">Reports</h1>
          <Link href="/reports/create"><Button variant="primary">Create Report</Button></Link>
        </div>

        <Card title="Filters & Search" className="mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            <Input label="Search (Sequence, Subject)" value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder="Enter search term..." />
            <div>
              <label htmlFor="statusFilter" className="block text-sm font-medium text-gray-700">Status</label>
              <select id="statusFilter" value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm">
                <option value="">All Statuses</option>
                {Object.entries(REPORT_STATUSES).map(([key, value]) => <option key={key} value={value}>{value.replace(/_/g, ' ').toUpperCase()}</option>)}
              </select>
            </div>
            <div>
              <label htmlFor="requestTypeFilter" className="block text-sm font-medium text-gray-700">Request Type</label>
              <select id="requestTypeFilter" value={requestTypeFilter} onChange={(e) => setRequestTypeFilter(e.target.value)} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm">
                <option value="">All Types</option>
                {REQUEST_TYPES.map(type => <option key={type} value={type}>{type.charAt(0).toUpperCase() + type.slice(1)}</option>)}
              </select>
            </div>
            {isAdmin && (
             <div>
                 <label htmlFor="companyFilter" className="block text-sm font-medium text-gray-700">Company</label>
                 <select id="companyFilter" value={companyFilter} onChange={(e) => setCompanyFilter(e.target.value)} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm">
                 <option value="">All Companies</option>
                 {companiesList.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                 </select>
             </div>
            )}
            <Input label="Date From" type="date" value={dateFromFilter} onChange={(e) => setDateFromFilter(e.target.value)} />
            <Input label="Date To" type="date" value={dateToFilter} onChange={(e) => setDateToFilter(e.target.value)} />
          </div>
          <div className="p-4 border-t flex items-center space-x-2">
             <Button onClick={handleApplyFilters} variant="primary" disabled={loading}>Apply Filters</Button>
             <Button onClick={() => { // Reset all filters
                setStatusFilter(''); setRequestTypeFilter(''); setCompanyFilter('');
                setDateFromFilter(''); setDateToFilter(''); setSearchTerm('');
                setSortBy('createdAt'); setSortOrder('desc');
                setCurrentPage(1);
                // fetchReports(1); // fetchReports will be called by useEffect due to state changes
             }} variant="ghost" disabled={loading}>Reset Filters</Button>
          </div>
        </Card>

        {error && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">{error}</p>}
        {loading && <p className="text-center py-4">Loading reports...</p>}

        {!loading && reports.length === 0 ? (
            <p className="text-center py-4">No reports found matching your criteria.</p>
        ) : !loading && (
          <>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <button onClick={() => handleSort('sequence')} className={`hover:text-gray-900 ${sortBy === 'sequence' ? 'font-bold text-gray-900' : ''}`}>Sequence {sortBy === 'sequence' ? (sortOrder === 'asc' ? '▲' : '▼') : ''}</button>
                    </th>
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <button onClick={() => handleSort('status')} className={`hover:text-gray-900 ${sortBy === 'status' ? 'font-bold text-gray-900' : ''}`}>Status {sortBy === 'status' ? (sortOrder === 'asc' ? '▲' : '▼') : ''}</button>
                    </th>
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    {isAdmin && <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>}
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <button onClick={() => handleSort('createdAt')} className={`hover:text-gray-900 ${sortBy === 'createdAt' ? 'font-bold text-gray-900' : ''}`}>Created {sortBy === 'createdAt' ? (sortOrder === 'asc' ? '▲' : '▼') : ''}</button>
                    </th>
                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {reports.map((report) => (
                    <tr key={report.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{report.sequence}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{report.subject ? `${report.subject.firstName} ${report.subject.lastName}` : 'N/A'}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span className="font-semibold uppercase">{report.status?.replace(/_/g, ' ') || 'N/A'}</span></td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{report.requestType || 'N/A'}</td>
                      {isAdmin && <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{report.company?.name || 'N/A'}</td>}
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{new Date(report.createdAt).toLocaleDateString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <Link href={`/reports/${report.id}`}><Button size="sm" variant="ghost">View</Button></Link>
                        <Link href={`/reports/edit/${report.id}`}><Button size="sm" variant="secondary">Edit</Button></Link>
                        <Button size="sm" variant="danger" onClick={() => { setReportToDelete(report); setShowDeleteModal(true); setError(null); }}>Delete</Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {totalPages > 1 && (
              <div className="mt-6 flex justify-between items-center">
                <Button onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))} disabled={currentPage === 1 || loading}>Previous</Button>
                <div className="flex items-center space-x-2">
                    <span>Page {currentPage} of {totalPages}</span>
                    <select value={itemsPerPage} onChange={(e) => { setItemsPerPage(parseInt(e.target.value)); setCurrentPage(1); }} className="p-2 border-gray-300 rounded-md shadow-sm">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
                <Button onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))} disabled={currentPage === totalPages || loading}>Next</Button>
              </div>
            )}
          </>
        )}
        <Modal isOpen={showDeleteModal} onClose={() => {setShowDeleteModal(false); setError(null);}} title="Confirm Delete">
          <p>Are you sure you want to delete report: <strong>{reportToDelete?.sequence}</strong>?</p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
            <Button variant="danger" onClick={handleDeleteReport} isLoading={isDeleting}>Delete</Button>
          </div>
        </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
