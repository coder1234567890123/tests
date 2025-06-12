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

interface SystemConfigFormData {
  opt?: string; // Option Key - should be read-only on edit
  val?: string; // Option Value
  systemType?: string; // Input as string
}

export default function EditSystemConfigPage() {
  const params = useParams();
  const key = params.key as string; // This is the 'opt' field
  const router = useRouter();
  const [formData, setFormData] = useState<SystemConfigFormData>({});
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);
  const { user } = useAuth();
  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');

  const fetchConfig = useCallback(async () => {
    if (key && isAdmin) {
      setIsFetching(true);
      setError(null);
      try {
        const response = await apiClient.get(`/system-configs/${key}`);
        const config = response.data;
        setFormData({
          ...config,
          systemType: config.systemType?.toString() ?? '',
        });
      } catch (err: any) {
        setError(err.response?.data?.error || 'Failed to fetch system config.');
      } finally {
        setIsFetching(false);
      }
    } else if (user && !isAdmin) {
        setError("Access Denied.");
        setIsFetching(false);
    } else if (!user && !isFetching) {
        setIsFetching(false);
    }
  }, [key, isAdmin, user, isFetching]);

  useEffect(() => {
    fetchConfig();
  }, [fetchConfig]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      const dataToSubmit = {
        val: formData.val,
        systemType: formData.systemType ? parseInt(formData.systemType) : null,
      };
      await apiClient.patch(`/system-configs/${key}`, dataToSubmit);
      router.push('/admin/system-configs');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update system config.');
    } finally {
      setIsLoading(false);
    }
  };

  if (!isAdmin && user) return <ProtectedRoute><MainLayout><p className="text-red-500">Access Denied.</p></MainLayout></ProtectedRoute>;
  if (isFetching) return <ProtectedRoute><MainLayout><p>Loading data...</p></MainLayout></ProtectedRoute>;
  if (error && !formData.opt) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Edit System Config: ${formData.opt || ''}`} className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Option Key (opt)" name="opt" value={formData.opt || ''} onChange={handleChange} required readOnly disabled />
            <div>
              <label htmlFor="val" className="block text-sm font-medium text-gray-700 mb-1">Option Value (val)</label>
              <textarea
                id="val"
                name="val"
                value={formData.val || ''}
                onChange={handleChange}
                rows={6}
                className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              />
            </div>
            <Input label="System Type (optional number)" name="systemType" type="number" value={formData.systemType || ''} onChange={handleChange} />

            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={!isAdmin || isLoading}>
              {isLoading ? 'Updating...' : 'Update Config'}
            </Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
