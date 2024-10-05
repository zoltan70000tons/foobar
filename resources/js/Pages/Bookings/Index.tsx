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
  categories,
  cabins,
  errors,
}: PageProps & { tab: string; data: any }) => {
  const { hasPermission } = usePermissions();
  const [selectedTab, setSelectedTab] = useState(0);

  const [openDialog, setOpenDialog] = useState(false);

  const [loading, setLoading] = useState(true);

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
