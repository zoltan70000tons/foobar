import React from "react";
import { Head, router, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Tooltip,
  IconButton,
  Typography,
  Stack,
  Button,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { ArrowBack, Check, Close, Beenhere } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { PotentialSurvivorMatches, PotentialSurvivorMatchStatus } from "@/Pages/PotentialSurvivorMatches/Index";
import { Passenger } from "@/Pages/Bookings/partials/Payment";
import { Booking } from "@/types/booking";
import { Event } from "@/interfaces/Event";

type Props = PageProps & {
  auth: AuthProps;
  potentialMatch: PotentialSurvivorMatches & { survivor_number: string | null };
  booking: Booking;
  event: Event;
  otherPassenger: Passenger & { booking: Booking };
};

const View = ({ auth, potentialMatch, booking, event, otherPassenger }: Props) => {
  const { put, delete: destroy } = useForm();
  const { hasPermission } = usePermissions();
  const { showSnackbar } = useSnackbar();

  const handleBack = () => {
    router.visit(route("matches.index"), {
      only: ["potentialMatch"],
    });
  };

  const handleEdit = () => {
    put(route("matches.update", { id: potentialMatch.id }), {
      onSuccess: () => {
        showSnackbar("Accepted successfully", "success");
      },
      onError: () => {
        showSnackbar("Something went wrong", "error");
      },
    });
  };

  const handleDelete = () => {
    const confirmed = window.confirm("Are you sure you want to delete this potential match?");
    if (confirmed) {
      destroy(route("matches.destroy", { id: potentialMatch.id }), {
        onSuccess: () => {
          showSnackbar("Declined successfully", "success");
        },
        onError: () => {
          showSnackbar("Something went wrong", "error");
        },
      });
    }
  };

  const handleResolve = () => {
    put(route("matches.resolve", { id: potentialMatch.id }), {
      onSuccess: () => {
        showSnackbar("Resolved successfully", "success");
      },
      onError: () => {
        showSnackbar("Something went wrong", "error");
      },
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Potential Survivor Match"}>
      <Head title="View Potential Survivor Match" />
      <Toolbar></Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.ViewCustomers) && (
            <>
              <Paper
                sx={{
                  p: 2,
                  display: "flex",
                  flexDirection: "column",
                  minHeight: 240,
                  width: "100%",
                }}
              >
                {potentialMatch.type === "match" && (
                  <>
                    <Box sx={{ width: "100%" }}>
                      <Grid container spacing={2}>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Event"
                            variant="outlined"
                            value={event.name}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Booking Code"
                            variant="outlined"
                            value={booking.booking_code}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Passenger ID"
                            variant="outlined"
                            value={potentialMatch.passenger_id}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="User Details ID"
                            variant="outlined"
                            value={potentialMatch.user_detail_id}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Passenger First Name"
                            variant="outlined"
                            value={potentialMatch.passenger_first_name}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="User Details First Name"
                            variant="outlined"
                            value={potentialMatch.user_first_name}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Passenger Last Name"
                            variant="outlined"
                            value={potentialMatch.passenger_last_name}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="User Details Last Name"
                            variant="outlined"
                            value={potentialMatch.user_last_name}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                      </Grid>
                    </Box>

                    <Box sx={{ width: "100%", mt: 2, mb: 2 }}>
                      <Grid container spacing={2}>
                        <LocalizationProvider dateAdapter={AdapterDayjs}>
                          <Grid item xs={6}>
                            <TextField
                              fullWidth
                              label="Passenger Date of Birth"
                              variant="outlined"
                              value={potentialMatch.passenger_dob}
                              InputProps={{ readOnly: true }}
                              sx={{ marginBottom: "16px" }}
                            />
                          </Grid>
                          <Grid item xs={6}>
                            <TextField
                              fullWidth
                              label="User Details Date of Birth"
                              variant="outlined"
                              value={potentialMatch.user_dob}
                              InputProps={{ readOnly: true }}
                              sx={{ marginBottom: "16px" }}
                            />
                          </Grid>
                        </LocalizationProvider>
                      </Grid>
                    </Box>

                    <Box sx={{ width: "100%" }}>
                      <Grid container spacing={2}>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Score"
                            variant="outlined"
                            value={potentialMatch.score.toFixed(2)}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                      </Grid>
                    </Box>
                  </>
                )}

                {potentialMatch.type === "double_booking" && (
                  <>
                    <Box sx={{ width: "100%" }}>
                      <Grid container spacing={2}>
                        <Grid item xs={6}>
                          <Typography variant="h5" component="h2" mb={4}>
                            Synced Passenger
                          </Typography>
                          <TextField
                            fullWidth
                            label="Event"
                            variant="outlined"
                            value={event.name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <Stack direction="row" spacing={2} sx={{ marginBottom: "16px" }}>
                            <TextField
                              fullWidth
                              label="Booking Code"
                              variant="outlined"
                              value={booking.booking_code}
                              InputProps={{ readOnly: true }}
                            />
                            <Button
                              variant="contained"
                              color="primary"
                              component="a"
                              href={`/events/${event.id}/bookings/${booking.booking_code}`}
                              target="_blank"
                            >
                              Open
                            </Button>
                          </Stack>
                          <TextField
                            fullWidth
                            label="Passenger First Name"
                            variant="outlined"
                            value={potentialMatch.passenger_first_name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger Last Name"
                            variant="outlined"
                            value={potentialMatch.passenger_last_name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger DOB"
                            variant="outlined"
                            value={potentialMatch.passenger_dob}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger SN"
                            variant="outlined"
                            value={potentialMatch.survivor_number ?? "-"}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                        </Grid>
                        <Grid item xs={6}>
                          <Typography variant="h5" component="h2" mb={4}>
                            Other Passenger
                          </Typography>
                          <TextField
                            fullWidth
                            label="Event"
                            variant="outlined"
                            value={otherPassenger.booking.event.name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <Stack direction="row" spacing={2} sx={{ marginBottom: "16px" }}>
                            <TextField
                              fullWidth
                              label="Booking Code"
                              variant="outlined"
                              value={otherPassenger.booking.booking_code}
                              InputProps={{ readOnly: true }}
                            />
                            <Button
                              variant="contained"
                              color="primary"
                              component="a"
                              href={`/events/${otherPassenger.booking.event_id}/bookings/${otherPassenger.booking.booking_code}`}
                              target="_blank"
                            >
                              Open
                            </Button>
                          </Stack>
                          <TextField
                            fullWidth
                            label="Passenger First Name"
                            variant="outlined"
                            value={otherPassenger.first_name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger Last Name"
                            variant="outlined"
                            value={otherPassenger.last_name}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger DOB"
                            variant="outlined"
                            value={otherPassenger.dob}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                          <TextField
                            fullWidth
                            label="Passenger SN"
                            variant="outlined"
                            value={otherPassenger.survivor_number}
                            InputProps={{ readOnly: true }}
                            sx={{ marginBottom: "16px" }}
                          />
                        </Grid>
                      </Grid>
                    </Box>
                    <Box sx={{ width: "100%" }}>
                      <Grid container spacing={2}>
                        <Grid item xs={6}>
                          <TextField
                            fullWidth
                            label="Score"
                            variant="outlined"
                            value={potentialMatch.score.toFixed(2)}
                            InputProps={{ readOnly: true }}
                          />
                        </Grid>
                      </Grid>
                    </Box>
                  </>
                )}

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
                    {potentialMatch.status === PotentialSurvivorMatchStatus.InProgress && (
                      <>
                        {hasPermission(Permissions.EditCustomers) && potentialMatch.type === "match" && (
                          <Tooltip title="Accept">
                            <IconButton color="success" onClick={handleEdit}>
                              <Check />
                            </IconButton>
                          </Tooltip>
                        )}
                        {hasPermission(Permissions.DeleteCustomers) && potentialMatch.type === "match" && (
                          <Tooltip title="Decline">
                            <IconButton color="error" onClick={handleDelete}>
                              <Close />
                            </IconButton>
                          </Tooltip>
                        )}
                        {hasPermission(Permissions.EditCustomers) && potentialMatch.type === "double_booking" && (
                          <Tooltip title="Resolve">
                            <IconButton color="success" onClick={handleResolve}>
                              <Beenhere />
                            </IconButton>
                          </Tooltip>
                        )}
                      </>
                    )}
                  </div>
                </Box>
              </Paper>
            </>
          )}
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
