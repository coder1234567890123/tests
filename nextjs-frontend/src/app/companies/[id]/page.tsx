import MainLayout from '@/components/layout/MainLayout';

interface CompanyDetailPageProps {
  params: { id: string };
}

export default function CompanyDetailPage({ params }: CompanyDetailPageProps) {
  return <MainLayout><h1 className="text-2xl">Company Detail Page (ID: {params.id}) (Placeholder)</h1></MainLayout>;
}
