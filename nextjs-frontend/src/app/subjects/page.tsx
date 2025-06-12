'use client';
import React, { useEffect, useState, useCallback, ChangeEvent } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams, usePathname } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import Input from '@/components/ui/Input';
import Modal from '@/components/ui/Modal';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

interface Subject {
  id: string; firstName: string | null; lastName: string | null; identification: string;
  status: string | null; reportType: string;
  company: { id: string; name: string; } | null;
  country: { name: string; } | null;
  createdAt: string;
}
interface CompanyMin { id: string; name: string; }
interface CountryMin { id: string; name: string; }
const SUBJECT_STATUSES = ['new_subject', 'new_request', 'search_started', 'search_completed', 'validated', 'under_investigation', 'investigation_completed', 'team_lead_approved', 'completed', 'abandoned'];
const REPORT_TYPES = ['basic', 'standard', 'full', 'high_profile', 'all'];


export default function SubjectsPage() {
  const router = useRouter(); const pathname = usePathname(); const searchParams = useSearchParams();
  const { user: authUser } = useAuth();
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [loading, setLoading] = useState(true); const [error, setError] = useState<string | null>(null);

  const [filters, setFilters] = useState({
     firstName: searchParams.get('firstName') || '', lastName: searchParams.get('lastName') || '',
     identification: searchParams.get('identification') || '', status: searchParams.get('status') || '',
     reportType: searchParams.get('reportType') || '', companyId: searchParams.get('companyId') || '',
     countryId: searchParams.get('countryId') || '',
  });
  const [companiesList, setCompaniesList] = useState<CompanyMin[]>([]);
  const [countriesList, setCountriesList] = useState<CountryMin[]>([]);

  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page') || '1', 10));
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(parseInt(searchParams.get('limit') || '10', 10));
  const [sortBy, setSortBy] = useState(searchParams.get('sortBy') || 'createdAt');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>(searchParams.get('sortOrder') === 'asc' ? 'asc' : 'desc'); // Default to 'asc' for names, 'desc' for dates

  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [itemToDelete, setItemToDelete] = useState<Subject | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const isAdmin = authUser?.roles?.includes('ROLE_SUPER_ADMIN') || authUser?.roles?.includes('ROLE_ADMIN_USER');

  useEffect(() => {
     apiClient.get('/countries?limit=500').then(res => setCountriesList(res.data.data || res.data || [])).catch(console.error);
     if (isAdmin) {
         apiClient.get('/companies?limit=1000').then(res => setCompaniesList(res.data.data || res.data || [])).catch(console.error);
     }
  }, [isAdmin]);

  const fetchSubjects = useCallback(async (pageToFetch = currentPage) => {
    setLoading(true); setError(null);
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            if (key === 'companyId' && !isAdmin && authUser?.companyId) { // Non-admin, if has company, can only see their own
                params.append(key, authUser.companyId); // Force their own company
            } else if (key === 'companyId' && !isAdmin && !authUser?.companyId) {
                // Non-admin, no company: backend will restrict to their own created. Don't send companyId.
                return;
            }
            else {
                params.append(key, value);
            }
        }
    });

    params.append('page', pageToFetch.toString()); params.append('limit', itemsPerPage.toString());
    params.append('sortBy', sortBy); params.append('sortOrder', sortOrder);

    const queryString = params.toString();
    if (searchParams.toString() !== queryString) {
        router.replace(`${pathname}?${queryString}`, { scroll: false });
    }

    try {
      const response = await apiClient.get('/subjects', { params });
      setSubjects(response.data.data); setTotalPages(response.data.totalPages); setCurrentPage(response.data.page);
    } catch (e: any) { setError(e.response?.data?.error || 'Failed to fetch subjects');
    } finally { setLoading(false); }
  }, [filters, itemsPerPage, sortBy, sortOrder, isAdmin, pathname, router, currentPage, authUser?.companyId, searchParams]); // Added authUser.companyId

  useEffect(() => { fetchSubjects(currentPage); }, [fetchSubjects, currentPage]);

  const handleFilterChange = (e: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
     setFilters(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };
  const handleApplyFilters = () => {
    setCurrentPage(1);
    if (currentPage !== 1) setCurrentPage(1); else fetchSubjects(1);
  };
  const handleSort = (field: string) => {
     const newSortOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
     setSortBy(field); setSortOrder(newSortOrder); setCurrentPage(1);
  };
  const handleDelete = async () => {
     if (!itemToDelete) return; setIsDeleting(true); setError(null);
     try { await apiClient.delete(`/subjects/${itemToDelete.id}`); fetchSubjects(currentPage); // Refetch
         setShowDeleteModal(false); setItemToDelete(null);
     } catch(e:any) { setError(e.response?.data?.error || 'Failed to delete subject.');}
     finally { setIsDeleting(false); }
  };

  if (loading && subjects.length === 0 && currentPage === 1) return <ProtectedRoute><MainLayout><p>Loading subjects...</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6"><h1 className="text-3xl font-bold">Subjects</h1><Link href="/subjects/create"><Button variant="primary">Create Subject</Button></Link></div>
        <Card title="Filters & Search" className="mb-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-4">
                <Input label="First Name" name="firstName" value={filters.firstName} onChange={handleFilterChange} />
                <Input label="Last Name" name="lastName" value={filters.lastName} onChange={handleFilterChange} />
                <Input label="Identification" name="identification" value={filters.identification} onChange={handleFilterChange} />
                <div><label htmlFor="statusSubject" className="block text-sm font-medium text-gray-700">Status</label><select id="statusSubject" name="status" value={filters.status} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{SUBJECT_STATUSES.map(s=><option key={s} value={s}>{s.replace(/_/g, ' ').toUpperCase()}</option>)}</select></div>
                <div><label htmlFor="reportTypeSubject" className="block text-sm font-medium text-gray-700">Report Type</label><select id="reportTypeSubject" name="reportType" value={filters.reportType} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{REPORT_TYPES.map(rt=><option key={rt} value={rt}>{rt.charAt(0).toUpperCase() + rt.slice(1)}</option>)}</select></div>
                {isAdmin && <div><label htmlFor="companyIdSubject" className="block text-sm font-medium text-gray-700">Company</label><select id="companyIdSubject" name="companyId" value={filters.companyId} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{companiesList.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></div>}
                <div><label htmlFor="countryIdSubject" className="block text-sm font-medium text-gray-700">Country</label><select id="countryIdSubject" name="countryId" value={filters.countryId} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{countriesList.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></div>
            </div>
            <div className="p-4 border-t flex space-x-2">
                <Button onClick={handleApplyFilters} variant="primary" disabled={loading}>Apply Filters</Button>
                <Button onClick={() => {
                    setFilters({firstName:'', lastName:'', identification:'', status:'', reportType:'', companyId:'', countryId:''});
                    setSortBy('createdAt'); setSortOrder('desc'); setCurrentPage(1);
                }} variant="ghost" disabled={loading}>Reset</Button>
            </div>
        </Card>

        {error && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">{error}</p>}
        {loading && <p className="text-center py-4">Loading subjects...</p>}
        {!loading && subjects.length === 0 && <p className="text-center py-4">No subjects found matching your criteria.</p>}

        {!loading && subjects.length > 0 && (
          <>
          <div className="overflow-x-auto bg-white shadow rounded-lg">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  {[{label:'Name', field:'firstName'}, {label:'Identification', field:'identification'}, {label:'Company', field:'company.name'}, {label:'Country', field:'country.name'}, {label:'Status', field:'status'}, {label:'Report Type', field:'reportType'}, {label:'Created', field:'createdAt'}].map(h=>(
                    <th key={h.field} scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button onClick={() => handleSort(h.field)} className={`hover:text-gray-900 ${sortBy === h.field ? 'font-bold text-gray-900' : ''}`}>
                           {h.label} {sortBy === h.field ? (sortOrder === 'asc' ? '▲' : '▼') : ''}
                        </button>
                    </th>
                  ))}
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {subjects.map(s => (
                  <tr key={s.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{`${s.firstName || ''} ${s.lastName || ''}`}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{s.identification}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{s.company?.name || 'N/A'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{s.country?.name || 'N/A'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span className="font-semibold uppercase">{s.status?.replace(/_/g, ' ') || 'N/A'}</span></td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{s.reportType}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{new Date(s.createdAt).toLocaleDateString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link href={`/subjects/${s.id}`} className="text-indigo-600 hover:text-indigo-900">View</Link>
                      <Link href={`/subjects/edit/${s.id}`} className="ml-3 text-yellow-600 hover:text-yellow-900">Edit</Link>
                      {isAdmin && <Button size="sm" variant="danger" className="ml-3 !py-1 !px-2 !text-xs" onClick={() => { setItemToDelete(s); setShowDeleteModal(true); setError(null);}}>Del</Button>}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          </>
        )}
         {totalPages > 1 && (
           <div className="mt-6 flex justify-between items-center">
             <Button onClick={() => setCurrentPage(p => Math.max(1, p - 1))} disabled={currentPage === 1 || loading}>Previous</Button>
             <div className="flex items-center space-x-2">
                <span className="text-sm text-gray-700">Page {currentPage} of {totalPages}</span>
                <select value={itemsPerPage} onChange={(e) => { setItemsPerPage(parseInt(e.target.value)); setCurrentPage(1); }} className="p-2 border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="5">5/page</option><option value="10">10/page</option>
                    <option value="20">20/page</option><option value="50">50/page</option>
                </select>
             </div>
             <Button onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))} disabled={currentPage === totalPages || loading}>Next</Button>
           </div>
         )}
         <Modal isOpen={showDeleteModal} onClose={() => {setShowDeleteModal(false); setError(null);}} title="Confirm Delete">
           <p>Are you sure you want to delete subject: <strong>{itemToDelete?.firstName} {itemToDelete?.lastName}</strong> (ID: {itemToDelete?.identification})?</p>
           <div className="mt-4 flex justify-end space-x-2">
             <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
             <Button variant="danger" onClick={handleDelete} isLoading={isDeleting}>Delete</Button>
           </div>
         </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
