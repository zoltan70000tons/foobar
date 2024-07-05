import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container } from '@mui/material'

export default function Edit({ auth, mustVerifyEmail, status }: PageProps<{ mustVerifyEmail: boolean, status?: string }>) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2>Profile</h2>}
    >
      <Head title="Profile" />
      <Container>
        <UpdateProfileInformationForm
          mustVerifyEmail={mustVerifyEmail}
          status={status}
        />
        <UpdatePasswordForm />
        <DeleteUserForm />
      </Container>
    </AuthenticatedLayout>
  );
}
