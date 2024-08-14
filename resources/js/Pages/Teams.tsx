import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Toolbar, Paper, Grid } from '@mui/material';
import List from '@/Pages/Team/partials/List';
import Invite from '@/Pages/Team/partials/Invite';
import NavigationTeam from '@/Components/NavigationTeam';
import buttonsConfig from './Team/buttonsConfig';
import NoAccessAlert from '@/Components/NoAccessAlert';
import { usePermissions } from '@/Providers/PermissionContext';

export default function Teams({ auth }: PageProps) {
  const { hasPermission } = usePermissions();

  const viewPermission = 'View Users'; 
  const createUserPermission = 'Create User'; 

  return (
    <AuthenticatedLayout user={auth.user} header={"Team"}>
      <Head title="Team" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Paper sx={{ p: 2, display: 'flex', flexDirection: 'column' }}>
              <NavigationTeam buttonsConfig={buttonsConfig} />
              {hasPermission(viewPermission) &&
                <List />
              }
            </Paper>
          </Grid>
          {hasPermission(createUserPermission) && (<Grid item xs={12} md={8} lg={9}>
            <Paper
              sx={{
                p: 2,
                display: 'flex',
                flexDirection: 'column',
                minHeight: 240,
              }}
            >

              <Invite />
            </Paper>
          </Grid>)
          }
          {/* <Grid item xs={12} md={4} lg={3}>
            <Paper
              sx={{
                p: 2,
                display: 'flex',
                flexDirection: 'column',
                // minheight: 240,
              }}
            >

            </Paper>
          </Grid> */}
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
}