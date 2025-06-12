'use client';
import React, { useState, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

interface GlobalWeightFormData {
  socialPlatform: string;
  globalUsageWeighting: string; // Input as string, parse on submit
  version?: string;
  ordering?: string;
  stdComments?: string; // Comma-separated string
}

export default function CreateGlobalWeightPage() {
  const [formData, setFormData] = useState<GlobalWeightFormData>({
    socialPlatform: '',
    globalUsageWeighting: '0.0',
    version: '1',
    ordering: '0',
    stdComments: ''
  });
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();
  const { user } = useAuth();
  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      const dataToSubmit = {
        ...formData,
        globalUsageWeighting: parseFloat(formData.globalUsageWeighting),
        version: formData.version ? parseInt(formData.version) : undefined,
        ordering: formData.ordering ? parseInt(formData.ordering) : undefined,
        stdComments: formData.stdComments ? formData.stdComments.split(',').map(s => s.trim()).filter(s => s) : [],
      };
      await apiClient.post('/global-weights', dataToSubmit);
      router.push('/admin/global-weights');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create global weight.');
    } finally {
      setIsLoading(false);
    }
  };

  if (!isAdmin && user) return <ProtectedRoute><MainLayout><p className="text-red-500">Access Denied.</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title="Create New Global Weight" className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Social Platform" name="socialPlatform" value={formData.socialPlatform} onChange={handleChange} required />
            <Input label="Global Usage Weighting" name="globalUsageWeighting" type="number" step="0.01" value={formData.globalUsageWeighting} onChange={handleChange} required />
            <Input label="Version" name="version" type="number" value={formData.version} onChange={handleChange} />
            <Input label="Ordering" name="ordering" type="number" value={formData.ordering} onChange={handleChange} />
            <Input label="Standard Comments (comma-separated)" name="stdComments" value={formData.stdComments} onChange={handleChange} />

            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={!isAdmin || isLoading}>
              {isLoading ? 'Creating...' : 'Create Weight'}
            </Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
