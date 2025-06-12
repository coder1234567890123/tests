'use client';
import React, { useState, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

export default function CreateReportPage() {
  const [formData, setFormData] = useState({ sequence: '', subjectId: '', requestType: 'normal' });
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault(); setError(null); setIsLoading(true);
    try {
      await apiClient.post('/reports', formData);
      router.push('/reports');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create report.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title="Create New Report" className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Sequence #" name="sequence" value={formData.sequence} onChange={handleChange} required />
            <Input label="Subject ID" name="subjectId" value={formData.subjectId} onChange={handleChange} required placeholder="Enter existing Subject ID"/>
            <div>
              <label htmlFor="requestType" className="block text-sm font-medium text-gray-700 mb-1">Request Type</label>
              <select id="requestType" name="requestType" value={formData.requestType} onChange={handleChange} className="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="normal">Normal</option>
                <option value="rush">Rush</option>
                <option value="test">Test</option>
              </select>
            </div>
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading}>{isLoading ? 'Creating...' : 'Create Report'}</Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
