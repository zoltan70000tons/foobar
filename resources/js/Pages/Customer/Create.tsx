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
  MenuItem,
} from "@mui/material";
import SnackbarAlert from "@/Components/SnackbarAlert";
import {Permissions} from "@/enums/PermissionEnum";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import {usePermissions} from "@/Providers/PermissionContext";

const Create = ({ auth, errors }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { data, setData, post, processing } = useForm({
    username: "",
    email: "",
    survivor_number: "",
    first_name: "",
    last_name: "",
    middle_name: "",
    gender: "",
    year: "",
    month: "",
    day: "",
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

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleChange = <TForm extends Record<string, any>>(
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const {name, value} = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleStringChange = <TForm extends Record<string, any>>(
      value: string, name: string
  ) => {
    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

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
        console.log(errors);
        setSnackbar({
          open: true,
          severity: "error",
          message: "Error creating customer",
        });
      },
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Customers"}>
        <Head title="Create Customer"/>
        <Toolbar/>
        <Container maxWidth="lg" sx={{mt: 4, mb: 4}}>
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
                    <Box sx={{width: "100%"}}>
                      <Grid container spacing={2}>
                        <Grid item xs={6} sx={{mr: 2}}>
                          <TextField
                              fullWidth
                              label="Survivor Number"
                              variant="outlined"
                              value={data.survivor_number}
                              name={"survivor_number"}
                              onChange={handleChange}
                          />
                        </Grid>
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

                    <Typography variant="h6" sx={{mt: 2, mb: 2}}>
                      Date of Birth
                    </Typography>
                    <Box sx={{width: "100%"}}>
                      <Grid container spacing={2}>
                        <Grid item xs={2}>
                          <TextField
                              fullWidth
                              label="Year"
                              variant="outlined"
                              value={data.year}
                              name={"year"}
                              onChange={handleChange}
                          />
                        </Grid>
                        <Grid item xs={2}>
                          <TextField
                              fullWidth
                              label="Month"
                              variant="outlined"
                              value={data.month}
                              name={"month"}
                              onChange={handleChange}
                          />
                        </Grid>
                        <Grid item xs={2}>
                          <TextField
                              fullWidth
                              label="Day"
                              variant="outlined"
                              value={data.day}
                              name={"day"}
                              onChange={handleChange}
                          />
                        </Grid>
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

                    <Typography variant="h6" sx={{mt: 2, mb: 2}}>
                      Phone Number
                    </Typography>
                    <Box sx={{width: "100%"}}>
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

                    <Typography variant="h6" sx={{mt: 2, mb: 2}}>
                      Address Information
                    </Typography>
                    <Box sx={{width: "100%"}}>
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
                          <TextField
                              fullWidth
                              label="State"
                              variant="outlined"
                              value={data.state}
                              name={"state"}
                              onChange={handleChange}
                          />
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

                    <Typography variant="h6" sx={{mt: 2, mb: 2}}>
                      Emergency Contact
                    </Typography>
                    <Box sx={{width: "100%"}}>
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

                    <Typography variant="h6" sx={{mt: 2, mb: 2}}>
                      Preferred Language
                    </Typography>
                    <Box sx={{width: "100%"}}>
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
                    <Box sx={{mt: 4}}>
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
