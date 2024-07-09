import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container } from '@mui/material';
import Join from './Organization/Partials/Join';

export default function JoinOrganization({ auth }: PageProps) {
  return (
     <><Head title="Join Organization" />

     <Container>
     <Join />
     </Container></>
     );
}
