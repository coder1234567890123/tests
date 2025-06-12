'use client';
import React, { useState } from 'react';
import { useRouter } from 'next/navigation'; // For App Router
import { useAuth } from '@/contexts/AuthContext';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      const response = await apiClient.post('/auth/login', { email, password });
      const { token, user } = response.data;
      login(token, user);
      router.push('/users'); // Redirect to users page or dashboard
    } catch (err: any) {
      setError(err.response?.data?.error || 'Login failed. Please check your credentials.');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <MainLayout>
      <div className="flex justify-center items-center min-h-[calc(100vh-10rem)]">
        <Card title="Login" className="w-full max-w-md">
          <form onSubmit={handleSubmit} className="space-y-6">
            <Input
              label="Email"
              name="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            <Input
              label="Password"
              name="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={isLoading}>
              {isLoading ? 'Logging in...' : 'Login'}
            </Button>
          </form>
        </Card>
      </div>
    </MainLayout>
  );
}
