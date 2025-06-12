'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

interface SubjectFormData {
  identification: string; firstName: string; lastName: string; reportType: string;
  middleName?: string; maidenName?: string; nickname?: string; handles?: string; // Comma-separated
  gender?: string; dateOfBirth?: string; primaryEmail?: string; secondaryEmail?: string;
  primaryMobile?: string; secondaryMobile?: string; educationInstitutes?: string; // Comma-separated
  province?: string; imageFile?: string; status?: string;
  allowTrait?: boolean; rushReport?: boolean;
  companyId?: string; countryId?: string;
  // address fields if handling as flat properties or a sub-object in form data
  // street?: string; suburb?: string; city?: string; postalCode?: string;
}

export default function EditSubjectPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [formData, setFormData] = useState<Partial<SubjectFormData>>({});
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);

  useEffect(() => {
    if (id) {
      apiClient.get(`/subjects/${id}`)
        .then(response => {
          const subject = response.data;
          setFormData({
            ...subject,
            dateOfBirth: subject.dateOfBirth ? new Date(subject.dateOfBirth).toISOString().split('T')[0] : '',
            handles: Array.isArray(subject.handles) ? subject.handles.join(', ') : '',
            educationInstitutes: Array.isArray(subject.educationInstitutes) ? subject.educationInstitutes.join(', ') : '',
          });
          setIsFetching(false);
        })
        .catch(err => { setError('Failed to fetch subject.'); setIsFetching(false); });
    }
  }, [id]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;
    if (type === 'checkbox') {
      const { checked } = e.target as HTMLInputElement;
      setFormData(prev => ({ ...prev, [name]: checked }));
    } else {
      setFormData(prev => ({ ...prev, [name]: value }));
    }
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault(); setError(null); setIsLoading(true);
    try {
      const dataToSubmit = {
        ...formData,
        handles: formData.handles?.split(',').map(s => s.trim()).filter(s => s) || [],
        educationInstitutes: formData.educationInstitutes?.split(',').map(s => s.trim()).filter(s => s) || [],
        dateOfBirth: formData.dateOfBirth ? new Date(formData.dateOfBirth).toISOString() : null,
      };
      await apiClient.patch(`/subjects/${id}`, dataToSubmit);
      router.push(`/subjects/${id}`); // or /subjects
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update subject.');
    } finally {
      setIsLoading(false);
    }
  };

  if (isFetching) return <ProtectedRoute><MainLayout><p>Loading subject...</p></MainLayout></ProtectedRoute>;
  if (error && !formData.identification) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Edit Subject: ${formData.firstName || ''} ${formData.lastName || ''}`} className="max-w-xl mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="ID/Passport" name="identification" value={formData.identification || ''} onChange={handleChange} required />
            <Input label="First Name" name="firstName" value={formData.firstName || ''} onChange={handleChange} required />
            <Input label="Last Name" name="lastName" value={formData.lastName || ''} onChange={handleChange} required />
            <Input label="Middle Name" name="middleName" value={formData.middleName || ''} onChange={handleChange} />
            <Input label="Maiden Name" name="maidenName" value={formData.maidenName || ''} onChange={handleChange} />
            <Input label="Nickname" name="nickname" value={formData.nickname || ''} onChange={handleChange} />
            <Input label="Handles (comma-sep)" name="handles" value={formData.handles || ''} onChange={handleChange} />
            <Input label="Gender" name="gender" value={formData.gender || ''} onChange={handleChange} />
            <Input label="Date of Birth" name="dateOfBirth" type="date" value={formData.dateOfBirth || ''} onChange={handleChange} />
            <Input label="Primary Email" name="primaryEmail" type="email" value={formData.primaryEmail || ''} onChange={handleChange} />
            {/* Added missing fields from prompt for completeness */}
            <Input label="Secondary Email" name="secondaryEmail" type="email" value={formData.secondaryEmail || ''} onChange={handleChange} />
            <Input label="Primary Mobile" name="primaryMobile" type="tel" value={formData.primaryMobile || ''} onChange={handleChange} />
            <Input label="Secondary Mobile" name="secondaryMobile" type="tel" value={formData.secondaryMobile || ''} onChange={handleChange} />
            <Input label="Education Institutes (comma-sep)" name="educationInstitutes" value={formData.educationInstitutes || ''} onChange={handleChange} />
            <Input label="Province" name="province" value={formData.province || ''} onChange={handleChange} />
            <Input label="Image File URL" name="imageFile" value={formData.imageFile || ''} onChange={handleChange} />

            <Input label="Report Type" name="reportType" value={formData.reportType || ''} onChange={handleChange} required />
            <Input label="Status" name="status" value={formData.status || ''} onChange={handleChange} />
            <div className="flex items-center space-x-4">
                <label className="flex items-center"><input type="checkbox" name="allowTrait" checked={formData.allowTrait || false} onChange={handleChange} className="mr-2"/> Allow Trait Analysis</label>
                <label className="flex items-center"><input type="checkbox" name="rushReport" checked={formData.rushReport || false} onChange={handleChange} className="mr-2"/> Rush Report</label>
            </div>
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading}>{isLoading ? 'Updating...' : 'Update Subject'}</Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
