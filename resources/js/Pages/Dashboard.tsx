import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Toolbar, Grid, CircularProgress, Typography } from "@mui/material";
import DashboardCard from "./Dashboard/DashboardCard";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";

// Icons
import LocalActivityIcon from "@mui/icons-material/LocalActivity";
import RoomPreferencesIcon from "@mui/icons-material/RoomPreferences";
import PersonIcon from "@mui/icons-material/Person";
import GroupWorkIcon from "@mui/icons-material/GroupWork";
import DirectionsBoatIcon from "@mui/icons-material/DirectionsBoat";
import LocalPoliceIcon from "@mui/icons-material/LocalPolice";
import TagIcon from '@mui/icons-material/Tag';

export default function Dashboard({ auth }: PageProps) {
  const { hasPermission } = usePermissions();

  //if (error) return <Typography color="error">Error: {error.message}</Typography>;

  // Show loader while permissions are being fetched
  // if (loading) {
  //   return (
  //     <AuthenticatedLayout user={auth.user} header="Dashboard">
  //       <Head title="Dashboard" />
  //       <Toolbar />
  //       <Container
  //         maxWidth="lg"
  //         sx={{ mt: 4, mb: 4, display: "flex", justifyContent: "center", alignItems: "center", height: "50vh" }}
  //       >
  //         <CircularProgress />
  //       </Container>
  //     </AuthenticatedLayout>
  //   );
  // }

  // Dashboard Items Configuration
  const dashboardItems = [
    {
      title: "Bookings",
      description: "Manage Bookings",
      icon: LocalActivityIcon,
      link: "/events/1/bookings?tab=1",
      permission: Permissions.ViewBookings,
    },
    {
      title: "Cabins",
      description: "Manage Cabins",
      icon: RoomPreferencesIcon,
      link: "/events/1/cabins",
      permission: Permissions.ViewCabins,
    },
    {
      title: "Customers",
      description: "Manage customers",
      icon: PersonIcon,
      link: "/customers",
      permission: Permissions.ViewCustomers,
    },
    {
      title: "Team",
      description: "Manage your team",
      icon: GroupWorkIcon,
      link: "/team",
      permission: Permissions.ViewUsers,
    },
    {
      title: "Events",
      description: "Manage your events",
      icon: DirectionsBoatIcon,
      link: "/events",
      permission: Permissions.ViewEvents,
    },
    {
      title: "Roles",
      description: "Manage organization roles",
      icon: LocalPoliceIcon,
      link: "/team/roles",
      permission: Permissions.ViewRoles,
    },
    {
      title: "Customer Tags",
      description: "Manage customer tags",
      icon: TagIcon,
      link: "/customer-tags",
      permission: Permissions.ViewCustomerTags,
    },
  ];

  return (
    <AuthenticatedLayout user={auth.user} header="Dashboard">
      <Head title="Dashboard" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Grid container spacing={3}>
              {dashboardItems
                .filter((item) => hasPermission(item.permission))
                .map(({ title, description, icon: Icon, link }, index) => (
                  <Grid item xs={12} sm={6} md={3} key={index}>
                    <DashboardCard title={title} description={description} Icon={Icon} link={link} />
                  </Grid>
                ))}
            </Grid>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
}
