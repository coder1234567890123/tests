'use client';
import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import Modal from '@/components/ui/Modal';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

interface SystemConfig {
  id: string;
  opt: string;
  val: string;
  systemType?: number | null;
}

export default function SystemConfigsPage() {
  const [configs, setConfigs] = useState<SystemConfig[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [itemToDelete, setItemToDelete] = useState<SystemConfig | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const { user } = useAuth();
  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');

  const fetchConfigs = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await apiClient.get('/system-configs');
      setConfigs(response.data);
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to fetch system configs');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (isAdmin) {
      fetchConfigs();
    } else if (user && !isAdmin) {
      setError("Access Denied. Admin role required.");
      setLoading(false);
    } else if (!user && !loading) {
      setLoading(false);
    }
  }, [isAdmin, user, fetchConfigs, loading]);

  const handleDelete = async () => {
    if (!itemToDelete) return;
    setIsDeleting(true);
    setError(null);
    try {
      await apiClient.delete(`/system-configs/${itemToDelete.opt}`); // Delete by 'opt' key
      setConfigs(prev => prev.filter(c => c.opt !== itemToDelete.opt));
      setShowDeleteModal(false);
      setItemToDelete(null);
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to delete item.');
    } finally {
      setIsDeleting(false);
    }
  };

  if (!isAdmin && user) return <ProtectedRoute><MainLayout><div className="p-4"><p className="text-red-500">Access Denied. Admin role required.</p></div></MainLayout></ProtectedRoute>;
  if (loading) return <ProtectedRoute><MainLayout><div className="p-4"><p>Loading system configs...</p></div></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">System Configurations</h1>
          {isAdmin && <Link href="/admin/system-configs/create"><Button variant="primary">Add New Config</Button></Link>}
        </div>

        {!isAdmin && user && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">Access Denied. Admin role required to view content.</p>}
        {error && isAdmin && <p className="text-red-500 bg-red-100 p-3 my-4 rounded">{error}</p>}

        {isAdmin && !loading && !error && configs.length === 0 && <p>No system configurations found.</p>}

        {isAdmin && (
          <div className="space-y-4">
            {configs.map((conf) => (
              <Card key={conf.opt} title={conf.opt}>
                <pre className="bg-gray-100 p-2 rounded overflow-x-auto text-sm whitespace-pre-wrap break-all">{conf.val}</pre>
                <p className="text-xs text-gray-500 mt-1">Type: {conf.systemType ?? 'N/A'}</p>
                <p className="text-xs text-gray-500 mt-1">ID: {conf.id}</p>
                <div className="mt-4 flex space-x-2">
                  <Link href={`/admin/system-configs/edit/${conf.opt}`}><Button size="sm" variant="secondary">Edit</Button></Link>
                  <Button size="sm" variant="danger" onClick={() => { setItemToDelete(conf); setShowDeleteModal(true); setError(null); }}>Delete</Button>
                </div>
              </Card>
            ))}
          </div>
        )}
        <Modal isOpen={showDeleteModal} onClose={() => {setShowDeleteModal(false); setError(null);}} title="Confirm Delete">
          <p>Are you sure you want to delete config: <strong>{itemToDelete?.opt}</strong>?</p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="ghost" onClick={() => {setShowDeleteModal(false); setError(null);}}>Cancel</Button>
            <Button variant="danger" onClick={handleDelete} isLoading={isDeleting}>Delete</Button>
          </div>
        </Modal>
      </MainLayout>
    </ProtectedRoute>
  );
}
