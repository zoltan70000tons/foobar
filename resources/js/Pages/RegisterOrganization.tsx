import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container } from '@mui/material';
import Register from './Organization/Partials/Register';

export default function RegisterOrganization({ auth }: PageProps) {
  return (
     <><Head title="Register Organization" />

     <Container>
     <Register />
     </Container></>
     );
}
