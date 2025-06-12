'use client';
import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
// import { useAuth } from '@/contexts/AuthContext'; // Optional: if auto-login after register

export default function RegisterPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();
  // const { login } = useAuth(); // Optional: if auto-login

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      await apiClient.post('/auth/register', { email, password, firstName, lastName });
      // Optionally, log the user in directly if the API returns a token/user
      // const loginResponse = await apiClient.post('/auth/login', { email, password });
      // login(loginResponse.data.token, loginResponse.data.user);
      // router.push('/users');
      alert('Registration successful! Please login.'); // Simple feedback
      router.push('/login');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Registration failed. Please try again.');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <MainLayout>
      <div className="flex justify-center items-center min-h-[calc(100vh-10rem)]">
        <Card title="Register" className="w-full max-w-md">
          <form onSubmit={handleSubmit} className="space-y-6">
            <Input
              label="First Name"
              name="firstName"
              type="text"
              value={firstName}
              onChange={(e) => setFirstName(e.target.value)}
            />
            <Input
              label="Last Name"
              name="lastName"
              type="text"
              value={lastName}
              onChange={(e) => setLastName(e.target.value)}
            />
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
              {isLoading ? 'Registering...' : 'Register'}
            </Button>
          </form>
        </Card>
      </div>
    </MainLayout>
  );
}
