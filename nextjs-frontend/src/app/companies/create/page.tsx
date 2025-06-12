'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

// Inline ProtectedRoute logic
// import { useAuth } from '@/contexts/AuthContext'; // No longer needed

// interface Country { id: string; name: string; } // Not used in this simplified version

export default function CreateCompanyPage() {
  const [formData, setFormData] = useState({ name: '', email: '', phone: '', countryId: '' });
  // const [countries, setCountries] = useState<Country[]>([]); // Country fetching commented out
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  // useEffect(() => {
  //    // apiClient.get('/countries').then(res => setCountries(res.data)).catch(console.error); // Assuming /countries endpoint
  // }, []); // Country fetching commented out

  // ProtectedRoute will handle auth check and loading state for auth

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      await apiClient.post('/companies', formData);
      router.push('/companies');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create company.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-center items-center">
          <Card title="Create New Company" className="w-full max-w-lg mt-8">
            <form onSubmit={handleSubmit} className="space-y-6">
              <Input label="Company Name" name="name" value={formData.name} onChange={handleChange} required />
              <Input label="Email" name="email" type="email" value={formData.email} onChange={handleChange} />
              <Input label="Phone" name="phone" type="tel" value={formData.phone} onChange={handleChange} />
              {/* Basic Country select - ideally this would fetch countries from an API */}
              {/* <select name="countryId" value={formData.countryId} onChange={handleChange} className="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                 <option value="">Select Country</option>
                 {countries.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select> */}
              {error && <p className="text-red-500 text-sm">{error}</p>}
              <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={isLoading}>
                {isLoading ? 'Creating...' : 'Create Company'}
              </Button>
            </form>
          </Card>
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
