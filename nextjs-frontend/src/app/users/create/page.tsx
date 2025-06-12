'use client';
import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

export default function CreateUserPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  // const [roles, setRoles] = useState<string[]>(['USER']); // Simplified for now
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      // Using the /auth/register endpoint as it handles password hashing
      await apiClient.post('/auth/register', { email, password, firstName, lastName, roles: ['USER'] });
      router.push('/users');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create user.');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-center items-center">
          <Card title="Create New User" className="w-full max-w-lg mt-8">
            <form onSubmit={handleSubmit} className="space-y-6">
              <Input label="First Name" name="firstName" type="text" value={firstName} onChange={(e) => setFirstName(e.target.value)} />
              <Input label="Last Name" name="lastName" type="text" value={lastName} onChange={(e) => setLastName(e.target.value)} />
              <Input label="Email" name="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
              <Input label="Password" name="password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
              {/* Role selection can be added later */}
              {error && <p className="text-red-500 text-sm">{error}</p>}
              <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={isLoading}>
                {isLoading ? 'Creating...' : 'Create User'}
              </Button>
            </form>
          </Card>
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
