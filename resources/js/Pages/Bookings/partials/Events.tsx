import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Avatar, Box, Container, Grid, Typography, Toolbar, useTheme, Alert } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import { Permissions } from "@/enums/PermissionEnum";
import EventSelector from "@/Components/EventSelector";

const Events = ({ auth, events }: PageProps) => {
  const { hasPermission } = usePermissions();
  const theme = useTheme();
  dayjs.extend(localizedFormat);

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Events" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Alert severity="info">To continue, please select an event.</Alert>
        <br />
        {hasPermission(Permissions.ViewEvents) && <EventSelector events={events} url="/bookings" />}
      </Container>
    </AuthenticatedLayout>
  );
};

export default Events;
