import React, { useEffect, useState } from "react";
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
  MenuItem, FormHelperText, FormControl, InputLabel,
} from "@mui/material";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import iso3166 from 'iso-3166-2';

const Create = ({ auth, errors }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { data, setData, post, processing } = useForm({
    username: "",
    email: "",
    survivor_number: "",
    first_name: "",
    last_name: "",
    middle_name: "",
    gender: "M",
    dob: "",
    citizenship: "",
    phone: "",
    address_first: "",
    address_second: "",
    city: "",
    state: "",
    postal_code: "",
    country: "",
    emergency_c_name: "",
    emergency_c_phone: "",
    language: "",
  });

  const selectedCountry = data.country;

  const getStateOptions = (countryCode: string) => {
    const subdivisions = iso3166.country(countryCode)?.sub || {};

    const seen = new Set<string>();

    return Object.entries(subdivisions)
      .map(([fullCode, { name }]) => ({ label: name, value: name }))
      .filter(({ label }) => {
        if (seen.has(label)) {
          return false;
        }
        seen.add(label);
        return true;
      })
      .sort((a, b) => a.label.localeCompare(b.label));
  };

  const stateOptions = getStateOptions(selectedCountry);

  useEffect(() => {
    setData("state", "");
  }, [selectedCountry]);

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleChange = <TForm extends Record<string, unknown>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };


  const handleSelectChange = (
    e: React.ChangeEvent<{ name?: string; value: unknown }>
  ) => {
    const name = e.target.name;
    const value = e.target.value;

    setData("state", value as string);
  };

  const handleStringChange = <TForm extends Record<string, unknown>>(
    value: string,
    name: keyof TForm
  ) => {
    setData(name, value as TForm[keyof TForm]);
  };


  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false, message: "" });
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setSnackbar({ ...snackbar, message: "" });

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    router.post(route("customers.store"), formData, {
      forceFormData: true,
      onSuccess: (response) => {
        setSnackbar({
          open: true,
          severity: "success",
          message: "Customer created successfully",
        });
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join("\n");
        setSnackbar({
          open: true,
          severity: "error",
          message: `Error creating customer\n${errorMessages}`,
        });
      },
    });
  };

  const handleBack = () => {
    //window.history.back(); //Keeps ordering and filtering, does not reload when data changed on EDIT
    router.visit(route("customers.index"), {
      only: ['users'],
    })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={"Customers"}>
      <Head title="Create Customer" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleBack}>
          Back
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.CreateCustomers) && (<Paper
            sx={{
              p: 2,
              display: "flex",
              flexDirection: "column",
              minHeight: 240,
              width: "100%",
            }}
          >
            <h1>Create Customer</h1>
            <form onSubmit={handleSubmit} encType="multipart/form-data">
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="eMail"
                      variant="outlined"
                      value={data.email}
                      name={"email"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Username"
                      variant="outlined"
                      value={data.username}
                      name={"username"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="First Name"
                      variant="outlined"
                      value={data.first_name}
                      name={"first_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Middle Name"
                      variant="outlined"
                      name={"middle_name"}
                      value={data.middle_name}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Last Name"
                      variant="outlined"
                      value={data.last_name}
                      name={"last_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Select
                      fullWidth
                      label="Gender"
                      variant="outlined"
                      value={data.gender}
                      name={"gender"}
                      onChange={handleChange}
                    >
                      <MenuItem value={"M"}>Male</MenuItem>
                      <MenuItem value={"F"}>Female</MenuItem>
                    </Select>
                  </Grid>
                </Grid>
              </Box>

              <Box sx={{ width: "100%", mt: 2, mb: 2 }}>
                <Grid container spacing={2}>
                  <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <Grid item xs={6}>
                      <DatePicker
                        label="Date of Birth"
                        sx={{ width: "100%" }}
                        value={data.dob ? dayjs(data.dob) : null}
                        maxDate={dayjs()} // Restricts future dates
                        format="YYYY-MM-DD" // Ensures consistent formatting
                        onChange={(e) => handleStringChange(e, "dob")}
                        renderInput={(params) => (
                          <TextField {...params} fullWidth />
                        )}
                      />
                    </Grid>
                  </LocalizationProvider>
                  <Grid item xs={6}>
                    <Country
                      fullWidth
                      label="Country"
                      variant="outlined"
                      value={data.citizenship}
                      name={"citizenship"}
                      onChange={(e) => handleStringChange(e, 'citizenship')}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Phone Number
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <PhoneNumber
                      value={data.phone || ""}
                      forceDialCode={true}
                      name={"phone"}
                      onChange={(e) => handleStringChange(e, 'phone')}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Address Information
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Address Line 1"
                      variant="outlined"
                      value={data.address_first}
                      name={"address_first"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Address Line 2"
                      variant="outlined"
                      value={data.address_second}
                      name={"address_second"}
                      onChange={handleChange}
                    />
                  </Grid>

                  <Grid item xs={4}>
                    <TextField
                      fullWidth
                      label="City"
                      variant="outlined"
                      value={data.city}
                      name={"city"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={4}>
                    <FormControl required={selectedCountry === "USA" || selectedCountry === "CAN"} fullWidth error={!!errors.state}>
                      <InputLabel id="state-label">State</InputLabel>
                      <Select
                        labelId="state-label"
                        label="State"
                        value={data.state}
                        onChange={handleSelectChange}
                        variant="outlined"
                        error={!!errors.state}
                      >
                        {stateOptions.map(({ value, label }) => (
                          <MenuItem key={value} value={value}>
                            {label}
                          </MenuItem>
                        ))}
                      </Select>
                      <FormHelperText>{errors.state}</FormHelperText>
                    </FormControl>
                  </Grid>
                  <Grid item xs={4}>
                    <TextField
                      fullWidth
                      label="Zip Code"
                      variant="outlined"
                      value={data.postal_code}
                      name={"postal_code"}
                      onChange={handleChange}
                    />
                  </Grid>

                  <Grid item xs={12}>
                    <Country
                      fullWidth
                      label="Country"
                      variant="outlined"
                      value={data.country}
                      name={"country"}
                      onChange={(e) => handleStringChange(e, 'country')}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Emergency Contact
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Contact Full Name"
                      variant="outlined"
                      value={data.emergency_c_name}
                      name={"emergency_c_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <PhoneNumber
                      value={data.emergency_c_phone || ""}
                      forceDialCode={true}
                      name={"emergency_c_phone"}
                      onChange={(e) => handleStringChange(e, 'emergency_c_phone')}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Preferred Language
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <Select
                      fullWidth
                      label="Preferred Language"
                      variant="outlined"
                      value={data.language}
                      name={"language"}
                      onChange={handleChange}
                    >
                      <MenuItem value={"de"}>Deutsch</MenuItem>
                      <MenuItem value={"en"}>English</MenuItem>
                      <MenuItem value={"es"}>Español</MenuItem>
                    </Select>
                  </Grid>
                </Grid>
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
          )}
          <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
            horizontal={"center"}
            vertical={"top"}
          />
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Create;
