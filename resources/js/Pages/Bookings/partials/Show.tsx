import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import {
  Avatar,
  Box,
  Container,
  Grid,
  Typography,
  Toolbar,
  useTheme,
  Alert,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import "dayjs/locale/en";
import localizedFormat from "dayjs/plugin/localizedFormat";
import { Permissions } from "@/enums/PermissionEnum";


const Show = ({ auth, event, booking }: PageProps) => {
  const { hasPermission } = usePermissions();
  console.log(event, booking);
  const theme = useTheme(); 
  dayjs.extend(localizedFormat);

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Booking " />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container>
            <Grid item xs={6}><Typography variant="h5">Booking ID : { booking.booking_code}</Typography></Grid>
           <Grid item xs={6}>asdf</Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Show;
