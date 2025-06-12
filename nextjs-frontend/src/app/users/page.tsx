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

interface User {
  id: string;
  firstName: string | null;
  lastName: string | null;
  email: string;
  enabled: boolean;
  roles: string[]; // Prisma Json can be string[]
  company: { id: string; name: string } | null;
  createdAt: string;
}
interface Company { id: string; name: string; }

const AVAILABLE_ROLES = ['ROLE_USER_STANDARD', 'ROLE_USER_MANAGER', 'ROLE_ANALYST', 'ROLE_TEAM_LEAD', 'ROLE_ADMIN_USER', 'ROLE_SUPER_ADMIN'];

export default function UsersPage() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const { user: authUser } = useAuth();

  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [emailFilter, setEmailFilter] = useState(searchParams.get('email') || '');
  const [firstNameFilter, setFirstNameFilter] = useState(searchParams.get('firstName') || '');
  const [lastNameFilter, setLastNameFilter] = useState(searchParams.get('lastName') || '');
  const [roleFilter, setRoleFilter] = useState(searchParams.get('role') || '');
  const [companyFilter, setCompanyFilter] = useState(searchParams.get('companyId') || '');
  const [companiesList, setCompaniesList] = useState<Company[]>([]);

  const [currentPage, setCurrentPage] = useState(parseInt(searchParams.get('page') || '1', 10));
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(parseInt(searchParams.get('limit') || '10', 10));

  const [sortBy, setSortBy] = useState(searchParams.get('sortBy') || 'createdAt');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>(searchParams.get('sortOrder') === 'asc' ? 'asc' : 'desc');

  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [userToDelete, setUserToDelete] = useState<User | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);

  const isAdmin = authUser?.roles?.includes('ROLE_SUPER_ADMIN') || authUser?.roles?.includes('ROLE_ADMIN_USER');

  useEffect(() => {
    if (isAdmin) {
      apiClient.get('/companies?limit=1000')
         .then(res => setCompaniesList(res.data.data || res.data || [])) // Handle different possible response structures
         .catch(console.error);
    }
  }, [isAdmin]);

  const fetchUsers = useCallback(async (pageToFetch = currentPage) => {
    setLoading(true); setError(null);
    const params = new URLSearchParams();
    if (emailFilter) params.append('email', emailFilter);
    if (firstNameFilter) params.append('firstName', firstNameFilter);
    if (lastNameFilter) params.append('lastName', lastNameFilter);
    if (roleFilter) params.append('role', roleFilter);
    if (isAdmin && companyFilter) params.append('companyId', companyFilter);

    params.append('page', pageToFetch.toString());
    params.append('limit', itemsPerPage.toString());
    params.append('sortBy', sortBy);
    params.append('sortOrder', sortOrder);

    const queryString = params.toString();
    // Only replace if query string actually changes to avoid re-triggering useEffect unnecessarily
    if (searchParams.toString() !== queryString) {
        router.replace(`${pathname}?${queryString}`, { scroll: false });
    }


    try {
      const response = await apiClient.get('/users', { params }); // apiClient now sends params directly
      setUsers(response.data.data);
      setTotalPages(response.data.totalPages);
      setCurrentPage(response.data.page);
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to fetch users');
    } finally {
      setLoading(false);
    }
  }, [emailFilter, firstNameFilter, lastNameFilter, roleFilter, companyFilter, itemsPerPage, sortBy, sortOrder, isAdmin, pathname, router, currentPage, searchParams]); // Added searchParams

  useEffect(() => {
     fetchUsers(currentPage);
  }, [fetchUsers, currentPage]);


  const handleApplyFilters = () => {
     setCurrentPage(1);
     // fetchUsers(1); // This will be triggered by useEffect due to currentPage change if fetchUsers is stable
     // If fetchUsers is not stable due to router.replace, then call it explicitly
     if (currentPage !== 1) setCurrentPage(1); else fetchUsers(1);
  };

  const handleSort = (field: string) => {
     const newSortOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
     setSortBy(field); setSortOrder(newSortOrder); setCurrentPage(1);
     // fetchUsers will be called by useEffect
  };

  const handleDeleteUser = async () => {
    if (!userToDelete) return;
    setIsDeleting(true); setError(null);
    try {
      await apiClient.delete(`/users/${userToDelete.id}`);
      fetchUsers(currentPage);
      setShowDeleteModal(false); setUserToDelete(null);
    } catch (err: any) { setError(err.response?.data?.error || 'Failed to delete user.');
    } finally { setIsDeleting(false); }
  };

  if (loading && users.length === 0 && currentPage === 1) return <ProtectedRoute><MainLayout><p>Loading users...</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">Users</h1>
          {isAdmin && <Link href="/admin/users/create"><Button variant="primary">Create User (Admin)</Button></Link>}
          {/* Note: Regular user creation is /register. Admin might have a different create user form. Assuming /users/create is admin only. */}
        </div>

        <Card title="Filters & Search" className="mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            <Input label="First Name" value={firstNameFilter} onChange={(e) => setFirstNameFilter(e.target.value)} />
            <Input label="Last Name" value={lastNameFilter} onChange={(e) => setLastNameFilter(e.target.value)} />
            <Input label="Email" type="email" value={emailFilter} onChange={(e) => setEmailFilter(e.target.value)} />
            <div>
              <label htmlFor="roleFilter" className="block text-sm font-medium text-gray-700">Role</label>
              <select id="roleFilter" value={roleFilter} onChange={(e) => setRoleFilter(e.target.value)} className="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm">
                <option value="">All Roles</option>
                {AVAILABLE_ROLES.map(role => <option key={role} value={role}>{role.replace('ROLE_', '').replace(/_/g, ' ')}</option>)}
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
          </div>
          <div className="p-4 border-t flex space-x-2">
            <Button onClick={handleApplyFilters} variant="primary" disabled={loading}>Apply Filters</Button>
            <Button onClick={() => {
                setEmailFilter(''); setFirstNameFilter(''); setLastNameFilter('');
                setRoleFilter(''); setCompanyFilter('');
                setSortBy('createdAt'); setSortOrder('desc');
                setCurrentPage(1);
                // fetchUsers(1); // Will be triggered by state changes -> fetchUsers dep update
            }} variant="ghost" disabled={loading}>Reset</Button>
          </div>
        </Card>

        {error && <p className="text-red-500 bg-red-100 p-3 my-2 rounded">{error}</p>}
        {loading && <p className="text-center py-4">Loading users...</p>}

        {!loading && users.length === 0 && <p className="text-center py-4">No users found matching criteria.</p>}

        {!loading && users.length > 0 && (
         <>
         <div className="overflow-x-auto bg-white shadow rounded-lg">
          <table className="min-w-full divide-y divide-gray-200">
             <thead className="bg-gray-50">
                 <tr>
                     {['firstName', 'lastName', 'email', 'createdAt'].map(field => (
                         <th key={field} scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             <button onClick={() => handleSort(field)} className={`hover:text-gray-900 ${sortBy === field ? 'font-bold text-gray-900' : ''}`}>
                                 {field.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())} {sortBy === field ? (sortOrder === 'asc' ? '▲' : '▼') : ''}
                             </button>
                         </th>
                     ))}
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                 </tr>
             </thead>
             <tbody className="bg-white divide-y divide-gray-200">
                 {users.map((user) => (
                     <tr key={user.id} className="hover:bg-gray-50">
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{user.firstName || 'N/A'}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{user.lastName || 'N/A'}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{user.email}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{new Date(user.createdAt).toLocaleDateString()}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{user.company?.name || 'N/A'}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{(user.roles || []).join(', ').replace(/ROLE_/g, '').replace(/_/g, ' ')}</td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm">
                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                {user.enabled ? 'Enabled' : 'Disabled'}
                            </span>
                         </td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                             <Link href={`/users/${user.id}`} className="text-indigo-600 hover:text-indigo-900">View</Link>
                             {isAdmin && <>
                             <Link href={`/admin/users/edit/${user.id}`} className="ml-3 text-yellow-600 hover:text-yellow-900">Edit</Link> {/* Admin edit link */}
                             <Button size="sm" variant="danger" className="ml-3 !py-1 !px-2 !text-xs" onClick={() => { setUserToDelete(user); setShowDeleteModal(true); setError(null); }}>Delete</Button>
                             </>}
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
          <p>Are you sure you want to delete user: <strong>{userToDelete?.email}</strong>?</p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
            <Button variant="danger" onClick={handleDeleteUser} isLoading={isDeleting}>Delete</Button>
          </div>
        </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
