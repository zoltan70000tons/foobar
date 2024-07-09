import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container } from '@mui/material';
import ListOrganizations from './Organization/Partials/Register';
import RegisterOrganization from './Organization/Partials/Register';

export default function Dashboard({ auth }: PageProps) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2>Dashboard</h2>}
    >
      <Head title="Dashboard" />

      <Container>
        <p>You're logged in!</p>
      <RegisterOrganization mustVerifyEmail={true} />
      </Container>
      
    </AuthenticatedLayout>
  );
}
