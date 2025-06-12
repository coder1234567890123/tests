'use client';
import React, { useState, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

export default function CreateSubjectPage() {
  const [formData, setFormData] = useState({ identification: '', firstName: '', lastName: '', reportType: 'standard', email: '', phone: '' });
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault(); setError(null); setIsLoading(true);
    try {
      await apiClient.post('/subjects', formData);
      router.push('/subjects');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create subject.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title="Create New Subject" className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Identification (ID/Passport)" name="identification" value={formData.identification} onChange={handleChange} required />
            <Input label="First Name" name="firstName" value={formData.firstName} onChange={handleChange} required />
            <Input label="Last Name" name="lastName" value={formData.lastName} onChange={handleChange} required />
            <Input label="Primary Email" name="email" type="email" value={formData.email} onChange={handleChange} />
            <Input label="Primary Phone" name="phone" type="tel" value={formData.phone} onChange={handleChange} />
            <div>
              <label htmlFor="reportType" className="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
              <select id="reportType" name="reportType" value={formData.reportType} onChange={handleChange} className="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="basic">Basic</option>
                <option value="standard">Standard</option>
                <option value="full">Full</option>
                <option value="high_profile">High Profile</option>
              </select>
            </div>
            {/* Add companyId, countryId selects later if needed */}
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading}>{isLoading ? 'Creating...' : 'Create Subject'}</Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
