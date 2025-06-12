'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

// Inline ProtectedRoute logic
// import { useAuth } from '@/contexts/AuthContext'; // No longer needed

interface CompanyData { name: string; email?: string | null; phone?: string | null; countryId?: string | null; /* add other fields */ }
// interface Country { id: string; name: string; } // Country fetching commented out


export default function EditCompanyPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [company, setCompany] = useState<CompanyData | null>(null);
  const [formData, setFormData] = useState<Partial<CompanyData>>({});
  // const [countries, setCountries] = useState<Country[]>([]); // Country fetching commented out
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);

  useEffect(() => {
    // ProtectedRoute handles auth. Fetch if id is present.
    if (id) {
      setIsFetching(true);
      // apiClient.get('/countries').then(res => setCountries(res.data)).catch(console.error); // Country fetching commented out
      apiClient.get(`/companies/${id}`)
        .then(response => {
          setCompany(response.data);
          setFormData(response.data);
          setIsFetching(false);
        })
        .catch(err => { setError('Failed to fetch company data.'); setIsFetching(false); console.error(err); });
    }
  }, [id]); // authUser and authIsLoading removed

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
    try {
      await apiClient.patch(`/companies/${id}`, formData);
      router.push('/companies');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to update company.');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  // ProtectedRoute will handle auth loading.
  // Data fetching specific loading/error
  if (isFetching) return <MainLayout><p>Loading company data...</p></MainLayout>; // Needs to be wrapped by ProtectedRoute
  if (error && !company) return <MainLayout><p className="text-red-500">{error}</p></MainLayout>;
  if (!company) return <MainLayout><p className="text-red-500">Company not found.</p></MainLayout>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-center items-center">
          <Card title={`Edit Company: ${company.name}`} className="w-full max-w-lg mt-8">
            <form onSubmit={handleSubmit} className="space-y-6">
              <Input label="Company Name" name="name" value={formData.name || ''} onChange={handleChange} required />
              <Input label="Email" name="email" type="email" value={formData.email || ''} onChange={handleChange} />
              <Input label="Phone" name="phone" type="tel" value={formData.phone || ''} onChange={handleChange} />
              {/* <select name="countryId" value={formData.countryId || ''} onChange={handleChange} className="block w-full mt-1 rounded-md border-gray-300 shadow-sm">
                 <option value="">Select Country</option>
                 {countries.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select> */}
              {error && <p className="text-red-500 text-sm">{error}</p>}
              <Button type="submit" variant="primary" className="w-full" isLoading={isLoading} disabled={isLoading}>
                {isLoading ? 'Updating...' : 'Update Company'}
              </Button>
            </form>
          </Card>
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
