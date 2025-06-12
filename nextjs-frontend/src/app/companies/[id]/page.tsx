'use client';
import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

// Inline ProtectedRoute logic
// import { useAuth } from '@/contexts/AuthContext'; // No longer needed

interface Company {
  id: string; name: string; email?: string | null; phone?: string | null; city?: string | null;
  registrationNumber?: string | null; vatNumber?: string | null;
  country?: { name: string } | null; createdAt: string; updatedAt: string;
  // Add other fields as needed
}

export default function ViewCompanyPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [company, setCompany] = useState<Company | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoadingPage, setIsLoadingPage] = useState(true);

  useEffect(() => {
    // ProtectedRoute handles auth. Fetch if id is present.
    if (id) {
      setIsLoadingPage(true);
      apiClient.get(`/companies/${id}`)
        .then(response => {
          setCompany(response.data);
          setIsLoadingPage(false);
        })
        .catch(err => {
          setError('Failed to fetch company data.');
          setIsLoadingPage(false);
          console.error(err);
        });
    }
  }, [id]); // authUser and authIsLoading removed

  // ProtectedRoute will handle auth loading.
  // Data fetching specific loading/error
  if (isLoadingPage) return <MainLayout><p>Loading company data...</p></MainLayout>; // Needs to be wrapped by ProtectedRoute
  if (error && !company) return <MainLayout><p className="text-red-500">{error}</p></MainLayout>;
  if (!company) return <MainLayout><p className="text-red-500">Company not found.</p></MainLayout>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Company Details: ${company.name}`} className="max-w-2xl mx-auto mt-8">
          <div className="space-y-3">
            <p><strong>ID:</strong> {company.id}</p>
            <p><strong>Name:</strong> {company.name}</p>
            <p><strong>Email:</strong> {company.email || 'N/A'}</p>
            <p><strong>Phone:</strong> {company.phone || 'N/A'}</p>
            <p><strong>City:</strong> {company.city || 'N/A'}</p>
            <p><strong>Country:</strong> {company.country?.name || 'N/A'}</p>
            <p><strong>Registration #:</strong> {company.registrationNumber || 'N/A'}</p>
            <p><strong>VAT #:</strong> {company.vatNumber || 'N/A'}</p>
            <p><strong>Created At:</strong> {new Date(company.createdAt).toLocaleString()}</p>
            <p><strong>Updated At:</strong> {new Date(company.updatedAt).toLocaleString()}</p>
          </div>
          <div className="mt-6 flex space-x-3">
            <Link href={`/companies/edit/${company.id}`}><Button variant="secondary">Edit Company</Button></Link>
            <Button onClick={() => router.push('/companies')}>Back to List</Button>
          </div>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
