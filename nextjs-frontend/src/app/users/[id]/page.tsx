'use client';
import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

// Inline ProtectedRoute logic
// import { useAuth } from '@/contexts/AuthContext'; // No longer needed directly here

interface User {
  id: string; email: string; firstName: string | null; lastName: string | null; roles: string[]; enabled: boolean; createdAt: string; updatedAt: string;
}

export default function ViewUserPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoadingPage, setIsLoadingPage] = useState(true);

  useEffect(() => {
    // ProtectedRoute handles auth. Fetch data if id is present.
    if (id) {
      setIsLoadingPage(true);
      apiClient.get(`/users/${id}`)
        .then(response => {
          setUser(response.data);
          setIsLoadingPage(false);
        })
        .catch(err => {
          setError('Failed to fetch user data.');
          console.error(err);
          setIsLoadingPage(false);
        });
    }
  }, [id]);

  // ProtectedRoute will handle auth loading.
  // Data fetching specific loading/error
  if (isLoadingPage) return <MainLayout><p>Loading user data...</p></MainLayout>; // This needs to be wrapped by ProtectedRoute
  if (error && !user) return <MainLayout><p className="text-red-500">{error}</p></MainLayout>;
  if (!user) return <MainLayout><p className="text-red-500">User not found.</p></MainLayout>;


  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`User Details: ${user.firstName || ''} ${user.lastName || ''}`} className="max-w-2xl mx-auto mt-8">
          <div className="space-y-3">
            <p><strong>ID:</strong> {user.id}</p>
            <p><strong>Email:</strong> {user.email}</p>
            <p><strong>First Name:</strong> {user.firstName || 'N/A'}</p>
            <p><strong>Last Name:</strong> {user.lastName || 'N/A'}</p>
            <p><strong>Roles:</strong> {user.roles.join(', ')}</p>
            <p><strong>Status:</strong> {user.enabled ? 'Enabled' : 'Disabled'}</p>
            <p><strong>Created At:</strong> {new Date(user.createdAt).toLocaleString()}</p>
            <p><strong>Updated At:</strong> {new Date(user.updatedAt).toLocaleString()}</p>
          </div>
          <div className="mt-6 flex space-x-3">
            <Link href={`/users/edit/${user.id}`}><Button variant="secondary">Edit User</Button></Link>
            <Button onClick={() => router.push('/users')}>Back to List</Button>
          </div>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
