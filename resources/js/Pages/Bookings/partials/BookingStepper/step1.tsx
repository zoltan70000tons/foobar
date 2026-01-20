import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import React, { useEffect, useState } from "react";
import {
  Alert,
  Autocomplete,
  Box,
  Button,
  Checkbox, Chip, CircularProgress,
  FormControl,
  FormControlLabel,
  Grid,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Tooltip,
  Typography
} from "@mui/material";
import axios from "axios";
import { useAppSelector } from "@/store/hooks";
import { selectBooking } from "@/store/slices/selectors";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { useValidateGenders } from "@/Hooks/booking/useValidateGenders";
import { BookingUser } from "@/interfaces/User";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { Passenger } from "@/interfaces/Passenger";

type SuggestionResponse = {
  data: Passenger[];
}

export const BookingStepperStepOne = () => {
  const { showSnackbar } = useSnackbar();

  const {
    setLoading,
    setPassenger,
    setPassengerField,
    setSelectedUser,
  } = useBookingActions();

  const {
    cabinCategory,
    cabinType,
    paymentPlan,
    isSingleRoom,
    passenger,
    loading,
    createdCustomer,
    selectedUser,
  } = useAppSelector(selectBooking);

  const [suggestions, setSuggestions] = useState([]);
  const [searchQuery, setSearchQuery] = useState("");

  const validateGenders = useValidateGenders({ cabinType, selectedUser });

  const fillPassengerFromUser = (user: BookingUser) => {
    if (!user) return;
    const valid = validateGenders();
    if (!valid) return;

    setSelectedUser(user);
    setSearchQuery(`${user.first_name} ${user.last_name}`);

    setPassenger({
      id: user.id,
      passenger_order: 1,
      booking_id: 0, // assign actual booking ID if available
      full_name: `${user.first_name} ${user.last_name}`,
      email: user.email,
      passenger_balance: user.passenger_balance ?? 0,
      passenger_allocated_cost: user.passenger_allocated_cost ?? 0,

      first_name: user.first_name,
      middle_name: user.middle_name ?? null,
      last_name: user.last_name,
      dob: user.dob ?? null,
      gender: user.gender ?? null,
      citizenship: user.citizenship ?? null,
      survivor_number: user.survivor_number ?? null,
      phone: user.phone ?? null,
      address_first: user.address_first ?? null,
      address_second: user.address_second ?? null,
      city: user.city ?? null,
      state: user.state ?? null,
      postal_code: user.postal_code ?? null,
      country: user.country ?? null,
      emergency_c_name: user.emergency_c_name ?? null,
      emergency_c_phone: user.emergency_c_phone ?? null,
      payment_method: paymentPlan?.id === "INSTALLMENTS" ? "CREDIT_CARD" : user.payment_method ?? null,
      special_request: user.special_request ?? null,
      travel_info: user.travel_info ?? false,
      terms_n_cons: true,
      single_t_agreement: isSingleRoom ? true : user.single_t_agreement ?? false,
      newsletter: user.newsletter ?? false,
      lead_passenger: true,
    } as Passenger);
  };

  useEffect(() => {
    if (createdCustomer) {
      fillPassengerFromUser(createdCustomer);
      showSnackbar("New customer created and selected.", "success");
    }
  }, [createdCustomer]);

  useEffect(() => {
    if (searchQuery.length < 3) {
      setSuggestions([]);
      return;
    }
    const fetchSuggestions = async () => {
      setLoading(true);
      try {
        const response = await axios.get<SuggestionResponse>("/passengers/search", { params: { query: searchQuery, eventId: cabinCategory?.event_id } });

        setSuggestions(response.data);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        setSuggestions([]);
      } finally {
        setLoading(false);
      }
    };

    void fetchSuggestions();
  }, [searchQuery]);

  const handlePrefill = () => {
    if (!selectedUser) return;
    if (selectedUser.has_booking) {
      showSnackbar("User already has a booking for the same event!", "error");
      return;
    }
    fillPassengerFromUser(selectedUser);
  };

  return (
    <Box sx={{ mt: 4 }}>
      <Typography variant="h6">Lead Passenger Details</Typography>
      <Box sx={{ mt: 2, mb: 2 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs>
            <Autocomplete
              options={suggestions}
              getOptionLabel={(option) =>
                `${option.first_name} ${option.last_name} (${option.email ?? "N/A"}) - SN: ${option.survivor_number}`
              }
              loading={loading}
              value={selectedUser}
              inputValue={searchQuery}
              onInputChange={(e, value) => setSearchQuery(value)}
              onChange={(e, value) => setSelectedUser(value)}
              renderInput={(params) => (
                <TextField
                  {...params}
                  label="Search by Email, Name or Survivor Number"
                  variant="outlined"
                  InputProps={{
                    ...params.InputProps,
                    endAdornment: (
                      <>
                        {loading ? <CircularProgress color="inherit" size={20} /> : null}
                        {params.InputProps.endAdornment}
                      </>
                    ),
                  }}
                />
              )}
              renderOption={(props, option) => (
                <li {...props} key={option.email}>
                  <div style={{ display: "flex", alignItems: "center" }}>
                    <span>{`${option.first_name} ${option.last_name} (${option.email ?? "N/A"}) - SN: ${option.survivor_number}`}</span>
                    {option.has_booking && (
                      <Chip label="ALREADY BOOKED" color="error" style={{ marginLeft: "20px" }} />
                    )}
                  </div>
                </li>
              )}
            />
          </Grid>
          <Grid item>
            <Button variant="outlined" color="secondary" onClick={handlePrefill}>
              SELECT CUSTOMER
            </Button>
          </Grid>
        </Grid>
      </Box>

      {passenger?.id && (
        <Alert
          severity="warning"
          sx={{ my: 2 }}
          action={
            <Button color="inherit" size="small" target="_blank" href={`/customers/${passenger?.id}`}>
              Edit Customer
            </Button>
          }
        >
          If you need to update any passenger information, please do so in the Customers admin panel.
        </Alert>
      )}

      <Grid container spacing={2}>
        {/* First Column */}
        <Grid item xs={12} md={3}>
          <TextField
            label="First Name"
            variant="outlined"
            fullWidth
            value={passenger?.first_name || ""}
            onChange={(e) =>
              setPassengerField("first_name", e.target.value)
            }
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Middle Name"
            variant="outlined"
            fullWidth
            value={passenger?.middle_name || ""}
            onChange={(e) => setPassengerField("middle_name", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Last Name"
            variant="outlined"
            fullWidth
            value={passenger?.last_name || ""}
            onChange={(e) => setPassengerField("last_name", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Date of Birth"
            variant="outlined"
            fullWidth
            type="date"
            value={passenger?.dob || ""}
            onChange={(e) => setPassengerField("dob", e.target.value)}
            InputLabelProps={{ shrink: true }}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <FormControl fullWidth>
            <InputLabel>Gender</InputLabel>
            <Select
              label="Gender"
              value={passenger?.gender || ""}
              onChange={(e) => setPassengerField("gender", e.target.value)}
              disabled
            >
              {" "}
              <MenuItem value=""></MenuItem>
              <MenuItem value="M">Male</MenuItem>
              <MenuItem value="F">Female</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12} md={3}>
          <Country
            fullWidth
            label="Citizenship"
            variant="outlined"
            value={passenger?.citizenship || ""}
            name={"citizenship"}
            onChange={(e) => setPassengerField("citizenship", e)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Survivor Number"
            variant="outlined"
            fullWidth
            value={passenger?.survivor_number || ""}
            onChange={(e) => setPassengerField("survivor_number", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Email"
            variant="outlined"
            fullWidth
            value={passenger?.email || ""}
            onChange={(e) => setPassengerField("email", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <PhoneNumber
            value={passenger?.phone || ""}
            forceDialCode={true}
            name={"phone"}
            onChange={(e) => setPassengerField("phone", e)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Address Line 1"
            variant="outlined"
            fullWidth
            value={passenger?.address_first || ""}
            onChange={(e) => setPassengerField("address_first", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Address Line 2"
            variant="outlined"
            fullWidth
            value={passenger?.address_second || ""}
            onChange={(e) => setPassengerField("address_second", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="City"
            variant="outlined"
            fullWidth
            value={passenger?.city || ""}
            onChange={(e) => setPassengerField("city", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="State"
            variant="outlined"
            fullWidth
            value={passenger?.state || ""}
            onChange={(e) => setPassengerField("state", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <TextField
            label="Postal Code"
            variant="outlined"
            fullWidth
            value={passenger?.postal_code || ""}
            onChange={(e) => setPassengerField("postal_code", e.target.value)}
            disabled
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <Country
            fullWidth
            label="Country"
            variant="outlined"
            value={passenger?.country || ""}
            name={"country"}
            onChange={(e) => setPassengerField("country", e)}
            disabled
          />
        </Grid>
      </Grid>

      <Typography variant="h6" sx={{ my: 2 }}>
        Booking Related Information
      </Typography>

      <Grid container spacing={2}>
        <Grid item xs={12} md={6}>
          <TextField
            label="Emergency Contact Name"
            variant="outlined"
            fullWidth
            value={passenger?.emergency_c_name || ""}
            onChange={(e) => setPassengerField("emergency_c_name", e.target.value)}
          />
        </Grid>
        <Grid item xs={12} md={6}>
          <PhoneNumber
            label="Emergency Contact Phone"
            value={passenger?.emergency_c_phone || ""}
            forceDialCode={true}
            name={"emergency_c_phone"}
            onChange={(e) => setPassengerField("emergency_c_phone", e)}
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <FormControl fullWidth>
            <InputLabel>Payment Method</InputLabel>
            <Select
              label="Payment Method"
              value={paymentPlan?.id === "INSTALLMENTS" ? "CREDIT_CARD" : passenger?.payment_method || ""}
              onChange={(e) => setPassengerField("payment_method", e.target.value)}
            >
              <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
              {paymentPlan?.id !== "INSTALLMENTS" && <MenuItem value="BANK_TRANSFER">Bank Transfer</MenuItem>}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12} md={2}>
          <FormControlLabel
            control={
              <Checkbox
                size="small"
                checked={passenger?.newsletter || false}
                onChange={(e) => setPassengerField("newsletter", e.target.checked)}
              />
            }
            label="Newsletter"
          />
        </Grid>
        <Grid item xs={12} md={2}>
          <FormControlLabel
            control={
              <Checkbox
                size="small"
                checked={passenger?.travel_info || false}
                onChange={(e) => setPassengerField("travel_info", e.target.checked)}
              />
            }
            label="Travel Info"
          />
        </Grid>
        {isSingleRoom && (
          <Grid item xs={12} md={2}>
            <Tooltip title="Single Ticket Agreement">
              <FormControlLabel control={<Checkbox size="small" checked={true} />} label="STA" />
            </Tooltip>
          </Grid>
        )}
      </Grid>
    </Box>
  );
}
