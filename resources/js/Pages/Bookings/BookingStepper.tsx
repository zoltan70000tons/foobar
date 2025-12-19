import React, { useEffect, useRef, useState } from "react";
import {
  Box,
  Button,
  Stepper,
  Step,
  StepLabel,
  Typography,
  TextField,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Autocomplete,
  FormControlLabel,
  Switch,
  CircularProgress,
  Grid,
  Checkbox,
  TableContainer,
  Table,
  TableCell,
  TableRow,
  TableBody,
  Paper,
  Tab,
  Tabs,
  ToggleButton,
  Tooltip,
  Chip,
  Alert,
} from "@mui/material";

import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { LocationEnum } from "@/enums/LocationEnum";
import { router } from "@inertiajs/react";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import LoadingOverlay from "@/Components/LoadingOverlay";
import FilterListIcon from "@mui/icons-material/FilterList";
import ClearIcon from "@mui/icons-material/Clear";
import { LoadingButton } from "@mui/lab";
import SpecialRequest from "@/Pages/Bookings/partials/SpecialRequest";
import { CabinType, CabinTypeIds } from "@/enums/CabinType";
import { CabinType as CabinTypeType} from "@/types/cabin";
import { formatCurrency } from "@/Helpers/stringUtils";
import { Customer } from "@/interfaces/Customer";
import { BedConfigOption, bedConfigOptions } from "@/types/bed-config";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { useAvailableCabins } from "@/Hooks/booking/useAvailableCabins";
import { useBookingSteps } from "@/Hooks/booking/useBookingSteps";
import { usePaymentPlans } from "@/Hooks/booking/usePaymentPlans";
import CabinSelector from "@/Pages/Bookings/partials/CabinSelector";
//import { toggleAddon } from "@/store/slices/bookingSlice";

const TabPanel = ({ children, value, index }) => {
  return (
    <div role="tabpanel" hidden={value !== index}>
      {value === index && <Box sx={{ p: 2 }}>{children}</Box>}
    </div>
  );
};

type PriceCalc = {
  extras: number;
  save: string;
  total: number;
  totalPassenger: number;
};

type BookingStepperProps = {
  cabinTypes: CabinTypeType[];
  cabinCategories: any[];
  close: () => void;
  setIsCreateCustomerVisible: (visible: boolean) => void;
  onBookingCreated: () => void;
  createdCustomer: Customer | null;
};

const BookingStepper: React.FC<BookingStepperProps> = ({
  cabinTypes,
  cabinCategories,
  close,
  setIsCreateCustomerVisible,
  onBookingCreated,
  createdCustomer,
}) => {
  const dispatch = useAppDispatch();
  const step = useAppSelector((s) => s.booking.step);

  const { tabValue, handleTabChange } = useBookingSteps();
  const {
    setCabin,
    setPassengerField,
    toggleAddon,
    resetBooking,
  } = useBookingActions();

  const { cabins, loading: cabinsLoading } = useAvailableCabins();
  const { plans, loading: plansLoading } = usePaymentPlans(cabinCategory?.id);

  const addons = useAppSelector((s) => s.booking.addons);
  const passenger = useAppSelector((s) => s.booking.passenger);




  const [activeStep, setActiveStep] = useState(0);
  const initialPassenger = {
    id: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    dob: "",
    gender: "",
    citizenship: "",
    survivor_number: "",
    email: "",
    phone: "",
    address_first: "",
    address_second: "",
    city: "",
    state: "",
    postal_code: "",
    country: "",
    emergency_c_name: "",
    emergency_c_phone: "",
    payment_method: "",
    special_request: "",
    lead_passenger: true,
    travel_info: false,
    terms_n_cons: true,
    single_t_agreement: false,
    newsletter: false,
    passenger_allocated_cost: "",
    passenger_balance: "",
  };
  //const [passenger, setPassenger] = useState(initialPassenger);
  const [cabinType, setCabinType] = useState(null);
  const [cabinCategory, setCabinCategory] = useState(null);
  const [filteredCategories, setFilteredCategories] = useState([]);
  const [availableCabins, setAvailableCabins] = useState([]);
  const [cabinNumber, setCabinNumber] = useState(null);
  const [advancedFilters, setAdvancedFilters] = useState(false);
  const [selectedDeck, setSelectedDeck] = useState(null);
  const [selectedLocation, setSelectedLocation] = useState("");
  const [onlyAccessible, setOnlyAccessible] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [suggestions, setSuggestions] = useState([]);
  const [selectedUser, setSelectedUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [paymentPlan, setPaymentPlan] = useState(null);
  const [numberOfInstallments, setNumberOfInstallments] = useState(null);
  const [bedConfig, setBedConfig] = React.useState<BedConfigOption | null>(null);
  const [isNextDisabled, setIsNextDisabled] = useState(true);
  //const [carbonOffset, setCarbonOffset] = useState(false);
  //const [youChooseYourCabin, setYouChooseYourCabin] = useState(false);
  const [isSingleRoom, setIsSingleRoom] = useState(false);
  const [fetching, setIsFetching] = useState(false);
  const [availableDecks, setAvailableDecks] = useState([]);
  

  const paymentPlanOptions = [
    {
      id: "INSTALLMENTS",
      value: "INSTALLMENTS",
    },
    {
      id: "PAY_IN_FULL",
      value: "PAY_IN_FULL",
    },
  ];
  const [tabValue, setTabValue] = useState(0);
  const [createLoader, setCreateLoader] = useState(false);
  const eventId = cabinCategory?.event_id;
  const cabinTypeRef = useRef(null);
  const [loadingFinalPrice, setLoadingFinalPrice] = useState<boolean>(true);
  const [priceCalc, setPriceCalc] = useState<PriceCalc | null>(null);

  const validateGenders = (silent = false): boolean => {
    if (!cabinType || !selectedUser) return true;

    const { id } = cabinType;
    const { gender } = selectedUser;

    const invalid =
      (id === CabinTypeIds[CabinType.SINGLE_TICKET_MALE] && gender === "F") ||
      (id === CabinTypeIds[CabinType.SINGLE_TICKET_FEMALE] && gender === "M");

    if (invalid) {
      if (!silent) {
        console.warn("This cabin is gender-restricted and cannot be assigned to this customer.");
        showSnackbar("This cabin is gender-restricted and cannot be assigned to this customer.", "error");
      }
      return false;
    }

    return true;
  };

  const resetState = () => {
    setActiveStep(0);
    setPassenger({ ...initialPassenger });
    setCabinType(null);
    setCabinCategory(null);
    setFilteredCategories([]);
    setAvailableCabins([]);
    setCabinNumber(null);
    setAdvancedFilters(false);
    setSelectedDeck(null);
    setSelectedLocation("");
    setOnlyAccessible(false);
    setSearchQuery("");
    setSuggestions([]);
    setSelectedUser(null);
    setLoading(false);
    setPaymentPlan(null);
    setNumberOfInstallments(null);
    setIsNextDisabled(true);
    setCarbonOffset(false);
    setYouChooseYourCabin(false);
    setIsSingleRoom(false);
    setAvailableDecks([]);
    setTabValue(0);
    setCreateLoader(false);
    setLoadingFinalPrice(true);
    setPriceCalc(null);
  };

  const handleClose = () => {
    resetState();
    close();
  };

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  useEffect(() => {
    return () => {
      resetState();
    };
  }, []);

  useEffect(() => {
    if (cabinTypeRef.current) {
      cabinTypeRef.current.focus();
    }
  }, []);

  useEffect(() => {
    setIsCreateCustomerVisible(activeStep === 1);
  }, [activeStep]);

  useEffect(() => {
    setIsNextDisabled(!validateStep());
  }, [activeStep, cabinType, cabinCategory, cabinNumber, passenger, paymentPlan, numberOfInstallments, bedConfig]);

  useEffect(() => {
  if (createdCustomer) {
    fillPassengerFromUser(createdCustomer);
    showSnackbar("New customer created and selected.", "success");
  }
}, [createdCustomer]);

  useEffect(() => {
    if (!cabinType) return;
    const filteredCategories = cabinCategories.filter((category) =>
      category.cabins.some((cabin) => {
        const matchesType = cabin.cabin_type?.id === cabinType.id;
        const status = cabin.status;

        const isValidStatus =
          status === "AVAILABLE" || status === "RESERVED" || (cabinType.id !== 1 && status === "PARTIALLY_BOOKED");

        return matchesType && isValidStatus;
      }),
    );

    setFilteredCategories(filteredCategories);
  }, [cabinType, cabinCategories]);

  const { showSnackbar } = useSnackbar();

  const steps = ["Select Cabin", "Passenger Details", "Special Request", "Discounts/Addons", "Confirm & Submit"];

  const onChange = (field, value) => {
    setPassenger((prev) => ({ ...prev, [field]: value }));
  };

  const validateStep = () => {
    switch (activeStep) {
      case 0:
        let rule = cabinType && cabinCategory && cabinNumber && paymentPlan && cabinNumber && !fetching && bedConfig;
        if (paymentPlan?.value === "INSTALLMENTS") {
          rule = rule && numberOfInstallments;
        }
        return !!rule;
      case 1:
        return (
          !!(
            passenger.first_name &&
            passenger.last_name &&
            passenger.address_first &&
            passenger.city &&
            passenger.country &&
            passenger.email &&
            passenger.dob &&
            passenger.gender &&
            passenger.payment_method &&
            passenger.terms_n_cons &&
            (isSingleRoom ? passenger.single_t_agreement : true)
          ) && validateGenders(true)
        );
      case 2:
        return true;
      case 3:
        return true;
      case 4:
        return true;
      default:
        return false;
    }
  };

  const fillPassengerFromUser = (user) => {
    if (!user) return;
    const valid = validateGenders(); 
    if (!valid) return;

    setSelectedUser(user);
    setSearchQuery(`${user.first_name} ${user.last_name}`);

    setPassenger((prev) => ({
      ...prev,
      id: user.id,
      first_name: user.first_name,
      middle_name: user.middle_name || "",
      last_name: user.last_name,
      dob: user.dob || "",
      gender: user.gender || "",
      citizenship: user.citizenship || "",
      survivor_number: user.survivor_number || "",
      email: user.email,
      phone: user.phone || "",
      address_first: user.address_first || "",
      address_second: user.address_second || "",
      city: user.city || "",
      state: user.state || "",
      postal_code: user.postal_code || "",
      country: user.country || "",
      emergency_c_name: user.emergency_c_name || "",
      emergency_c_phone: user.emergency_c_phone || "",
      payment_method: paymentPlan?.id === "INSTALLMENTS" ? "CREDIT_CARD" : user.payment_method || "",
      special_request: user.special_request || "",
      lead_passenger: user.lead_passenger ?? true,
      travel_info: user.travel_info ?? false,
      terms_n_cons: true,
      single_t_agreement: isSingleRoom ? true : user.single_t_agreement || false,
      newsletter: user.newsletter || false,
      passenger_allocated_cost: user.passenger_allocated_cost || "",
      passenger_balance: user.passenger_balance || "",
    }));

    setIsNextDisabled(!validateStep());
  };

  const handlePrefill = () => {
    if (!selectedUser) return;
    if (selectedUser.has_booking) {
      showSnackbar("User already has a booking for the same event!", "error");
      return;
    }
    fillPassengerFromUser(selectedUser);
  };

  // Handlers for navigation
  const handleNext = () => setActiveStep((prev) => prev + 1);
  const handleBack = () => setActiveStep((prev) => prev - 1);

  useEffect(() => {
    fetchAvailableCabins();
  }, [cabinType, cabinCategory, selectedDeck, selectedLocation, onlyAccessible, advancedFilters]);

  useEffect(() => {
    if (searchQuery.length < 3) {
      setSuggestions([]);
      return;
    }
    const fetchSuggestions = async () => {
      setLoading(true);
      try {
        const response = await axios.get("/passengers/search", { params: { query: searchQuery, eventId } });
        setSuggestions(response.data);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        setSuggestions([]);
      } finally {
        setLoading(false);
      }
    };

    fetchSuggestions();
  }, [searchQuery]);

  const handleSubmit = () => {
    const { capacity, cabin_category_spec_id, id: cabinCategoryId } = cabinCategory;

    const payload = {
      cabin_number: cabinNumber,
      cabin_capacity: capacity,
      cabin_category_id: cabinCategoryId,
      cabin_category_spec_id: cabin_category_spec_id,
      payment_plan: paymentPlan.value,
      number_of_installments: numberOfInstallments?.value,
      bed_configuration: bedConfig?.value,
      carbon_offset: carbonOffset,
      you_choose_your_cabin: youChooseYourCabin,
      passenger: {
        id: passenger.id,
        first_name: passenger.first_name,
        middle_name: passenger.middle_name,
        last_name: passenger.last_name,
        dob: passenger.dob,
        gender: passenger.gender,
        citizenship: passenger.citizenship,
        survivor_number: passenger.survivor_number,
        email: passenger.email,
        phone: passenger.phone,
        address_first: passenger.address_first,
        address_second: passenger.address_second,
        city: passenger.city,
        state: passenger.state,
        postal_code: passenger.postal_code,
        country: passenger.country,
        emergency_c_name: passenger.emergency_c_name,
        emergency_c_phone: passenger.emergency_c_phone,
        payment_method: passenger.payment_method,
        special_request: passenger.special_request,
        special_options: passenger.special_options,
        dietary_preferences: passenger.dietary_preferences,
        lead_passenger: passenger.lead_passenger,
        travel_info: passenger.travel_info,
        terms_n_cons: true,
        single_t_agreement: passenger.single_t_agreement,
        newsletter: passenger.newsletter,
        passenger_allocated_cost: passenger.passenger_allocated_cost,
        passenger_balance: passenger.passenger_balance,
      },
    };

    if (!payload.cabin_number || !payload.passenger.first_name || !payload.passenger.email) {
      showSnackbar("Please fill all required fields!", "error");
      return;
    }

    setCreateLoader(true);

    router.post(route("bookings.createManual", { id: 1 }), payload, {
      onSuccess: () => {
        showSnackbar("Booking created successfully!", "success");
        setActiveStep(0);
        if (onBookingCreated) onBookingCreated();
        handleClose();
      },
      onError: (errors) => {
        console.error("Error creating booking:", errors);

        // Extract meaningful error messages
        const errorMessages = Object.values(errors)
          .flat()
          .filter((msg) => msg?.trim()); // Remove empty values
        const errorMessage = errorMessages.length ? errorMessages[0] : "";

        // Conditionally add line breaks only if there's a meaningful error
        const message = errorMessage
          ? `Failed to create booking. Please try again.\n\n${errorMessage}`
          : "Failed to create booking. Please try again.";

        showSnackbar(message, "error");
      },
      onFinish: () => {
        setCreateLoader(false);
      },
    });
  };

  const fetchAvailableCabins = async () => {
    if (!cabinType || !cabinCategory) {
      return;
    }
    try {
      setIsFetching(true);
      const response = await axios.get(route("cabins.available"), {
        params: {
          type_id: cabinType?.id,
          category_id: cabinCategory?.id,
          deck: selectedDeck,
          location: selectedLocation,
          accessible: onlyAccessible,
        },
      });
      setIsFetching(false);
      if (response?.data?.error) {
        showSnackbar(response.data.error, "error");
      }
      if (response?.data?.cabins.length === 0) {
        showSnackbar("No available cabins match your selection.", "error");
      }
      const decks = Array.isArray(response?.data?.cabins)
        ? [...new Set(response.data.cabins.map((cabin) => cabin.deck))].map(Number).sort((a, b) => a - b)
        : [];

      setCabinNumber(null);
      setAvailableCabins(response?.data?.cabins || []);
      setAvailableDecks(decks);
    } catch (error) {
      showSnackbar(error.response.data.error, "error");
      console.error("Error fetching available cabins:", error);
      setAvailableCabins([]);
      setIsFetching(false);

      setCabinCategory(null);
    }
  };

  useEffect(() => {
    if (activeStep === 4) {
      getBookingFinalPrice();
    }
  }, [activeStep]);

  const getBookingFinalPrice = async () => {
    if (!cabinType || !cabinCategory) {
      return;
    }
    const { capacity, cabin_category_spec_id, id: cabinCategoryId } = cabinCategory;

    const payload = {
      cabin_number: cabinNumber,
      cabin_capacity: capacity,
      cabin_category_id: cabinCategoryId,
      cabin_type_id: cabinType.id,
      cabin_category_spec_id: cabin_category_spec_id,
      payment_plan: paymentPlan.value,
      number_of_installments: numberOfInstallments?.value,
      carbon_offset: carbonOffset,
      you_choose_your_cabin: youChooseYourCabin,
      passenger: {
        id: passenger.id,
        gender: passenger.gender,
        survivor_number: passenger.survivor_number,
        payment_method: passenger.payment_method,
      },
    };
    try {
      setLoadingFinalPrice(true);
      const response = await axios.get(route("bookings.getBookingFinalCost", { event: eventId }), {
        params: payload,
      });
      if (response?.data?.error) {
        showSnackbar(response.data.error, "error");
      }

      if (response?.data?.priceCalc) {
        setPriceCalc(response?.data?.priceCalc);
      }
    } catch (error) {
      //showSnackbar(error.response.data.error, "error");
      console.error("Couldn't get booking final price:", error);
    } finally {
      setLoadingFinalPrice(false);
    }
  };

  return (
    <Box sx={{ width: "100%", margin: "0 auto", mt: 4 }}>
      <Stepper activeStep={activeStep}>
        {steps.map((label, index) => (
          <Step key={index}>
            <StepLabel>{label}</StepLabel>
          </Step>
        ))}
      </Stepper>

      <Box>
        {activeStep === 0 && (
          <Box>
            <Grid container spacing={2}>
              <Grid item xs={12} md={3}>
                <FormControl fullWidth sx={{ mt: 2 }}>
                  <Autocomplete
                    fullWidth
                    options={cabinTypes}
                    getOptionLabel={(option) => option.cabin_type}
                    value={cabinType}
                    onChange={(event, newValue) => setCabinType(newValue)}
                    renderInput={(params) => <TextField {...params} label="Cabin Type" inputRef={cabinTypeRef} />}
                    sx={{ mb: 2 }}
                  />
                </FormControl>
              </Grid>

              <Grid item xs={12} md={6}>
                <FormControl fullWidth sx={{ mt: 2 }}>
                  <Autocomplete
                    fullWidth
                    options={filteredCategories}
                    getOptionLabel={(option) =>
                      `${option.title} - ${option.capacity_description} - ${formatCurrency(option.price)}`
                    }
                    value={cabinCategory}
                    onChange={(event, newValue) => {
                      setCabinCategory(newValue);
                      setAvailableCabins([]);
                    }}
                    renderInput={(params) => <TextField {...params} label="Cabin Category" disabled={!cabinType} />}
                    sx={{ mb: 2 }}
                    loading={fetching}
                    loadingText="Loading categories..."
                    disabled={!cabinType}
                  />
                </FormControl>
              </Grid>

              <Grid item xs={12} md={3} sx={{ mt: 2 }}>
                <FormControl fullWidth>
                  <ToggleButton
                    value="advancedFilters"
                    selected={advancedFilters}
                    onChange={() => {
                      const next = !advancedFilters;
                      setAdvancedFilters(next);
                      if (next) {
                      } else {
                        setSelectedDeck(null);
                        setAvailableCabins([]);
                        setSelectedLocation(null);
                        setAvailableDecks([]);
                        setOnlyAccessible(false);
                      }
                    }}
                    disabled={loading}
                    sx={{
                      color: advancedFilters ? "error.main" : "inherit",
                      borderColor: advancedFilters ? "error.main" : "default",
                    }}
                  >
                    {advancedFilters ? <ClearIcon /> : <FilterListIcon />}
                    {advancedFilters ? "Clear Filters" : "Advanced Filters"}
                  </ToggleButton>
                </FormControl>
              </Grid>
              {advancedFilters && (
                <>
                  <Grid item xs={12} md={3}>
                    <FormControl fullWidth>
                      <Autocomplete
                        fullWidth
                        options={availableDecks}
                        getOptionLabel={(option) => (option ? `Deck ${option}` : "")}
                        value={selectedDeck}
                        onChange={(event, newValue) => setSelectedDeck(newValue)}
                        renderInput={(params) => (
                          <TextField {...params} label="Cabin Deck" disabled={!cabinType || !cabinCategory} />
                        )}
                        sx={{ mb: 2 }}
                        disabled={!cabinType || !cabinCategory}
                      />
                    </FormControl>
                  </Grid>
                  <Grid item xs={12} md={3}>
                    <FormControl fullWidth>
                      <InputLabel id="location-label">Location</InputLabel>
                      <Select
                        labelId="location-label"
                        value={selectedLocation}
                        onChange={(e) => setSelectedLocation(e.target.value)}
                        label="Location"
                        disabled={!cabinType || !cabinCategory}
                      >
                        <MenuItem value="">
                          <em>None</em>
                        </MenuItem>
                        {Object.values(LocationEnum).map((location) => (
                          <MenuItem key={location} value={location}>
                            {location}
                          </MenuItem>
                        ))}
                      </Select>
                    </FormControl>
                  </Grid>
                  <Grid item xs={12} md={3}>
                    <FormControlLabel
                      control={
                        <Switch
                          checked={onlyAccessible}
                          onChange={(e) => setOnlyAccessible(e.target.checked)}
                          disabled={!cabinType || !cabinCategory}
                        />
                      }
                      label="Only Accessible"
                    />
                  </Grid>
                  <Grid item xs={12} md={3}></Grid>
                </>
              )}

              <Grid item xs={12} md={3}>
                <FormControl fullWidth>
                  <CabinSelector />
                </FormControl>
              </Grid>

              {/*Payment Plan */}
              <Grid item xs={12} md={3}>
                <FormControl fullWidth>
                  <Autocomplete
                    fullWidth
                    options={paymentPlanOptions}
                    getOptionLabel={(option) => option.value}
                    value={paymentPlan}
                    onChange={(event, newValue) => setPaymentPlan(newValue)}
                    renderInput={(params) => <TextField {...params} label="Payment Plan" />}
                    sx={{ mb: 2 }}
                  />
                </FormControl>
              </Grid>

              {/* Number of Installments */}
              {paymentPlan?.value === "INSTALLMENTS" && (
                <Grid item xs={12} md={3}>
                  <FormControl fullWidth>
                    <Autocomplete
                      fullWidth
                      options={[
                        { id: 2, value: 2 },
                        { id: 3, value: 3 },
                        { id: 4, value: 4 },
                        { id: 5, value: 5 },
                      ]}
                      getOptionLabel={(option) => `${option.value}`}
                      value={numberOfInstallments}
                      onChange={(event, newValue) => setNumberOfInstallments(newValue)}
                      renderInput={(params) => <TextField {...params} label="Number of Installments" />}
                      sx={{ mb: 2 }}
                    />
                  </FormControl>
                </Grid>
              )}
              <Grid item xs={12} md={3}>
                 <FormControl fullWidth>
                  <Autocomplete
                      fullWidth
                      options={bedConfigOptions}
                      getOptionLabel={(option) => `${option.value}`}
                      value={bedConfig}
                      onChange={(event, newValue) => setBedConfig(newValue)}
                      renderInput={(params) => <TextField {...params} label="Bed Configuration" />}
                      sx={{ mb: 2 }}
                    />

                 </FormControl>
              </Grid>
            </Grid>
          </Box>
        )}

        {activeStep === 1 && (
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
                  onChange={(e) => onChange("middle_name", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Last Name"
                  variant="outlined"
                  fullWidth
                  value={passenger?.last_name || ""}
                  onChange={(e) => onChange("last_name", e.target.value)}
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
                  onChange={(e) => onChange("dob", e.target.value)}
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
                    onChange={(e) => onChange("gender", e.target.value)}
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
                  onChange={(e) => onChange("citizenship", e)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Survivor Number"
                  variant="outlined"
                  fullWidth
                  value={passenger?.survivor_number || ""}
                  onChange={(e) => onChange("survivor_number", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Email"
                  variant="outlined"
                  fullWidth
                  value={passenger?.email || ""}
                  onChange={(e) => onChange("email", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <PhoneNumber
                  value={passenger?.phone || ""}
                  forceDialCode={true}
                  name={"phone"}
                  onChange={(e) => onChange("phone", e)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Address Line 1"
                  variant="outlined"
                  fullWidth
                  value={passenger?.address_first || ""}
                  onChange={(e) => onChange("address_first", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Address Line 2"
                  variant="outlined"
                  fullWidth
                  value={passenger?.address_second || ""}
                  onChange={(e) => onChange("address_second", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="City"
                  variant="outlined"
                  fullWidth
                  value={passenger?.city || ""}
                  onChange={(e) => onChange("city", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="State"
                  variant="outlined"
                  fullWidth
                  value={passenger?.state || ""}
                  onChange={(e) => onChange("state", e.target.value)}
                  disabled
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField
                  label="Postal Code"
                  variant="outlined"
                  fullWidth
                  value={passenger?.postal_code || ""}
                  onChange={(e) => onChange("postal_code", e.target.value)}
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
                  onChange={(e) => onChange("country", e)}
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
                  onChange={(e) => onChange("emergency_c_name", e.target.value)}
                />
              </Grid>
              <Grid item xs={12} md={6}>
                <PhoneNumber
                  label="Emergency Contact Phone"
                  value={passenger?.emergency_c_phone || ""}
                  forceDialCode={true}
                  name={"emergency_c_phone"}
                  onChange={(e) => onChange("emergency_c_phone", e)}
                />
              </Grid>
              <Grid item xs={12} md={3}>
                <FormControl fullWidth>
                  <InputLabel>Payment Method</InputLabel>
                  <Select
                    label="Payment Method"
                    value={paymentPlan?.id === "INSTALLMENTS" ? "CREDIT_CARD" : passenger?.payment_method || ""}
                    onChange={(e) => onChange("payment_method", e.target.value)}
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
                      onChange={(e) => onChange("newsletter", e.target.checked)}
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
                      onChange={(e) => onChange("travel_info", e.target.checked)}
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
        )}
        {activeStep === 2 && (
          <Box sx={{ mt: 4 }}>
            <Grid item xs={12}>
              <SpecialRequest disabledByDesign={false} onChange={onChange} passenger={passenger} />
            </Grid>
          </Box>
        )}

        {activeStep === 3 && (
          <>
            <Box>
              <Typography variant="body1" sx={{ mt: 2 }}>
                Carbon Offset
              </Typography>
              <Grid item xs={12} md={3}>
                <FormControlLabel
                  control={
                    <Checkbox
                      size="small"
                      checked={addons.carbonOffset}
                      onChange={() => toggleAddon("carbonOffset")}
                    />
                  }
                  label="Carbon Offset"
                />
              </Grid>
            </Box>
            <Box>
              <Typography variant="body1" sx={{ mt: 2 }}>
                You Choose Your Cabin
              </Typography>
              <Grid item xs={12} md={3}>
                <FormControlLabel
                  control={
                    <Checkbox
                      size="small"
                      checked={addons.youChooseYourCabin}
                      onChange={() => toggleAddon("youChooseYourCabin")}
                    />
                  }
                  label="You Choose Your Cabin"
                />
              </Grid>
            </Box>
          </>
        )}

        {activeStep === 4 && (
          <Box>
            <Typography variant="h6" gutterBottom>
              Confirm Booking
            </Typography>
            <Tabs
              value={tabValue}
              onChange={handleTabChange}
              indicatorColor="primary"
              textColor="primary"
              sx={{ mb: 2 }}
              aria-label="Booking Details Tabs"
            >
              <Tab label="Cabin Details" />
              <Tab label="Lead Passenger" />
              <Tab label="Payment Info" />
            </Tabs>

            {/* Tab Panel for Cabin Details */}
            {loadingFinalPrice && (
              <CircularProgress
                color="inherit"
                size={40}
                style={{ position: "absolute", inset: "50%", marginLeft: "-20px" }}
              />
            )}
            {!loadingFinalPrice && (
              <TabPanel value={tabValue} index={0}>
                <TableContainer component={Paper} elevation={3}>
                  <Table size="small">
                    <TableBody>
                      <TableRow>
                        <TableCell>
                          <strong>Type:</strong>
                        </TableCell>
                        <TableCell>{cabinType.cabin_type}</TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>
                          <strong>Category:</strong>
                        </TableCell>
                        <TableCell>{cabinCategory.title}</TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>
                          <strong>Cabin Number:</strong>
                        </TableCell>
                        <TableCell>{cabinNumber}</TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>
                          <strong>Capacity:</strong>
                        </TableCell>
                        <TableCell>{cabinCategory.spec.capacity}</TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>
                          <strong>Price Per Person Before Calculations:</strong>
                        </TableCell>
                        <TableCell>{formatCurrency(cabinCategory.price)}</TableCell>
                      </TableRow>
                      {priceCalc?.totalPassenger && (
                        <TableRow>
                          <TableCell>
                            <strong>Price per Person with Tax after Discount and Add-Ons:</strong>
                          </TableCell>
                          <TableCell>{formatCurrency(priceCalc.totalPassenger)}</TableCell>
                        </TableRow>
                      )}
                      {priceCalc?.total && (
                        <TableRow>
                          <TableCell>
                            <strong>Total with Tax After Discounts and Add-Ons:</strong>
                          </TableCell>
                          <TableCell>{formatCurrency(priceCalc.total)}</TableCell>
                        </TableRow>
                      )}
                    </TableBody>
                  </Table>
                </TableContainer>
              </TabPanel>
            )}

            {/* Tab Panel for Lead Passenger */}
            <TabPanel value={tabValue} index={1}>
              <TableContainer component={Paper} elevation={3}>
                <Table size="small">
                  <TableBody>
                    <TableRow>
                      <TableCell>
                        <strong>Name:</strong>
                      </TableCell>
                      <TableCell>
                        {passenger.first_name} {passenger.last_name}
                      </TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Email:</strong>
                      </TableCell>
                      <TableCell>{passenger.email}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Phone:</strong>
                      </TableCell>
                      <TableCell>{passenger.phone}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Gender:</strong>
                      </TableCell>
                      <TableCell>{passenger.gender}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Address 1:</strong>
                      </TableCell>
                      <TableCell>{passenger.address_first}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>City:</strong>
                      </TableCell>
                      <TableCell>{passenger.city}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Country:</strong>
                      </TableCell>
                      <TableCell>{passenger.country}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </TableContainer>
            </TabPanel>
            <TabPanel value={tabValue} index={2}>
              <TableContainer component={Paper} elevation={3}>
                <Table size="small">
                  <TableBody>
                    <TableRow>
                      <TableCell>
                        <strong>Payment Plan:</strong>
                      </TableCell>
                      <TableCell>{paymentPlan.value}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Payment Method:</strong>
                      </TableCell>
                      <TableCell>{passenger.payment_method}</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>
                        <strong>Number of Installments:</strong>
                      </TableCell>
                      <TableCell>{numberOfInstallments?.value}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </TableContainer>
            </TabPanel>
          </Box>
        )}

        <Box sx={{ display: "flex", justifyContent: "space-between", mt: 4 }}>
          <Button disabled={activeStep === 0} onClick={handleBack} variant="outlined">
            Back
          </Button>
          {activeStep === steps.length - 1 ? (
            <LoadingButton
              onClick={handleSubmit}
              variant="outlined"
              color="success"
              loading={createLoader}
              loadingPosition="start"
            >
              Create Booking
            </LoadingButton>
          ) : (
            <Button onClick={handleNext} variant="contained" color="primary" disabled={isNextDisabled}>
              Next
            </Button>
          )}
        </Box>
      </Box>
      <LoadingOverlay open={createLoader} />
    </Box>
  );
};

export default BookingStepper;
