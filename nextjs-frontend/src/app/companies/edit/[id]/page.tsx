import MainLayout from '@/components/layout/MainLayout';

interface EditCompanyPageProps {
  params: { id: string };
}

export default function EditCompanyPage({ params }: EditCompanyPageProps) {
  return <MainLayout><h1 className="text-2xl">Edit Company Page (ID: {params.id}) (Placeholder)</h1></MainLayout>;
}
