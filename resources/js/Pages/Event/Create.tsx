import React, { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Button,
  Typography,
  Select,
  SelectChangeEvent,
} from "@mui/material";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import dayjs, { Dayjs } from "dayjs";
import ImageUpload from "@/Components/ImageUpload";
import { EventStatus } from "@/enums/EventStatusEnum";
import EventStatusSelect from "@/Components/EventStatusSelect";
import SnackbarAlert from "@/Components/SnackbarAlert";

const Create = ({ auth, errors }: PageProps) => {
  const { data, setData, post, processing } = useForm({
    name: "",
    description: "",
    destination: "",
    start_date: null,
    end_date: null,
    status: "",
    image: null,
  });

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleDateChange =
    (field: "start_date" | "end_date") => (newValue: Dayjs | null) => {
      setData(field, newValue ? newValue.format("YYYY/MM/DD") : null);
    };

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    setData(e.target.name, e.target.value);
  };

  const handleImageChange = (file: File | null) => {
    setData("image", file);
  };

  const handleStatusChange = (event: SelectChangeEvent<EventStatus>) => {
    setData("status", event.target.value);
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (
      data.start_date &&
      data.end_date &&
      new Date(data.start_date).getTime() > new Date(data.end_date).getTime()
    ) {
      alert("The end date cannot be earlier than the start date.");
      return;
    }

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    router.post(route("events.store"), formData, {
      forceFormData: true,
      onSuccess: (response) => {
        setSnackbar({
          open: true,
          severity: "success",
          message: "Event created successfully",
        });
      },
      onError: (errors) => {
        setSnackbar({
          open: true,
          severity: "error",
          message: "Error creating event",
        });
      },
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Events" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Paper
            sx={{
              p: 2,
              display: "flex",
              flexDirection: "column",
              minHeight: 240,
              width: "100%",
            }}
          >
            <h1>Create Event</h1>
            <form onSubmit={handleSubmit}>
              <Box sx={{ mt: 4 }}>
                {/* <Typography variant="body2" style={{ marginBottom: "1rem" }}>
                  Event Name
                </Typography> */}
                <TextField
                  name="name"
                  fullWidth
                  label="Event Name"
                  variant="outlined"
                  value={data.name}
                  onChange={handleChange}
                  error={Boolean(errors.name)}
                  helperText={errors.name}
                />
              </Box>
              <Box sx={{ mt: 4 }}>
                <TextField
                  name="description"
                  fullWidth
                  label="Event Description"
                  variant="outlined"
                  multiline
                  rows={4}
                  placeholder="Enter your text here"
                  value={data.description}
                  onChange={handleChange}
                  error={Boolean(errors.description)}
                  helperText={errors.description}
                />
              </Box>
              <Box sx={{ mt: 4 }}>
                <TextField
                  name="destination"
                  fullWidth
                  label="Event Destination"
                  variant="outlined"
                  value={data.destination}
                  onChange={handleChange}
                  error={Boolean(errors.destination)}
                  helperText={errors.destination}
                />
              </Box>

              <Box sx={{ mt: 4 }}>
                <LocalizationProvider dateAdapter={AdapterDayjs}>
                  <Grid container spacing={2}>
                    <Grid item xs={12} sm={6}>
                      <DatePicker
                        label="Start Date"
                        sx={{ width: "100%" }}
                        value={data.start_date ? dayjs(data.start_date) : null}
                        disablePast
                        onChange={handleDateChange("start_date")}
                        renderInput={(params) => (
                          <TextField {...params} fullWidth />
                        )}
                        error={Boolean(errors.start_date)}
                        helperText={errors.start_date}
                      />
                    </Grid>
                    <Grid item xs={12} sm={6}>
                      <DatePicker
                        label="End Date (Optional)"
                        sx={{ width: "100%" }}
                        value={data.end_date ? dayjs(data.end_date) : null}
                        disablePast
                        onChange={handleDateChange("end_date")}
                        renderInput={(params) => (
                          <TextField {...params} fullWidth />
                        )}
                        error={Boolean(errors.end_date)}
                        helperText={errors.end_date}
                      />
                    </Grid>
                  </Grid>
                </LocalizationProvider>
              </Box>

              <Box sx={{ mt: 4 }}>
                <ImageUpload onChange={handleImageChange} />
                {errors.image && (
                  <Box
                    display="flex"
                    alignItems="center"
                    sx={{
                      border: "1px solid red",
                      borderRadius: "6px",
                      p: 1,
                      mt: 1,
                    }}
                  >
                    <Typography fontSize="0.75rem" color="error">
                      {errors.image}
                    </Typography>
                  </Box>
                )}
              </Box>

              <Box sx={{ mt: 4 }}>
                <EventStatusSelect
                  value={data.status}
                  onChange={handleStatusChange}
                  errors={errors}
                />
              </Box>
              <Box sx={{ mt: 4 }}>
                <Button
                  variant="contained"
                  color="primary"
                  fullWidth
                  type="submit"
                  disabled={processing}
                >
                  {processing ? "Submitting..." : "Submit"}
                </Button>
              </Box>
            </form>
          </Paper>
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

export default Create;
