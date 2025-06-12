'use client'; // Required for useAuth hook
import React, { useState, useEffect } from 'react'; // Added useState, useEffect
import Link from 'next/link';
import apiClient from '@/lib/api'; // Added apiClient import
import { useAuth } from '@/contexts/AuthContext';
import Button from '@/components/ui/Button'; // Assuming you have a Button component

const Navbar: React.FC = () => {
  const { user, logout, isLoading } = useAuth();
  const isAdmin = user?.roles?.includes('ROLE_SUPER_ADMIN');
  const [localUnreadCount, setLocalUnreadCount] = useState(0);

  useEffect(() => {
       if (user && !isLoading) {
           apiClient.get('/messages/my/unread-count')
               .then(res => setLocalUnreadCount(res.data.unreadCount))
               .catch(console.error);
       }
  }, [user, isLoading]);

  return (
    <nav className="bg-gray-800 text-white shadow-lg">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          <div className="flex items-center">
            <Link href="/" className="font-bold text-xl">
              AppName
            </Link>
          </div>
          <div className="hidden md:block">
            <div className="ml-10 flex items-baseline space-x-4">
              {user && (
                <Link href="/dashboard" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                  Dashboard
                </Link>
              )}
              {user && (
                <Link href="/profile" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                  My Profile
                </Link>
              )}
              {user && (
                <Link href="/users" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                  Users
                </Link>
              )}
              {user && (
                <Link href="/companies" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                  Companies
                </Link>
              )}
                 {user && (
                  <Link href="/subjects" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                    Subjects
                  </Link>
                 )}
                 {user && (
                  <Link href="/reports" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                    Reports
                  </Link>
                 )}
                 {isAdmin && (
                  <Link href="/admin" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 bg-yellow-500 text-gray-900">
                    Admin
                  </Link>
                 )}
                 {user && (
                   <Link href="/messages" className="relative px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                     Messages
                     {localUnreadCount > 0 && (
                       <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                         {localUnreadCount}
                       </span>
                     )}
                   </Link>
                 )}
            </div>
          </div>
          <div className="hidden md:block">
             <div className="ml-4 flex items-center md:ml-6">
                 {!isLoading && !user && (
                     <>
                         <Link href="/login" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                             Login
                         </Link>
                         <Link href="/register" className="ml-4">
                             <Button variant="secondary" size="sm">Register</Button>
                         </Link>
                     </>
                 )}
                 {!isLoading && user && (
                     <>
                         <span className="mr-4 text-sm">Welcome, {user.firstName || user.email}</span>
                         <Button onClick={logout} variant="ghost" size="sm">Logout</Button>
                     </>
                 )}
                 {isLoading && <p className="text-sm">Loading...</p>}
             </div>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
