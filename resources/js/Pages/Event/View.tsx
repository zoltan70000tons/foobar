import React, { useState } from "react";
import { Head, useForm, usePage } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Typography,
  Button,
  Tooltip,
  IconButton,
} from "@mui/material";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import dayjs from "dayjs";
import ImageUpload from "@/Components/ImageUpload";
import EventStatusSelect from "@/Components/EventStatusSelect";
import { usePermissions } from "@/Providers/PermissionContext";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { ArrowBack, Delete, Edit } from "@mui/icons-material";
import DashboardCard from "../Dashboard/DashboardCard";
import RoomPreferencesIcon from '@mui/icons-material/RoomPreferences';
import { Permissions } from "@/enums/PermissionEnum";


const View = ({ auth, event }: PageProps) => {
  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });
  const { get, delete: destroy } = useForm();
  const { hasPermission } = usePermissions();

  console.log(event);

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleEdit = () => {
    get(route('events.edit', { event: event.id}));
  }

  const handleBack = () => {
    window.history.back();
  }

  const handleDelete = () => {
    const confirmed = window.confirm('Are you sure you want to delete this event?');
    if (confirmed) {
      destroy(route('events.destroy', { event: event.id }));
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="View Event" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.ViewEvents) && (
            <>
            <Grid container spacing={3} sx={{mt:2}}>
              {hasPermission(Permissions.ViewCabins) && (
              <Grid item xs={12} sm={6} md={3}>
                <DashboardCard
                  title="Cabins"
                  description="Manage cabins"
                  Icon={RoomPreferencesIcon}
                  link={`/events/${event.id}/cabins/`}
                  //badgeContent={1}
                />
              </Grid>
              )}
            </Grid>
            <Paper
              sx={{
                p: 2,
                display: "flex",
                flexDirection: "column",
                minHeight: 240,
                width: "100%",
              }}
            >
              <Box sx={{ mt: 4 }}>
                <Typography variant="body2" style={{ marginBottom: "1rem" }}>
                  Event Name
                </Typography>
                <TextField
                  fullWidth
                  label="Event Name"
                  variant="outlined"
                  value={event.name}
                  InputProps={{ readOnly: true }}
                />
              </Box>
              <Box sx={{ mt: 4 }}>
                <TextField
                  fullWidth
                  label="Event Description"
                  variant="outlined"
                  multiline
                  rows={4}
                  value={event.description}
                  InputProps={{ readOnly: true }}
                />
              </Box>
              <Box sx={{ mt: 4 }}>
                <TextField
                  fullWidth
                  label="Event Destination"
                  variant="outlined"
                  value={event.address}
                  InputProps={{ readOnly: true }}
                />
              </Box>

              <Box sx={{ mt: 4 }}>
                <LocalizationProvider dateAdapter={AdapterDayjs}>
                  <Grid container spacing={2}>
                    <Grid item xs={12} md={6}>
                      <DatePicker
                        label="Start Date"
                        sx={{ width: "100%" }}
                        value={
                          event.start_date ? dayjs(event.start_date) : null
                        }
                        renderInput={(params) => (
                          <TextField
                            {...params}
                            fullWidth
                            InputProps={{ readOnly: true }}
                          />
                        )}
                      />
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <DatePicker
                        label="End Date (Optional)"
                        sx={{ width: "100%" }}
                        value={event.end_date ? dayjs(event.end_date) : null}
                        renderInput={(params) => (
                          <TextField
                            {...params}
                            fullWidth
                            InputProps={{ readOnly: true }}
                          />
                        )}
                      />
                    </Grid>
                  </Grid>
                </LocalizationProvider>
              </Box>

              <Box sx={{ mt: 4 }}>
                <ImageUpload initialImageUrl={event.image} />
              </Box>

              <Box sx={{ mt: 4 }}>
                <TextField
                  fullWidth
                  label="Event Status"
                  variant="outlined"
                  value={event.status}
                  InputProps={{ readOnly: true }}
                />
              </Box>

              <Box sx={{ mt: 4 }}>
                <div
                  style={{
                    display: "flex",
                    justifyContent: "flex-end",
                    gap: "8px",
                  }}
                >
                  <Tooltip title="Back">
                    <IconButton color="primary" onClick={handleBack}>
                      <ArrowBack />
                    </IconButton>
                  </Tooltip>
                  {hasPermission('Edit Event') && (<Tooltip title="Edit"> 
                    <IconButton color="primary" onClick={handleEdit}>
                      <Edit />
                    </IconButton>
                  </Tooltip>)}
                  {hasPermission('Delete Event') && (<Tooltip title="Delete">
                    <IconButton color="error" onClick={handleDelete}>
                      <Delete />
                    </IconButton>
                  </Tooltip>) }
                  
                </div>
              </Box>
            </Paper>
            </>
          )}
          <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
          />
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
