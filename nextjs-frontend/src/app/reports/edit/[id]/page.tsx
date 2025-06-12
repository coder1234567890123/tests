'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

interface ReportFormData {
  sequence: string; status?: string; requestType?: string; riskScore?: number | string;
  dueDate?: string; completedDate?: string; optionValue?: number | string; riskComment?: string;
  assignedToId?: string; // For simplicity, user types ID. Ideally a dropdown.
  subjectId?: string; // Keep subjectId, it's part of the report
  companyId?: string; // Keep companyId
}

export default function EditReportPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [formData, setFormData] = useState<Partial<ReportFormData>>({});
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);

  useEffect(() => {
    if (id) {
      apiClient.get(`/reports/${id}`)
        .then(response => {
          const report = response.data;
          setFormData({
            ...report, // Spread all report fields
            dueDate: report.dueDate ? new Date(report.dueDate).toISOString().split('T')[0] : '',
            completedDate: report.completedDate ? new Date(report.completedDate).toISOString().split('T')[0] : '',
            riskScore: report.riskScore?.toString() ?? '',
            optionValue: report.optionValue?.toString() ?? '0',
            // subjectId and companyId will be pre-filled if they exist on report object
          });
          setIsFetching(false);
        })
        .catch(err => { setError('Failed to fetch report.'); setIsFetching(false); });
    }
  }, [id]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault(); setError(null); setIsLoading(true);
    try {
      const dataToSubmit: any = { // Use 'any' for dataToSubmit to handle mixed types before Prisma parsing
        ...formData,
      };
      if (formData.riskScore) dataToSubmit.riskScore = parseFloat(formData.riskScore as string);
      if (formData.optionValue) dataToSubmit.optionValue = parseInt(formData.optionValue as string);
      if (formData.dueDate) dataToSubmit.dueDate = new Date(formData.dueDate).toISOString();
      if (formData.completedDate) dataToSubmit.completedDate = new Date(formData.completedDate).toISOString();

      // Remove IDs that shouldn't be directly updated if they are relational and handled by Prisma relations
      // delete dataToSubmit.subjectId; // Or ensure backend handles this appropriately if it's not meant to be changed here
      // delete dataToSubmit.companyId;

      await apiClient.patch(`/reports/${id}`, dataToSubmit);
      router.push(`/reports/${id}`); // or /reports
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update report.');
    } finally {
      setIsLoading(false);
    }
  };

  if (isFetching) return <ProtectedRoute><MainLayout><p>Loading report...</p></MainLayout></ProtectedRoute>;
  if (error && !formData.sequence) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Edit Report: ${formData.sequence || ''}`} className="max-w-xl mx-auto mt-8">
          <form onSubmit={handleSubmit} className="space-y-4">
            <Input label="Sequence" name="sequence" value={formData.sequence || ''} onChange={handleChange} required />
            <Input label="Subject ID (cannot change)" name="subjectId" value={formData.subjectId || ''} onChange={handleChange} disabled readOnly />
            <Input label="Company ID (cannot change)" name="companyId" value={formData.companyId || ''} onChange={handleChange} disabled readOnly />
            <Input label="Status" name="status" value={formData.status || ''} onChange={handleChange} />
            {/* <Input label="Request Type" name="requestType" value={formData.requestType || ''} onChange={handleChange} /> */}
            <div>
              <label htmlFor="requestType" className="block text-sm font-medium text-gray-700 mb-1">Request Type</label>
              <select id="requestType" name="requestType" value={formData.requestType || 'normal'} onChange={handleChange} className="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="normal">Normal</option>
                <option value="rush">Rush</option>
                <option value="test">Test</option>
              </select>
            </div>
            <Input label="Risk Score" name="riskScore" type="number" step="0.01" value={formData.riskScore || ''} onChange={handleChange} />
            <Input label="Due Date" name="dueDate" type="date" value={formData.dueDate || ''} onChange={handleChange} />
            <Input label="Completed Date" name="completedDate" type="date" value={formData.completedDate || ''} onChange={handleChange} />
            <Input label="Assigned User ID" name="assignedToId" value={formData.assignedToId || ''} onChange={handleChange} placeholder="Enter User ID"/>
            <label htmlFor="riskComment" className="block text-sm font-medium text-gray-700">Risk Comment</label>
            <textarea name="riskComment" value={formData.riskComment || ''} onChange={handleChange} placeholder="Risk Comment..." className="block w-full mt-1 rounded-md border-gray-300 shadow-sm p-2"></textarea>
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <Button type="submit" variant="primary" className="w-full" isLoading={isLoading}>{isLoading ? 'Updating...' : 'Update Report'}</Button>
          </form>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
