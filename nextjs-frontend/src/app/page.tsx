'use client';

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import MainLayout from '@/components/layout/MainLayout'; // Optional for a redirect page, but good for consistency if loading shows

export default function HomePage() {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading) {
      if (user) {
        router.replace('/dashboard'); // User is logged in, go to dashboard
      } else {
        router.replace('/login');    // User is not logged in, go to login
      }
    }
  }, [user, isLoading, router]);

  // Display a loading message or a minimal layout while redirecting
  return (
    <MainLayout>
      <div className="flex justify-center items-center h-screen">
        <p className="text-lg">Loading application...</p>
        {/* You could add a spinner here */}
      </div>
    </MainLayout>
  );
}
