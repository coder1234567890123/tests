'use client';
import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

interface SubjectDetail {
  id: string; firstName: string; lastName: string; identification: string; reportType: string; status: string | null;
  primaryEmail?: string; // Changed from email to primaryEmail to match schema
  primaryMobile?: string; // Changed from phone to primaryMobile
  company?: { name: string }; country?: { name: string };
  createdAt: string; updatedAt: string;
  // Add more fields as available from API
}

export default function ViewSubjectPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [subject, setSubject] = useState<SubjectDetail | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (id) {
      apiClient.get(`/subjects/${id}`)
        .then(response => { setSubject(response.data); setIsLoading(false); })
        .catch(err => { setError('Failed to fetch subject.'); setIsLoading(false); });
    }
  }, [id]);

  if (isLoading) return <ProtectedRoute><MainLayout><p>Loading subject...</p></MainLayout></ProtectedRoute>;
  if (error || !subject) return <ProtectedRoute><MainLayout><p className="text-red-500">{error || 'Subject not found.'}</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Subject: ${subject.firstName} ${subject.lastName}`} className="max-w-2xl mx-auto mt-8">
          <p><strong>ID:</strong> {subject.identification}</p>
          <p><strong>Report Type:</strong> {subject.reportType}</p>
          <p><strong>Current Subject Status:</strong> <span className="font-semibold uppercase">{subject.status?.replace(/_/g, ' ') || 'N/A'}</span></p>
          <p><strong>Email:</strong> {subject.primaryEmail || 'N/A'}</p>
          <p><strong>Phone:</strong> {subject.primaryMobile || 'N/A'}</p>
          <p><strong>Company:</strong> {subject.company?.name || 'N/A'}</p>
          <p><strong>Country:</strong> {subject.country?.name || 'N/A'}</p>
          <p><strong>Created:</strong> {new Date(subject.createdAt).toLocaleString()}</p>
          <p><strong>Updated:</strong> {new Date(subject.updatedAt).toLocaleString()}</p>
          <div className="mt-6 flex space-x-3">
            {/* <Link href={`/subjects/edit/${subject.id}`}><Button variant="secondary">Edit</Button></Link> */}
            <Button onClick={() => router.push('/subjects')}>Back to List</Button>
          </div>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
