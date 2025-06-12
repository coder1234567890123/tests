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

interface SystemConfigFormData {
  opt: string; // Option Key
  val: string; // Option Value
  systemType?: string; // Input as string, parse on submit
}

export default function CreateSystemConfigPage() {
  const [formData, setFormData] = useState<SystemConfigFormData>({
    opt: '',
    val: '',
    systemType: ''
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
        systemType: formData.systemType ? parseInt(formData.systemType) : null, // Prisma expects Int?
      };
      await apiClient.post('/system-configs', dataToSubmit);
      router.push('/admin/system-configs');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to create system config.');
    } finally {
      setIsLoading(false);
    }
  };

  if (!isAdmin && user) return <ProtectedRoute><MainLayout><p className="text-red-500">Access Denied.</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title="Create New System Configuration" className="max-w-lg mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Option Key (opt)" name="opt" value={formData.opt} onChange={handleChange} required />
            <div>
              <label htmlFor="val" className="block text-sm font-medium text-gray-700 mb-1">Option Value (val)</label>
              <textarea
                id="val"
                name="val"
                value={formData.val}
                onChange={handleChange}
                rows={4}
                className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              />
            </div>
            <Input label="System Type (optional number)" name="systemType" type="number" value={formData.systemType} onChange={handleChange} />

            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={!isAdmin || isLoading}>
              {isLoading ? 'Creating...' : 'Create Config'}
            </Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
