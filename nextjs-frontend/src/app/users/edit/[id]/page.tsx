import MainLayout from '@/components/layout/MainLayout';

interface EditUserPageProps {
  params: { id: string };
}

export default function EditUserPage({ params }: EditUserPageProps) {
  return <MainLayout><h1 className="text-2xl">Edit User Page (ID: {params.id}) (Placeholder)</h1></MainLayout>;
}
