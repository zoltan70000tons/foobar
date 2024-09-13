import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Toolbar, Paper,Grid} from '@mui/material';

export default function Welcome({}: PageProps) {

  return (
    <GuestLayout
      header={"Admin Panel"}
    >
      <Head title="70000tons of Metal | Admin Panel" />
      <Toolbar />
          <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        
          </Container>
    </GuestLayout>
  );
}
