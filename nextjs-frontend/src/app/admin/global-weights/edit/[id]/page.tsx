'use client';
import React, { useState, useEffect, FormEvent, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

interface GlobalWeightFormData {
  socialPlatform?: string;
  globalUsageWeighting?: string; // Input as string
  version?: string;
  ordering?: string;
  stdComments?: string; // Comma-separated string
}

export default function EditGlobalWeightPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [formData, setFormData] = useState<GlobalWeightFormData>({});
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);
  const { user } = useAuth();
  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');

  const fetchWeight = useCallback(async () => {
    if (id && isAdmin) { // Only fetch if admin
        setIsFetching(true);
        setError(null);
        try {
            const response = await apiClient.get(`/global-weights/${id}`);
            const weight = response.data;
            setFormData({
                ...weight,
                globalUsageWeighting: weight.globalUsageWeighting?.toString() ?? '0.0',
                version: weight.version?.toString() ?? '1',
                ordering: weight.ordering?.toString() ?? '0',
                stdComments: Array.isArray(weight.stdComments) ? weight.stdComments.join(', ') : '',
            });
        } catch (err: any) {
            setError(err.response?.data?.error || 'Failed to fetch global weight.');
        } finally {
            setIsFetching(false);
        }
    } else if (user && !isAdmin) {
        setError("Access Denied.");
        setIsFetching(false);
    } else if (!user && !isFetching) { // Auth context loaded, no user
        setIsFetching(false);
    }
  }, [id, isAdmin, user, isFetching]); // Added isFetching to deps to prevent potential loop if auth is slow

  useEffect(() => {
    fetchWeight();
  }, [fetchWeight]);

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
        globalUsageWeighting: formData.globalUsageWeighting ? parseFloat(formData.globalUsageWeighting) : undefined,
        version: formData.version ? parseInt(formData.version) : undefined,
        ordering: formData.ordering ? parseInt(formData.ordering) : undefined,
        stdComments: formData.stdComments ? formData.stdComments.split(',').map(s => s.trim()).filter(s => s) : [],
      };
      await apiClient.patch(`/global-weights/${id}`, dataToSubmit);
      router.push('/admin/global-weights');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update global weight.');
    } finally {
      setIsLoading(false);
    }
  };

  if (!isAdmin && user) return <ProtectedRoute><MainLayout><p className="text-red-500">Access Denied.</p></MainLayout></ProtectedRoute>;
  if (isFetching) return <ProtectedRoute><MainLayout><p>Loading data...</p></MainLayout></ProtectedRoute>;
  if (error && !formData.socialPlatform) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;


  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Edit Global Weight: ${formData.socialPlatform || ''}`} className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Social Platform" name="socialPlatform" value={formData.socialPlatform || ''} onChange={handleChange} required />
            <Input label="Global Usage Weighting" name="globalUsageWeighting" type="number" step="0.01" value={formData.globalUsageWeighting || ''} onChange={handleChange} required />
            <Input label="Version" name="version" type="number" value={formData.version || ''} onChange={handleChange} />
            <Input label="Ordering" name="ordering" type="number" value={formData.ordering || ''} onChange={handleChange} />
            <Input label="Standard Comments (comma-separated)" name="stdComments" value={formData.stdComments || ''} onChange={handleChange} />

            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={!isAdmin || isLoading}>
              {isLoading ? 'Updating...' : 'Update Weight'}
            </Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
