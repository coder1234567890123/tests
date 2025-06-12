'use client';
import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import Modal from '@/components/ui/Modal';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';


interface GlobalWeight {
  id: string;
  socialPlatform: string;
  globalUsageWeighting: number;
  version?: number | null;
  ordering?: number | null;
  stdComments?: any | null; // Prisma Json can be any, handle array display
}

export default function GlobalWeightsPage() {
  const [weights, setWeights] = useState<GlobalWeight[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [itemToDelete, setItemToDelete] = useState<GlobalWeight | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const router = useRouter();
  const { user } = useAuth();

  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');


  const fetchWeights = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await apiClient.get('/global-weights');
      setWeights(response.data);
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to fetch global weights');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (isAdmin) { // Check if isAdmin is resolved before fetching
      fetchWeights();
    } else if (user && !isAdmin) {
      setError("Access Denied. Admin role required.");
      setLoading(false);
    } else if (!user && !loading) { // If auth context is done loading and there's no user
      // This case should ideally be handled by ProtectedRoute redirecting to login
      // If ProtectedRoute is already active, this explicit check might be redundant
      // or could be a fallback if ProtectedRoute hasn't redirected yet.
      setLoading(false); // Ensure loading stops
    }
    // If user is null and auth context is still loading, ProtectedRoute handles it.
  }, [isAdmin, user, fetchWeights, loading]);


  const handleDelete = async () => {
    if (!itemToDelete) return;
    setIsDeleting(true);
    setError(null);
    try {
      await apiClient.delete(`/global-weights/${itemToDelete.id}`);
      setWeights(prev => prev.filter(w => w.id !== itemToDelete.id));
      setShowDeleteModal(false);
      setItemToDelete(null);
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to delete item.');
    } finally {
      setIsDeleting(false);
    }
  };

  // This page content will only be reached if ProtectedRoute allows it.
  // Further checks for isAdmin can refine UI elements within the page.
  if (loading) return <MainLayout><div className="p-4"><p>Loading global weights...</p></div></MainLayout>;
  // Error related to fetching can be displayed. Access denied is a specific error.
  if (error && !isAdmin && user) return <MainLayout><div className="p-4"><p className="text-red-500">{error}</p></div></MainLayout>;
  if (error && weights.length === 0) return <MainLayout><div className="p-4"><p className="text-red-500">{error}</p></div></MainLayout>;


  return (
    <ProtectedRoute> {/* Ensures only authenticated users reach here, further checks for admin role for content */}
      <MainLayout>
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">Global Weights</h1>
          {isAdmin && <Link href="/admin/global-weights/create"><Button variant="primary">Add New Weight</Button></Link>}
        </div>

        {!isAdmin && user && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">Access Denied. Admin role required to view content.</p>}
        {error && isAdmin && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">{error}</p>}

        {isAdmin && !loading && !error && weights.length === 0 && <p>No global weights found.</p>}

        {isAdmin && (
          <div className="space-y-4">
            {weights.map((w) => (
              <Card key={w.id} title={w.socialPlatform}>
                <p>Weighting: {w.globalUsageWeighting}</p>
                <p>Ordering: {w.ordering ?? 'N/A'}</p>
                <p>Version: {w.version ?? 'N/A'}</p>
                <p>Std Comments: {Array.isArray(w.stdComments) && w.stdComments.length > 0 ? w.stdComments.join('; ') : 'N/A'}</p>
                <div className="mt-4 flex space-x-2">
                  <Link href={`/admin/global-weights/edit/${w.id}`}><Button size="sm" variant="secondary">Edit</Button></Link>
                  <Button size="sm" variant="danger" onClick={() => { setItemToDelete(w); setShowDeleteModal(true); setError(null); }}>Delete</Button>
                </div>
              </Card>
            ))}
          </div>
        )}
        <Modal isOpen={showDeleteModal} onClose={() => {setShowDeleteModal(false); setError(null);}} title="Confirm Delete">
          <p>Are you sure you want to delete the weight for: <strong>{itemToDelete?.socialPlatform}</strong>?</p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
            <Button variant="danger" onClick={handleDelete} isLoading={isDeleting}>Delete</Button>
          </div>
        </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
