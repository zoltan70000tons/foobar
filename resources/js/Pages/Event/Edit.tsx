import React, { useState } from "react";
import { Head, useForm , usePage} from "@inertiajs/react";
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
  SelectChangeEvent,
} from "@mui/material";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import dayjs, { Dayjs } from "dayjs";
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import ImageUpload from "@/Components/ImageUpload";
import EventStatusSelect from "@/Components/EventStatusSelect";
import { EventStatus } from "@/enums/EventStatusEnum";
import { usePermissions } from "@/Providers/PermissionContext";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";


const Edit = ({ auth, errors}: PageProps) => {
  const { event, membership_presale_periods } = usePage().props;
  const [snackbar, setSnackbar] = useState({ open: false, severity: 'success', message: '' });
  const { hasPermission } = usePermissions();
  const { data, setData, head, processing } = useForm({
    name: event.name || "",
    description: event.description || "",
    destination: event.address || "",
    start_date: event.start_date || "",
    end_date: event.end_date || "",
    status: event.status || "",
    image: event.image || null,
    membership_presale_periods: membership_presale_periods || [],
  });

  dayjs.extend(utc);
  dayjs.extend(timezone);

  console.log(data.membership_presale_periods)

  const handleDateChange = (field: 'start_date' | 'end_date') => (newValue: Dayjs | null) => {
    setData(field, newValue ? newValue.format("YYYY/MM/DD") : null);
  };

  const handlePeriodDateChange = (index: number, field: 'start_date' | 'end_date') => (newValue: Dayjs | null) => {
    setData((prevData) => {
      const updated = [...prevData.membership_presale_periods];

      if (!updated[index].presale_period) {
        updated[index].presale_period = {
          event_id: event.id,
          membership_type_id: updated[index].membership_type.id,
          start_date: null,
          end_date: null,
        };
      }

      updated[index].presale_period[field] = newValue
        ? newValue.tz('America/New_York', true).format('YYYY-MM-DDTHH:mm:ss')
        : null;

      return { ...prevData, membership_presale_periods: updated };
    });
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

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (data.start_date && data.end_date && new Date(data.start_date).getTime() > new Date(data.end_date).getTime()) {
      alert("The end date cannot be earlier than the start date.");
      return;
    }

    for (const item of data.membership_presale_periods) {
      const presale = item.presale_period;
      if (presale) {
        const start = new Date(presale.start_date);
        const end = new Date(presale.end_date);

        if (start >= end) {
          alert('The Pre-Sale end date cannot be earlier than the start date.');
          return;
        }
      }
    }

    const formData = new FormData();
    for (const key in data) {
      if (key === 'membership_presale_periods') {
        formData.append(key, JSON.stringify(data[key]));
      } else {
        formData.append(key, data[key]);
      }
    }
    formData.append('_method', 'PUT');

    router.post(`/events/${event.id}`, formData, {
        forceFormData: true,
        onSuccess: (response) => {
            setSnackbar({ open: true, severity: 'success', message: 'Event edited successfully' });
        },
        onError: (errors) => {
            setSnackbar({ open: true, severity: 'error', message: 'Error editing event' });
        },
      });
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleBack = () => {
    router.visit(route('events.index'));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Events"}>
      <Head title="Events" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.EditEvents) && (<Paper
            sx={{
              p: 2,
              display: "flex",
              flexDirection: "column",
              minHeight: 240,
              width: "100%",
            }}
          >
            <Box style={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
              gap: "8px",
            }}>
              <h1>Edit Event</h1>
              <Box>
                <div
                  style={{
                    display: "flex",
                    justifyContent: "flex-start",
                    gap: "8px",
                  }}
                >
                  <Button onClick={handleBack} variant="outlined" color="secondary">
                    Back
                  </Button>
                </div>
              </Box>
            </Box>
            <form onSubmit={handleSubmit} encType="multipart/form-data">
              <Box sx={{ mt: 4 }}>
                <Typography variant="body2" style={{ marginBottom: "1rem" }}>
                  Event Name
                </Typography>
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
                    <Grid item xs={12} md={6}>
                      <DatePicker
                        label="Start Date"
                        sx={{ width: '100%' }}
                        value={data.start_date ? dayjs(data.start_date) : null}
                        disablePast
                        onChange={handleDateChange('start_date')}
                        renderInput={(params) => <TextField {...params} fullWidth />}
                        error={Boolean(errors.start_date)}
                        helperText={errors.start_date}
                      />
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <DatePicker
                        label="End Date (Optional)"
                        sx={{ width: '100%' }}
                        value={data.end_date ? dayjs(data.end_date) : null}
                        disablePast
                        onChange={handleDateChange('end_date')}
                        renderInput={(params) => <TextField {...params} fullWidth />}
                        error={Boolean(errors.end_date)}
                        helperText={errors.end_date}
                      />
                    </Grid>
                  </Grid>
                </LocalizationProvider>
              </Box>

              <Box sx={{ mt: 4 }}>
                <ImageUpload onChange={handleImageChange} initialImageUrl={data.image} />
                {errors.image && (
                    <Box
                      display="flex"
                      alignItems="center"
                      sx={{ border: '1px solid red', borderRadius: '6px', p: 1 , mt:1 }}
                    >
                      <Typography fontSize="0.75rem" color="error">{errors.image}</Typography>
                    </Box>
                  )}
              </Box>

              <Box sx={{ mt: 4 }}>
                <EventStatusSelect value={data.status} onChange={handleStatusChange} errors={errors}/>
              </Box>

              {(data.membership_presale_periods && data.membership_presale_periods.length > 0) && (
                <>
                  <Typography
                    variant="body2"
                    sx={{ display: 'inline-flex', alignItems: 'center', mt: 4 }}
                  >
                    Pre-Sale Periods (Time must be in EST)
                  </Typography>
                  <Grid container item xs={12} spacing={2} sx={{mt: 0}}>
                    {data.membership_presale_periods.map((item, index) => {
                      return (
                        <React.Fragment key={item.membership_type.id}>
                          <Grid item xs={4}>
                            <TextField
                              fullWidth
                              label="Membership Type"
                              variant="outlined"
                              value={item.membership_type?.name}
                              InputProps={{ readOnly: true }}
                            />
                          </Grid>
                          <Grid item xs={4}>
                            <LocalizationProvider dateAdapter={AdapterDayjs}>
                              <DatePicker
                                label="Start Date"
                                sx={{ width: "100%" }}
                                disablePast
                                onChange={handlePeriodDateChange(index, 'start_date')}
                                value={
                                  item.presale_period?.start_date ? dayjs(item.presale_period.start_date) : null
                                }
                                renderInput={(params) => (
                                  <TextField
                                    {...params}
                                    fullWidth
                                    InputProps={{ readOnly: true }}
                                  />
                                )}
                              />
                            </LocalizationProvider>
                          </Grid>
                          <Grid item xs={4}>
                            <LocalizationProvider dateAdapter={AdapterDayjs}>
                              <DatePicker
                                label="End Date"
                                sx={{ width: "100%" }}
                                disablePast
                                onChange={handlePeriodDateChange(index, 'end_date')}
                                value={
                                  item.presale_period?.end_date ? dayjs(item.presale_period.end_date) : null
                                }
                                renderInput={(params) => (
                                  <TextField
                                    {...params}
                                    fullWidth
                                    InputProps={{ readOnly: true }}
                                  />
                                )}
                              />
                            </LocalizationProvider>
                          </Grid>
                        </React.Fragment>
                      );
                    })}
                  </Grid>
                </>
              )}

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

export default Edit;
