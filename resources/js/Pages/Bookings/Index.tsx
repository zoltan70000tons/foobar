import React, { useState, useMemo, useEffect } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import {
  Box,
  Container,
  Grid,
  IconButton,
  Toolbar,
  Typography,
  Tabs,
  Tab,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Button,
} from "@mui/material";
import MuiTable from "@/Components/tables/MuiTable";
import { CabinStatus, CabinStatusColor, CabinStatusReduced } from "@/enums/CabinStatus";
import { TagEnum } from "@/enums/TagEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import LoadingOverlay from "@/Components/LoadingOverlay";
import SnackbarAlert from "@/Components/SnackbarAlert";

const Index = ({
  auth,
  event,
  bookings,
  errors,
}: PageProps & { tab: string; data: any }) => {
  const { hasPermission } = usePermissions();
  const [selectedTab, setSelectedTab] = useState(0);

  const [openDialog, setOpenDialog] = useState(false);

  const [loading, setLoading] = useState(true);
  console.log(bookings);

  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  
  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
  };

  const bookingColumns = useMemo(
    () => [
      {
        header: "Id",
        accessor: "id",
      },
      {
        header: "Booking code",
        accessor: "booking_code",
      },
      {
        header: "Lead passenger",
        accessor: "lead_passenger",
        draw: (row) => (
          <>{row.customer?.detail?.short_name}</>
      )
      },
      {
        header: "Type",
        accessor: "cabin",
        draw: (row) => (
            <>{row.cabin?.cabin_type?.cabin_type}</>
        )
      },
      {
        header: "Balance",
        accessor: "tags",
      },
      
      {
        header: "Actions",
        accessor: "",
        disableFilter: true,
        draw: (row) => (
          // <div style={{ display: "flex", gap: "10px" }}>
          //   {hasPermission(Permissions.ViewCabinCategories) && (
          //     <Visibility
          //       onClick={() => handleViewClick(row)}
          //       style={{ cursor: "pointer" }}
          //     />
          //   )}
          //   {hasPermission(Permissions.EditCabinCategories) && (
          //     <Edit
          //       onClick={() => handleEditClick(row)}
          //       style={{ cursor: "pointer" }}
          //     />
          //   )}
          //   {hasPermission(Permissions.DeleteCabinCategories) && (
          //     <Delete
          //       onClick={() => handleDeleteClick(row)}
          //       style={{ cursor: "pointer" }}
          //     />
          //   )}
          // </div>
          <></>
        ),
      },
    ],
    []
  );


  return (
    <AuthenticatedLayout user={auth.user} header={"Cabins"}>
      <Head title="Cabins" />
      <Toolbar sx={{ mt: 8 }}>
        <IconButton edge="start" color="inherit" aria-label="menu">
          <img src={event.image} alt="Logo" style={{ height: 40 }} />
        </IconButton>
        <Typography variant="h6" style={{ flexGrow: 1 }}>
          {event.name}
        </Typography>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              {/* Tabs for navigation */}
              <Tabs
                value={selectedTab}
                onChange={handleTabChange}
                aria-label="manage inventory and categories"
              >
                <Tab label="NEW BOOKINGS" />
                <Tab label="IN PROGRESS" />
                <Tab label="UPLOADED TO MANIFEST" />
                <Tab label="ALL" />
              </Tabs>

           
              <Box
                sx={{ display: selectedTab === 0 ? "block" : "none", mt: 2 }}
              >
                <MuiTable
                  columns={bookingColumns}
                  data={bookings}
                />
              
                
              </Box>

              <Box
                sx={{ display: selectedTab === 1 ? "block" : "none", mt: 2 }}
              >
                
              </Box>
            </Box>
           
            <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
          />
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
