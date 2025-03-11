import React, { useState } from "react";
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
  Typography,
  Tooltip,
  IconButton,
  MenuItem,
  Tab,
  Tabs,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { ArrowBack, Delete, Edit } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";
import PhoneNumber from "@/Components/PhoneNumber";
import Country from "@/Components/Country";
import BookingHistory from "@/Pages/Customer/partials/BookingHistory";

const monthNames = {
  "01": "January",
  "02": "February",
  "03": "March",
  "04": "April",
  "05": "May",
  "06": "June",
  "07": "July",
  "08": "August",
  "09": "September",
  "10": "October",
  "11": "November",
  "12": "December",
};

const View = ({ auth, customer, bookings }: PageProps) => {
  const { get, delete: destroy } = useForm();
  const { hasPermission } = usePermissions();

  const [selectedTab, setSelectedTab] = useState(0);
  const [year, month, day] = customer?.detail?.dob.split("-");

  const handleEdit = () => {
    get(route('customers.edit', { customer: customer.id }));
  }

  const handleBack = () => {
    //window.history.back(); //Keeps ordering and filtering, does not reload when data changed on EDIT
    router.get(route("customers.index")); //Loses ordering and filtering, update to date data
  }

  const handleDelete = () => {
    const confirmed = window.confirm('Are you sure you want to delete this customer?');
    if (confirmed) {
      destroy(route('customers.destroy', { customer: customer.id }));
    }
  };

  const handleTabChange = (event: React.ChangeEvent<{}>, newValue: number) => {
    setSelectedTab(newValue);
  };

  return (
    <AuthenticatedLayout user={ auth.user } header={ "Customers" }>
      <Head title="View Customer"/>
      <Toolbar/>

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
          { (hasPermission(Permissions.ViewCustomers) && selectedTab === 0) && (
            <>
              <Paper
                sx={ {
                  p: 2,
                  display: "flex",
                  flexDirection: "column",
                  minHeight: 240,
                  width: "100%",
                } }
              >
                <Box sx={ { width: "100%" } }>
                  <Grid container spacing={ 2 }>
                    <Grid item xs={ 6 } sx={ { mr: 2 } }>
                      <TextField
                        fullWidth
                        label="Survivor Number"
                        variant="outlined"
                        value={ customer.survivor_number.survivor_number }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="First Name"
                        variant="outlined"
                        value={ customer.detail.first_name }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Middle Name"
                        variant="outlined"
                        value={ customer.detail.middle_name }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Last Name"
                        variant="outlined"
                        value={ customer.detail.last_name }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        select
                        disabled
                        fullWidth
                        label="Gender"
                        variant="outlined"
                        value={ customer.detail.gender }
                      >
                        <MenuItem value={ "M" }>Male</MenuItem>
                        <MenuItem value={ "F" }>Female</MenuItem>
                      </TextField>
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
                        value={ year }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 2 }>
                      <TextField
                        fullWidth
                        label="Month"
                        variant="outlined"
                        value={ monthNames[month] || month }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 2 }>
                      <TextField
                        fullWidth
                        label="Day"
                        variant="outlined"
                        value={ day }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <Country
                        fullWidth
                        label="Citizenship"
                        variant="outlined"
                        value={ customer.detail.citizenship }
                        disabled
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
                        value={ customer.detail.phone || "" }
                        forceDialCode={ true }
                        disabled
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
                        value={ customer?.customer_address?.address_first }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <TextField
                        fullWidth
                        label="Address Line 2"
                        variant="outlined"
                        value={ customer?.customer_address?.address_second }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>

                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="City"
                        variant="outlined"
                        value={ customer?.customer_address?.city }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="State"
                        variant="outlined"
                        value={ customer?.customer_address?.state }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 4 }>
                      <TextField
                        fullWidth
                        label="Zip Code"
                        variant="outlined"
                        value={ customer?.customer_address?.postal_code }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>

                    <Grid item xs={ 12 }>
                      <Country
                        fullWidth
                        label="Country"
                        variant="outlined"
                        value={ customer?.customer_address?.country }
                        disabled
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
                        value={ customer?.detail?.emergency_c_name }
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={ 6 }>
                      <PhoneNumber
                        value={ customer?.detail?.emergency_c_phone || "" }
                        forceDialCode={ true }
                        disabled
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
                      <TextField
                        select
                        disabled
                        fullWidth
                        label="Preferred Language"
                        variant="outlined"
                        value={ customer.detail.language }
                      >
                        <MenuItem value={ "de" }>Deutsch</MenuItem>
                        <MenuItem value={ "en" }>English</MenuItem>
                        <MenuItem value={ "es" }>Español</MenuItem>
                      </TextField>
                    </Grid>
                  </Grid>
                </Box>

                <Box sx={ { mt: 4 } }>
                  <div
                    style={ {
                      display: "flex",
                      justifyContent: "flex-end",
                      gap: "8px",
                    } }
                  >
                    <Tooltip title="Back">
                      <IconButton color="primary" onClick={ handleBack }>
                        <ArrowBack/>
                      </IconButton>
                    </Tooltip>
                    { hasPermission(Permissions.EditCustomers) && (<Tooltip title="Edit">
                      <IconButton color="primary" onClick={ handleEdit }>
                        <Edit/>
                      </IconButton>
                    </Tooltip>) }
                    { hasPermission(Permissions.DeleteCustomers) && (<Tooltip title="Delete">
                      <IconButton color="error" onClick={ handleDelete }>
                        <Delete/>
                      </IconButton>
                    </Tooltip>) }
                  </div>
                </Box>
              </Paper>
            </>
          ) }

          { (hasPermission(Permissions.ViewBookings) && selectedTab === 1) && (
            <BookingHistory auth={ auth } bookings={ bookings }/>
          ) }
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
