import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Toolbar, Paper,Grid} from '@mui/material';
import List  from '@/Pages/Permission/partials/List';


export default function ManagePermission({ auth }: PageProps) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={"Teams"}
    >
      <Head title="Teams" />
      <Toolbar />
          <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
            <Grid container spacing={3}>
            <Grid item xs={12}>
                <Paper sx={{ p: 2, display: 'flex', flexDirection: 'column' }}>
                  <List />
                </Paper>
              </Grid>
            </Grid>
          </Container>
    </AuthenticatedLayout>
  );
}
