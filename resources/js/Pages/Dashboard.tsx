import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Toolbar, Paper,Grid, Box, Typography, Button} from '@mui/material';

export default function Dashboard({ auth }: PageProps) {
  const { user } = auth;
  console.log(auth);

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={"Dashboard"}
    >
      <Head title="Dashboard" />
      <Toolbar />
          <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
            <Grid container spacing={3}>
              {/* Chart */}
              <Grid item xs={12} md={8} lg={9}>
                <Paper
                  sx={{
                    p: 2,
                    display: 'flex',
                    flexDirection: 'column',
                    height: 240,
                  }}
                >
                   {user.organizations.map((organization) => (
                <Box key={organization.id} sx={{ mb: 2 }}>
                  <Typography variant="h6">{organization.name}</Typography>
                  <Button
                    variant="contained"
                    color="primary"
                    sx={{ mr: 2 }}
                    href={`/${organization.slug}/team`}
                  >
                    Team
                  </Button>

                  <Button
                    variant="contained"
                    color="secondary"
                    sx={{ mr: 2 }}
                    href={`/${organization.slug}/team/roles`}
                  >
                    Roles
                  </Button>

                  <Button
                    variant="contained"
                    color="secondary"
                    href={`/${organization.slug}/team/permissions`}
                  >
                    Permissions
                  </Button> 

                  {/* <Button
                    variant="contained"
                    color="primary"
                    sx={{ mr: 2 }}
                    href={`/${organization.slug}/team`}
                  >
                    Equipo
                  </Button>
                  <Button
                    variant="contained"
                    color="secondary"
                    sx={{ mr: 2 }}
                    href={`/${organization.slug}/team/roles`}
                  >
                    Roles
                  </Button>
                  <Button
                    variant="contained"
                    color="default"
                    href={`/${organization.slug}/team/permissions`}
                  >
                    Permisos
                  </Button> */}
                </Box>
              ))}
                </Paper>
              </Grid>
              {/* Recent Deposits */}
              <Grid item xs={12} md={4} lg={3}>
                <Paper
                  sx={{
                    p: 2,
                    display: 'flex',
                    flexDirection: 'column',
                    height: 240,
                  }}
                >
                  
                </Paper>
              </Grid>
              {/* Recent Orders */}
              <Grid item xs={12}>
                <Paper sx={{ p: 2, display: 'flex', flexDirection: 'column' }}>
                  {/* <Orders /> */}
                </Paper>
              </Grid>
            </Grid>
            {/* <Copyright sx={{ pt: 4 }} /> */}
          </Container>
    </AuthenticatedLayout>
  );
}
