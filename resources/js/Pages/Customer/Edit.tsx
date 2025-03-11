import React, { useEffect, useState } from "react";
import { Head, useForm, usePage } from "@inertiajs/react";
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
  Tabs,
  Tab,
  InputLabel,
  FormControl,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";
import PhoneNumber from "@/Components/PhoneNumber";
import Country from "@/Components/Country";
import BookingHistory from "@/Pages/Customer/partials/BookingHistory";
import LoadingOverlay from "@/Components/LoadingOverlay";

type Nullable<T> = T | null;

type CustomerDetail = {
  first_name: string;
  last_name: string;
  gender: string;
  middle_name: string;
  dob: string;
  citizenship: string;
  phone: string;
  emergency_c_name: string;
  emergency_c_phone: string;
  language: string;
}

type CustomerAddress = {
  address_first: string;
  address_second: Nullable<string>;
  city: string;
  state: string;
  postal_code: string;
  country: string;
}

type Customer = {
  detail: CustomerDetail;
  customer_address: CustomerAddress;
};

type PageProps = {
  customer: Customer;
}

const Edit = ({ auth, errors }: PageProps) => {
  const { customer, bookings }: PageProps = usePage().props;
  const [snackbar, setSnackbar] = useState({ open: false, severity: 'success', message: '' });
  const [selectedTab, setSelectedTab] = useState(0);
  const [loading, setLoading] = useState(true);
  const { hasPermission } = usePermissions();

  const [year, month, day] = customer?.detail?.dob.split("-");
  const { data, setData, head, processing } = useForm({
    survivor_number: customer.survivor_number.survivor_number || "",
    email: customer.email || "",
    first_name: customer.detail.first_name || "",
    last_name: customer.detail.last_name || "",
    middle_name: customer.detail.middle_name || "",
    gender: customer.detail.gender || "",
    year: year || "",
    month: month || "",
    day: day || "",
    citizenship: customer.detail.citizenship || "",
    phone: customer.detail.phone || "",
    address_first: customer.customer_address?.address_first || "",
    address_second: customer.customer_address?.address_second || "",
    city: customer.customer_address?.city || "",
    state: customer.customer_address?.state || "",
    postal_code: customer.customer_address?.postal_code || "",
    country: customer.customer_address?.country || "",
    emergency_c_name: customer.detail.emergency_c_name || "",
    emergency_c_phone: customer.detail.emergency_c_phone || "",
    language: customer.detail.language || "",
  });

  const handleChange = <TForm extends Record<string, any>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleStringChange = <TForm extends Record<string, any>>(
    value: string, name: string
  ) => {
    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleBack = () => {
    window.history.back();
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }
    formData.append('_method', 'PUT');

    router.post(`/customers/${ customer.id }/update`, formData, {
      forceFormData: true,
      onSuccess: (response) => {
        setSnackbar({ open: true, severity: 'success', message: 'Customer edited successfully' });
      },
      onError: (errors) => {
        const errorMessages = Object.values(errors).join("\n");
        setSnackbar({
          open: true,
          severity: "error",
          message: `Error editing customer\n${errorMessages}`,
        });
      },
      onFinish: () => {
        setLoading(false);
      },
    });
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  useEffect(() => {
    if (customer) {
      setLoading(false);
    }
  }, [customer, bookings]);

  return (
    <AuthenticatedLayout user={ auth.user } header={ "Customers" }>
      <Head title="Edit Customer"/>
      <Toolbar sx={ { mt: 8, mb: 4 } }>
        <Button variant="outlined" color="secondary" onClick={ handleBack }>
          Back
        </Button>
      </Toolbar>
      <Container maxWidth="lg" sx={ { mt: 4, mb: 4 } }>
        <Grid container spacing={ 3 }>
          <Tabs
            value={ selectedTab }
            onChange={ handleTabChange }
            aria-label="customer data and related bookings"
          >
            <Tab label="CUSTOMER DATA"/>
            <Tab label="BOOKING HISTORY"/>
          </Tabs>
          { (hasPermission(Permissions.EditCustomers) && selectedTab === 0) && (<Paper
              sx={ {
                p: 2,
                display: "flex",
                flexDirection: "column",
                minHeight: 240,
                width: "100%",
              } }
            >
              <h1>Edit Customer</h1>
              <form onSubmit={ handleSubmit } encType="multipart/form-data">
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Survivor Number"
                        variant="outlined"
                        value={ data.survivor_number }
                        disabled
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Email"
                        variant="outlined"
                        value={ data.email }
                        name={ "email" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="First Name"
                        variant="outlined"
                        value={ data.first_name }
                        name={ "first_name" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Middle Name"
                        variant="outlined"
                        name={ "middle_name" }
                        value={ data.middle_name }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Last Name"
                        variant="outlined"
                        value={ data.last_name }
                        name={ "last_name" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <Select
                        fullWidth
                        label="Gender"
                        variant="outlined"
                        value={ data.gender }
                        name={ "gender" }
                        onChange={ handleChange }
                      >
                        <MenuItem value={ "M" }>Male</MenuItem>
                        <MenuItem value={ "F" }>Female</MenuItem>
                      </Select>
                    </Grid>
                  </Grid>
                </Box>

                <Typography variant="h6" sx={ { mt: 2, mb: 2 } }>
                  Date of Birth
                </Typography>
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 2 }>
                      <TextField
                        fullWidth
                        label="Year"
                        variant="outlined"
                        value={ data.year }
                        name={ "year" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={2}>
                      <FormControl fullWidth variant="outlined">
                        <InputLabel>Month</InputLabel>
                        <Select
                          value={data.month}
                          name="month"
                          onChange={handleChange}
                          label="Month"
                        >
                          {[
                            { label: "January", value: "01" },
                            { label: "February", value: "02" },
                            { label: "March", value: "03" },
                            { label: "April", value: "04" },
                            { label: "May", value: "05" },
                            { label: "June", value: "06" },
                            { label: "July", value: "07" },
                            { label: "August", value: "08" },
                            { label: "September", value: "09" },
                            { label: "October", value: "10" },
                            { label: "November", value: "11" },
                            { label: "December", value: "12" },
                          ].map((month) => (
                            <MenuItem key={month.value} value={month.value}>
                              {month.label}
                            </MenuItem>
                          ))}
                        </Select>
                      </FormControl>
                    </Grid>
                    <Grid item xs={ 2 }>
                      <TextField
                        fullWidth
                        label="Day"
                        variant="outlined"
                        value={ data.day }
                        name={ "day" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <Country
                        fullWidth
                        label="Country"
                        variant="outlined"
                        value={ data.citizenship }
                        name={ "citizenship" }
                        onChange={ (e) => handleStringChange(e, 'citizenship') }
                      />
                    </Grid>
                  </Grid>
                </Box>

                <Typography variant="h6" sx={ { mt: 2, mb: 2 } }>
                  Phone Number
                </Typography>
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <PhoneNumber
                        value={ data.phone || "" }
                        forceDialCode={ true }
                        name={ "phone" }
                        onChange={ (e) => handleStringChange(e, 'phone') }
                      />
                    </Grid>
                  </Grid>
                </Box>

                <Typography variant="h6" sx={ { mt: 2, mb: 2 } }>
                  Address Information
                </Typography>
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Address Line 1"
                        variant="outlined"
                        value={ data.address_first }
                        name={ "address_first" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Address Line 2"
                        variant="outlined"
                        value={ data.address_second }
                        name={ "address_second" }
                        onChange={ handleChange }
                      />
                    </Grid>

                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="City"
                        variant="outlined"
                        value={ data.city }
                        name={ "city" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="State"
                        variant="outlined"
                        value={ data.state }
                        name={ "state" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="Zip Code"
                        variant="outlined"
                        value={ data.postal_code }
                        name={ "postal_code" }
                        onChange={ handleChange }
                      />
                    </Grid>

                    <Grid item xs={ 12 }>
                      <Country
                        fullWidth
                        label="Country"
                        variant="outlined"
                        value={ data.country }
                        name={ "country" }
                        onChange={ (e) => handleStringChange(e, 'country') }
                      />
                    </Grid>
                  </Grid>
                </Box>

                <Typography variant="h6" sx={ { mt: 2, mb: 2 } }>
                  Emergency Contact
                </Typography>
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Contact Full Name"
                        variant="outlined"
                        value={ data.emergency_c_name }
                        name={ "emergency_c_name" }
                        onChange={ handleChange }
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <PhoneNumber
                        value={ data.emergency_c_phone || "" }
                        forceDialCode={ true }
                        name={ "emergency_c_phone" }
                        onChange={ (e) => handleStringChange(e, 'emergency_c_phone') }
                      />
                    </Grid>
                  </Grid>
                </Box>

                <Typography variant="h6" sx={ { mt: 2, mb: 2 } }>
                  Preferred Language
                </Typography>
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 }>
                      <Select
                        fullWidth
                        label="Preferred Language"
                        variant="outlined"
                        value={ data.language }
                        name={ "language" }
                        onChange={ handleChange }
                      >
                        <MenuItem value={ "de" }>Deutsch</MenuItem>
                        <MenuItem value={ "en" }>English</MenuItem>
                        <MenuItem value={ "es" }>Español</MenuItem>
                      </Select>
                    </Grid>
                  </Grid>
                </Box>
                <Box sx={ { mt: 4 } }>
                  <Button
                    variant="contained"
                    color="primary"
                    fullWidth
                    type="submit"
                    disabled={ processing }
                  >
                    { processing ? "Submitting..." : "Submit" }
                  </Button>
                </Box>
              </form>
            </Paper>
          ) }

          { (hasPermission(Permissions.ViewBookings) && selectedTab === 1) && (
            <BookingHistory auth={ auth } bookings={ bookings }/>
          ) }
          <SnackbarAlert
            open={ snackbar.open }
            severity={ snackbar.severity }
            message={ snackbar.message }
            onClose={ handleCloseSnackbar }
            horizontal={ "center" }
            vertical={ "top" }
          />
        </Grid>
        <LoadingOverlay open={loading} />
      </Container>
    </AuthenticatedLayout>
  );
};

export default Edit;
