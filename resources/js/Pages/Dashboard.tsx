import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Toolbar, Grid } from "@mui/material";
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
import TagIcon from "@mui/icons-material/Tag";
import HistoryIcon from "@mui/icons-material/History";
import JoinInnerIcon from "@mui/icons-material/JoinInner";

export default function Dashboard({ auth }: PageProps) {
  const { hasPermission } = usePermissions();

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
      title: "Tags",
      description: "Manage tags",
      icon: TagIcon,
      link: "/tags",
      permission: Permissions.ViewTags,
    },
    {
      title: "Logs",
      description: "Check Logs",
      icon: HistoryIcon,
      link: "/logs",
      permission: Permissions.ViewLogs,
    },
    {
      title: "Potential Survivor Matches",
      description: "Potential Survivor Matches",
      icon: JoinInnerIcon,
      link: "/potential-survivor-matches",
      permission: Permissions.ViewCustomers,
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
