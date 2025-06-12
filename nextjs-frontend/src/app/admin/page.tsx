import MainLayout from '@/components/layout/MainLayout';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import Link from 'next/link';
import Card from '@/components/ui/Card';

export default function AdminDashboardPage() {
  return (
    <ProtectedRoute> {/* Ensure admin role check is inside if needed, or this HOC handles it */}
      <MainLayout>
        <h1 className="text-3xl font-bold mb-6">Admin Dashboard</h1>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card title="Manage Settings">
            <ul className="space-y-2">
              <li>
                <Link href="/admin/global-weights" className="text-blue-600 hover:underline">
                  Global Weights
                </Link>
              </li>
              <li>
                <Link href="/admin/system-configs" className="text-blue-600 hover:underline">
                  System Configurations
                </Link>
              </li>
              {/* Add more admin links here */}
            </ul>
          </Card>
          {/* Add other admin sections/cards here */}
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
