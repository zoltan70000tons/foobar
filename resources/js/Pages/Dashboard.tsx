import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Container, Toolbar, Grid, Box, Typography, Button, Paper } from '@mui/material';
import RecentOrders from './Dashboard/RecentOrders';
import DashboardCard from './Dashboard/DashboardCard';
import GroupIcon from '@mui/icons-material/Group';
import DirectionsBoatIcon from '@mui/icons-material/DirectionsBoat';
import LocalActivityIcon from '@mui/icons-material/LocalActivity';
import GroupWorkIcon from '@mui/icons-material/GroupWork';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import LocalPoliceIcon from '@mui/icons-material/LocalPolice';
import { usePermissions } from '@/Providers/PermissionContext';
import Skeleton from '@mui/material/Skeleton';


export default function Dashboard({ auth }: PageProps) {
  const { hasPermission, loading, error } = usePermissions();
  const { user } = auth;

  //if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={"Dashboard"}
    >
      <Head title="Dashboard" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12} md={12} lg={12}>
            {loading ?  <>
              <Box sx={{ width: 300 }}>
             <Skeleton />
             <Skeleton animation="wave" />
             <Skeleton animation={true} />
             
           </Box> 
            </> :
            (<Grid container spacing={3}>
              {hasPermission('View Users') && (
                <Grid item xs={12} sm={6} md={3}>
                  <DashboardCard
                    title="Team"
                    description="Manage your team"
                    Icon={GroupWorkIcon}
                    link="/70k/team"
                    badgeContent={4}
                  />
                </Grid>
              )}
              {hasPermission('View Orders') && (
              <Grid item xs={12} sm={6} md={3}>
                <DashboardCard
                  title="Orders"
                  description="Manage your orders"
                  Icon={ShoppingCartIcon}
                  link="/70k/"
                  badgeContent={67}
                />
              </Grid>
              )}
              {hasPermission('View Events') && (
              <Grid item xs={12} sm={6} md={3}>
                <DashboardCard
                  title="Events"
                  description="Manage your events"
                  Icon={LocalActivityIcon}
                  link="/70k/events"
                  badgeContent={1}
                />
              </Grid>
              )}
              {hasPermission('View Customers') && (
              <Grid item xs={12} sm={6} md={3}>
                <DashboardCard
                  title="Customers"
                  description="Manage your customers"
                  Icon={GroupIcon}
                  link="/70k/customers"
                  badgeContent={1}
                />
              </Grid>
              )}
              {hasPermission('View Roles') && (
              <Grid item xs={12} sm={6} md={3}>
                <DashboardCard
                  title="Roles"
                  description="Manage organization roles"
                  Icon={LocalPoliceIcon}
                  link="/70k/team/roles"
                  badgeContent={1}
                />
              </Grid>
              )}
            </Grid>)
            
            }
            
          </Grid>
          <Grid item xs={12}>
            <Paper sx={{ p: 2, display: 'flex', flexDirection: 'column' }}>
              <RecentOrders />
            </Paper>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
}
