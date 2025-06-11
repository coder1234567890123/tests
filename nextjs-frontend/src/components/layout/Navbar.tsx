import React from 'react';
import Link from 'next/link';

const Navbar: React.FC = () => {
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
              <Link href="/dashboard" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                Dashboard
              </Link>
              <Link href="/users" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                Users
              </Link>
              <Link href="/companies" className="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                Companies
              </Link>
              {/* Placeholder for auth links */}
            </div>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
