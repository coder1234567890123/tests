'use client';
import React, { useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import MainLayout from '@/components/layout/MainLayout'; // Import MainLayout for consistent loading UI

const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }
  }, [user, isLoading, router]);

  if (isLoading || !user) {
    // Using MainLayout to provide consistent UI during loading/redirect
    return (
      <MainLayout>
        <div className="flex justify-center items-center h-screen">
          <p>Loading or redirecting to login...</p>
        </div>
      </MainLayout>
    );
  }

  return <>{children}</>;
};

export default ProtectedRoute;
