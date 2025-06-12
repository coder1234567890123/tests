'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

// Inline ProtectedRoute logic
// import { useAuth } from '@/contexts/AuthContext'; // No longer needed directly here

interface UserData {
  email: string;
  firstName: string | null;
  lastName: string | null;
  roles: string[];
  enabled: boolean;
}

export default function EditUserPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [user, setUser] = useState<UserData | null>(null);
  const [formData, setFormData] = useState<Partial<UserData>>({});
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);

  useEffect(() => {
    // The ProtectedRoute component will handle redirection if not authenticated.
    // We only fetch data if an id is present. Auth check is implicitly handled by ProtectedRoute.
    if (id) {
      setIsFetching(true);
      apiClient.get(`/users/${id}`)
        .then(response => {
          setUser(response.data);
          setFormData({
            email: response.data.email,
            firstName: response.data.firstName,
            lastName: response.data.lastName,
            roles: response.data.roles,
            enabled: response.data.enabled,
          });
          setIsFetching(false);
        })
        .catch(err => {
          setError('Failed to fetch user data.');
          console.error(err);
          setIsFetching(false);
        });
    }
  }, [id]); // authUser and authIsLoading removed as ProtectedRoute handles this layer

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    if (type === 'checkbox') {
     const { checked } = e.target as HTMLInputElement;
     setFormData(prev => ({ ...prev, [name]: checked }));
    } else {
     setFormData(prev => ({ ...prev, [name]: value }));
    }
  };

  const handleRolesChange = (e: React.ChangeEvent<HTMLInputElement>) => {
     setFormData(prev => ({ ...prev, roles: e.target.value.split(',').map(role => role.trim()).filter(role => role) }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      await apiClient.patch(`/users/${id}`, formData);
      router.push('/users');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update user.');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  // ProtectedRoute will handle auth loading state.
  // Data fetching specific loading/error
  if (isFetching) return <MainLayout><p>Loading user data...</p></MainLayout>; // This still needs to be wrapped by ProtectedRoute in return
  if (error && !user) return <MainLayout><p className="text-red-500">{error}</p></MainLayout>;
  if (!user) return <MainLayout><p className="text-red-500">User not found.</p></MainLayout>;


  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-center items-center">
          <Card title={`Edit User: ${user.firstName || ''} ${user.lastName || ''}`} className="w-full max-w-lg mt-8">
            <form onSubmit={handleSubmit} className="space-y-6">
              <Input label="First Name" name="firstName" type="text" value={formData.firstName || ''} onChange={handleChange} />
              <Input label="Last Name" name="lastName" type="text" value={formData.lastName || ''} onChange={handleChange} />
              <Input label="Email" name="email" type="email" value={formData.email || ''} onChange={handleChange} required />
              <Input label="Roles (comma-separated)" name="roles" type="text" value={(formData.roles || []).join(', ')} onChange={handleRolesChange} />
              <div>
                 <label className="flex items-center text-sm font-medium text-gray-700 mb-1">
                   <input type="checkbox" name="enabled" checked={formData.enabled === true} onChange={handleChange} className="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2"/>
                   Enabled
                 </label>
              </div>
              {error && <p className="text-red-500 text-sm">{error}</p>}
              <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={isLoading}>
                {isLoading ? 'Updating...' : 'Update User'}
              </Button>
            </form>
          </Card>
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
