'use client'; // Required for client-side hooks like useEffect, useState

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';

interface User {
  id: string;
  firstName: string | null;
  lastName: string | null;
  email: string;
  enabled: boolean;
}

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        // TODO: Replace with actual API endpoint from the Express backend
        // For now, using a placeholder. Ensure the backend is running and accessible.
        // Assuming the backend is running on port 3001 (from .env of express-prisma-api)
        const response = await fetch('http://localhost:3001/api/users'); // Ensure backend is running
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        setUsers(data);
      } catch (e: any) {
        setError(e.message);
        console.error("Failed to fetch users:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchUsers();
  }, []);

  if (loading) return <MainLayout><p>Loading users...</p></MainLayout>;
  if (error) return <MainLayout><p className="text-red-500">Error fetching users: {error}</p></MainLayout>;

  return (
    <MainLayout>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Users</h1>
        <Link href="/users/create">
          <Button variant="primary">Create User</Button>
        </Link>
      </div>
      {users.length === 0 ? (
        <p>No users found.</p>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {users.map((user) => (
            <Card key={user.id} title={`${user.firstName || ''} ${user.lastName || ''}`.trim() || 'Unnamed User'}>
              <p className="text-sm text-gray-600 mb-1">ID: {user.id}</p>
              <p className="text-sm text-gray-600 mb-1">Email: {user.email}</p>
              <p className="text-sm text-gray-600 mb-4">Status: {user.enabled ? 'Enabled' : 'Disabled'}</p>
              <div className="flex space-x-2">
                <Link href={`/users/${user.id}`}><Button size="sm" variant="ghost">View</Button></Link>
                <Link href={`/users/edit/${user.id}`}><Button size="sm" variant="secondary">Edit</Button></Link>
                {/* Delete button would require a confirmation and API call */}
                <Button size="sm" variant="danger" onClick={() => alert('Delete functionality not implemented yet for user ' + user.id)}>Delete</Button>
              </div>
            </Card>
          ))}
        </div>
      )}
    </MainLayout>
  );
}
