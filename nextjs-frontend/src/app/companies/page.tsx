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

interface Company {
  id: string;
  name: string;
  city: string | null;
  // province: string | null; // Removed as per prompt's interface for this view
  country: { name: string } | null;
  team: { id: string; teamName?: string; teamLeader?: {firstName?:string; lastName?:string} } | null;
  createdAt: string;
}
interface CountryMin { id: string; name: string; }
interface TeamMin { id: string; teamName?: string; teamLeader?: {firstName?: string, lastName?: string}; }


export default function CompaniesPage() {
  const router = useRouter(); const pathname = usePathname(); const searchParams = useSearchParams();
  const { user: authUser } = useAuth();

  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true); const [error, setError] = useState<string | null>(null);

  const [filters, setFilters] = useState({
     name: searchParams.get('name') || '',
     city: searchParams.get('city') || '',
     countryId: searchParams.get('countryId') || '',
     teamId: searchParams.get('teamId') || '',
  });
  const [countriesList, setCountriesList] = useState<CountryMin[]>([]);
  const [teamsList, setTeamsList] = useState<TeamMin[]>([]);

  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page') || '1', 10));
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(parseInt(searchParams.get('limit') || '10', 10));
  const [sortBy, setSortBy] = useState(searchParams.get('sortBy') || 'name');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>(searchParams.get('sortOrder') === 'desc' ? 'desc' : 'asc');

  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [itemToDelete, setItemToDelete] = useState<Company | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const isAdmin = authUser?.roles?.includes('ROLE_SUPER_ADMIN') || authUser?.roles?.includes('ROLE_ADMIN_USER');

  useEffect(() => {
    if (isAdmin) {
      // Assuming /api/countries returns an array directly or { data: [] }
      apiClient.get('/countries?limit=500').then(res => setCountriesList(res.data.data || res.data || [])).catch(console.error);
      apiClient.get('/team?limit=500').then(res => setTeamsList(res.data.data || res.data || [])).catch(console.error);
    }
  }, [isAdmin]);

  const fetchCompanies = useCallback(async (pageToFetch = currentPage) => {
    setLoading(true); setError(null);
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            // Ensure only admins can apply certain filters if that's the logic from backend
            if ((key === 'countryId' || key === 'teamId') && !isAdmin) return;
            params.append(key, value);
        }
    });

    params.append('page', pageToFetch.toString()); params.append('limit', itemsPerPage.toString());
    params.append('sortBy', sortBy); params.append('sortOrder', sortOrder);

    const queryString = params.toString();
    if (searchParams.toString() !== queryString) { // Avoid redundant replaces if params are same
        router.replace(`${pathname}?${queryString}`, { scroll: false });
    }

    try {
      const response = await apiClient.get('/companies', { params });
      setCompanies(response.data.data); setTotalPages(response.data.totalPages); setCurrentPage(response.data.page);
    } catch (e: any) { setError(e.response?.data?.error || 'Failed to fetch companies');
    } finally { setLoading(false); }
  }, [filters, itemsPerPage, sortBy, sortOrder, isAdmin, pathname, router, currentPage, searchParams]);

  useEffect(() => { fetchCompanies(currentPage); }, [fetchCompanies, currentPage]);

  const handleFilterChange = (e: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
     setFilters(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };
  const handleApplyFilters = () => {
    setCurrentPage(1);
    if (currentPage !== 1) setCurrentPage(1); else fetchCompanies(1);
  };
  const handleSort = (field: string) => {
     const newSortOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
     setSortBy(field); setSortOrder(newSortOrder); setCurrentPage(1);
  };
  const handleDelete = async () => {
     if (!itemToDelete) return; setIsDeleting(true); setError(null);
     try { await apiClient.delete(`/companies/${itemToDelete.id}`); fetchCompanies(currentPage); // Refetch current page
         setShowDeleteModal(false); setItemToDelete(null);
     } catch(e:any) { setError(e.response?.data?.error || 'Failed to delete company.');}
     finally { setIsDeleting(false); }
  };

  if (loading && companies.length === 0 && currentPage === 1) return <ProtectedRoute><MainLayout><p>Loading companies...</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6"><h1 className="text-3xl font-bold">Companies</h1>{isAdmin && <Link href="/admin/companies/create"><Button variant="primary">Create Company</Button></Link>}</div>
        <Card title="Filters & Search" className="mb-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-4">
                <Input label="Name" name="name" value={filters.name} onChange={handleFilterChange} placeholder="Search by name..." />
                <Input label="City" name="city" value={filters.city} onChange={handleFilterChange} placeholder="Search by city..." />
                {isAdmin && <>
                 <div><label htmlFor="countryIdCompany" className="block text-sm font-medium text-gray-700">Country</label><select id="countryIdCompany" name="countryId" value={filters.countryId} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{countriesList.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></div>
                 <div><label htmlFor="teamIdCompany" className="block text-sm font-medium text-gray-700">Team</label><select id="teamIdCompany" name="teamId" value={filters.teamId} onChange={handleFilterChange} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm"><option value="">All</option>{teamsList.map(t=><option key={t.id} value={t.id}>{t.teamName || (t.teamLeader ? `${t.teamLeader.firstName} ${t.teamLeader.lastName}`: `Team ${t.id}`)}</option>)}</select></div>
                </>}
            </div>
            <div className="p-4 border-t flex space-x-2">
                <Button onClick={handleApplyFilters} variant="primary" disabled={loading}>Apply Filters</Button>
                <Button onClick={() => {
                    setFilters({name:'', city:'', countryId:'', teamId:''});
                    setSortBy('name'); setSortOrder('asc'); setCurrentPage(1);
                }} variant="ghost" disabled={loading}>Reset</Button>
            </div>
        </Card>

        {error && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">{error}</p>}
        {loading && <p className="text-center py-4">Loading companies...</p>}

        {!loading && companies.length === 0 && <p className="text-center py-4">No companies found matching your criteria.</p>}
        {!loading && companies.length > 0 && (
          <>
          <div className="overflow-x-auto bg-white shadow rounded-lg">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  {[{label:'Name', field:'name'}, {label:'City', field:'city'}, {label:'Country', field:'country.name'}, {label:'Team', field:'team.teamName'}, {label:'Created', field:'createdAt'}].map(h=>(
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
                {companies.map(c => (
                  <tr key={c.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{c.name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{c.city || 'N/A'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{c.country?.name || 'N/A'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{c.team?.teamName || (c.team?.teamLeader ? `${c.team.teamLeader.firstName} ${c.team.teamLeader.lastName}`: 'N/A')}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{new Date(c.createdAt).toLocaleDateString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link href={`/companies/${c.id}`} className="text-indigo-600 hover:text-indigo-900">View</Link>
                      {isAdmin && <Link href={`/admin/companies/edit/${c.id}`} className="ml-3 text-yellow-600 hover:text-yellow-900">Edit</Link>}
                      {isAdmin && <Button size="sm" variant="danger" className="ml-3 !py-1 !px-2 !text-xs" onClick={() => { setItemToDelete(c); setShowDeleteModal(true); setError(null);}}>Del</Button>}
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
          <p>Are you sure you want to delete company: <strong>{itemToDelete?.name}</strong>?</p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
            <Button variant="danger" onClick={handleDelete} isLoading={isDeleting}>Delete</Button>
          </div>
        </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
