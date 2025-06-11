import MainLayout from '@/components/layout/MainLayout';

interface UserDetailPageProps {
  params: { id: string };
}

export default function UserDetailPage({ params }: UserDetailPageProps) {
  return <MainLayout><h1 className="text-2xl">User Detail Page (ID: {params.id}) (Placeholder)</h1></MainLayout>;
}
