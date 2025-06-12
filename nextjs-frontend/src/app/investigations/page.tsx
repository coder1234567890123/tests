import MainLayout from '@/components/layout/MainLayout';
import Link from 'next/link';
import ProtectedRoute from '@/components/auth/ProtectedRoute';

export default function InvestigationsBasePage() {
  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="p-4">
          <h1 className="text-2xl font-bold mb-4">Investigations</h1>
          <p>This is a general placeholder page for investigations.</p>
          <p>Typically, you would access an investigation via a specific <Link href="/reports" className="text-blue-600 hover:underline">report</Link>.</p>
          {/* If you want to list all reports that have ongoing investigations, that logic could go here */}
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
