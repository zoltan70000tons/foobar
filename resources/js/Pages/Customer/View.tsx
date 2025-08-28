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
  Button,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { ArrowBack, Delete, Edit } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";
import PhoneNumber from "@/Components/PhoneNumber";
import Country from "@/Components/Country";
import BookingHistory from "@/Pages/Customer/partials/BookingHistory";
import dayjs from "dayjs";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import CommentIcon from "@mui/icons-material/Comment";
import CustomerSidebar from "@/Pages/Bookings/partials/CustomerSidebar";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { StatusEnum } from "@/enums/StatusEnum";
import Tags from "@/Pages/Bookings/partials/Tags";
import CustomerTags from "@/Pages/Bookings/partials/CustomerTags";
// temporaryPassword
import TemporaryPassword from "@/Pages/Customer/partials/TemporaryPassword";

const View = ({ auth, customer, bookings, availableTags, isTemporaryPassword }: PageProps) => {
  const { get, delete: destroy } = useForm();
  const { hasPermission } = usePermissions();
  const { showSnackbar } = useSnackbar();
  console.log(customer);

  const [selectedTab, setSelectedTab] = useState(0);

  const [isSidebarOpen, setSidebarOpen] = useState(false);
  const toggleSidebar = () => setSidebarOpen(!isSidebarOpen);
  const [comments, setComments] = useState(customer.comments || []);

  const handleEdit = () => {
    get(route('customers.edit', { customer: customer.id }));
  }

  const handleBack = () => {
    //window.history.back(); //Keeps ordering and filtering, does not reload when data changed on EDIT
    router.visit(route("customers.index"), {
      only: ['users'],
    })
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

  const handleAddComment = (comment: string) => {
    router.post(
      route("customers.addComment", {
        user: customer.id,
      }),
      {
        comment: comment,
      },
      {
        onSuccess: (page) => {
          const newComment = page.props.customer.comments.slice(-1)[0];
          setComments((prevComments) => [...prevComments, newComment]);
          showSnackbar("Comment added successfully!", "success");
        },
        onError: (errors) => {
          showSnackbar("Error adding comment:", "error");
          console.error("Error adding comment:", errors);
        },
        preserveScroll: true,
        preserveState: true,
      },
    );
  };

  return (
    <AuthenticatedLayout user={ auth.user } header={ "Customers" }>
      <Head title="View Customer"/>
      <Toolbar 
        sx={{ 
          mt: 8,
          flexDirection: 'row',
          justifyContent: 'space-between',
          gap: 2, 
        }}
      >
        <Box>
          <Button variant="outlined" color="secondary" onClick={ handleBack }>
            Back
          </Button>
          <Button
            variant="outlined"
            color="secondary"
            startIcon={<CommentIcon />}
            onClick={toggleSidebar}
            sx={{ ml: 2 }}
          >
            View Comments & Logs
          </Button>
        </Box>
        <TemporaryPassword 
          customer={customer}
          isTemporaryPassword={isTemporaryPassword}
        />
        
      </Toolbar>

      <Paper variant="outlined" sx={{ p: 2, backgroundColor: '#1c1c1c', mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} md={6}>
            <Grid container mt={2}>
              <CustomerTags customer={customer} availableTags={availableTags} />
            </Grid>
          </Grid>
        </Grid>
      </Paper>

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

                <Box sx={ { width: "100%", mt: 2, mb: 2 } }>
                  <Grid container spacing={ 2 }>
                    <LocalizationProvider dateAdapter={AdapterDayjs}>
                      <Grid item xs={ 6 }>
                        <DatePicker
                          label="Date of Birth"
                          sx={{ width: "100%" }}
                          value={customer.detail.dob ? dayjs(customer.detail.dob) : null}
                          maxDate={dayjs()} // Restricts future dates
                          format="YYYY-MM-DD" // Ensures consistent formatting
                          renderInput={(params) => (
                            <TextField {...params} fullWidth />
                          )}
                          disabled
                        />
                      </Grid>
                    </LocalizationProvider>
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
        <CustomerSidebar
          isOpen={isSidebarOpen}
          toggleSidebar={toggleSidebar}
          logs={customer.logs}
          comments={comments}
          auth={auth}
          onAddComment={handleAddComment}
          customerId={customer.id}
        />
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
