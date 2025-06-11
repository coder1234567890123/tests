'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';

interface Company {
  id: string;
  name: string;
  email: string | null;
  phone: string | null;
  city: string | null;
  country: { name: string } | null; // Assuming country relation is included
}

export default function CompaniesPage() {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchCompanies = async () => {
      try {
        // TODO: Replace with actual API endpoint from the Express backend
        // Assuming the backend is running on port 3001
        const response = await fetch('http://localhost:3001/api/companies'); // Ensure backend is running
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        setCompanies(data);
      } catch (e: any) {
        setError(e.message);
        console.error("Failed to fetch companies:", e);
      } finally {
        setLoading(false);
      }
    };
    fetchCompanies();
  }, []);

  if (loading) return <MainLayout><p>Loading companies...</p></MainLayout>;
  if (error) return <MainLayout><p className="text-red-500">Error fetching companies: {error}</p></MainLayout>;

  return (
    <MainLayout>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Companies</h1>
        <Link href="/companies/create">
          <Button variant="primary">Create Company</Button>
        </Link>
      </div>
      {companies.length === 0 ? (
        <p>No companies found.</p>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {companies.map((company) => (
            <Card key={company.id} title={company.name}>
              <p className="text-sm text-gray-600 mb-1">ID: {company.id}</p>
              <p className="text-sm text-gray-600 mb-1">Email: {company.email || 'N/A'}</p>
              <p className="text-sm text-gray-600 mb-1">City: {company.city || 'N/A'}</p>
              <p className="text-sm text-gray-600 mb-4">Country: {company.country?.name || 'N/A'}</p>
              <div className="flex space-x-2">
                <Link href={`/companies/${company.id}`}><Button size="sm" variant="ghost">View</Button></Link>
                <Link href={`/companies/edit/${company.id}`}><Button size="sm" variant="secondary">Edit</Button></Link>
                <Button size="sm" variant="danger" onClick={() => alert('Delete functionality not implemented yet for company ' + company.id)}>Delete</Button>
              </div>
            </Card>
          ))}
        </div>
      )}
    </MainLayout>
  );
}
