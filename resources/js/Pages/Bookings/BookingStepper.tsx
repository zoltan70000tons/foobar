import React, { useEffect, useState } from "react";
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
} from "@mui/material";

import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { ToggleButton } from "react-aria-components";
import FilterListIcon from "@mui/icons-material/FilterList";
import { LocationEnum } from "@/enums/LocationEnum";
import { DeckEnum } from "@/enums/DeckEnum";

const BookingStepper: React.FC = ({ cabinTypes, cabinCategories }) => {
    const [activeStep, setActiveStep] = useState(0);
    const [cabin, setCabin] = useState("");
    const [passenger, setPassenger] = useState({
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
        confirmed_booking_email: false,
        travel_info: false,
        terms_n_cons: false,
        cabin_conf_accp: false,
        single_t_agreement: false,
        was_on_board: false,
        newsletter: false,
        passenger_allocated_cost: "",
        passenger_balance: "",
    });
    const [cabinType, setCabinType] = useState(null);
    const [cabinCategory, setCabinCategory] = useState(null);
    const [availableCabins, setAvailableCabins] = useState([]);
    const [cabinNumber, setCabinNumber] = useState(null);
    const [advancedFilters, setAdvancedFilters] = useState(false);
    const [selectedDeck, setSelectedDeck] = useState(null);
    const [onlyBalcony, setOnlyBalcony] = useState(false);
    const [selectedLocation, setSelectedLocation] = useState("");
    const [onlyAccessible, setOnlyAccessible] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");
    const [suggestions, setSuggestions] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [loading, setLoading] = useState(false);

    const { showSnackbar } = useSnackbar();

    const steps = ["Select Cabin", "Passenger Details", "Confirm & Submit"];



    const handlePrefill = () => {
        if (!selectedUser) return;
        //inputs
        onChange("first_name", selectedUser.first_name);
        onChange("middle_name", selectedUser.middle_name || "");
        onChange("last_name", selectedUser.last_name);
        onChange("email", selectedUser.email);
        onChange("phone", selectedUser.phone || "");
        onChange("address_first", selectedUser.address_first || "");
        onChange("address_second", selectedUser.address_second || "");
        onChange("city", selectedUser.city || "");
        onChange("state", selectedUser.state || "");
        onChange("postal_code", selectedUser.postal_code || "");
        onChange("country", selectedUser.country || "");
        onChange("emergency_c_name", selectedUser.emergency_c_name || "");
        onChange("emergency_c_phone", selectedUser.emergency_c_phone || "");
        onChange("passenger_allocated_cost", selectedUser.passenger_allocated_cost || "");
        onChange("passenger_balance", selectedUser.passenger_balance || "");
        onChange("survivor_number", selectedUser.survivor_number || "");

        //select
        onChange("gender", selectedUser.gender || "");
        onChange("payment_method", selectedUser.payment_method || "");
        onChange("citizenship", selectedUser.citizenship || "");

        //date
        onChange("dob", selectedUser.dob || "");


        //text area
        onChange("special_request", selectedUser.special_request || "");

        //checkboxs
        onChange("lead_passenger", selectedUser.lead_passenger || false);
        onChange("confirmed_booking_email", selectedUser.confirmed_booking_email || false);
        onChange("travel_info", selectedUser.travel_info || false);
        onChange("terms_n_cons", selectedUser.term_n_cons || false);
        onChange("cabin_conf_accp", selectedUser.cabin_conf_accp || false);
        onChange("single_t_agreement", selectedUser.single_t_agreement || false);
        onChange("was_on_board", selectedUser.was_on_board || false);
        onChange("newsletter", selectedUser.newsletter || false);



    };

    // Handlers for navigation
    const handleNext = () => setActiveStep((prev) => prev + 1);
    const handleBack = () => setActiveStep((prev) => prev - 1);

    console.log(cabinCategories);

    useEffect(() => {
        fetchAvailableCabins();
    }, [cabinType, cabinCategory, selectedDeck, onlyBalcony, selectedLocation, onlyAccessible]);

    useEffect(() => {
        console.log(cabinNumber);
    }, [cabinNumber]);


    useEffect(() => {
        if (searchQuery.length < 3) {
            setSuggestions([]);
            return;
        }
        const fetchSuggestions = async () => {
            setLoading(true);
            try {
                const response = await axios.get("/passengers/search", { params: { query: searchQuery } });
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

    // Handlers for form data
    const handleCabinChange = (event: React.ChangeEvent<{ value: unknown }>) => {
        setCabin(event.target.value as string);
    };

    const handlePassengerChange = (
        event: React.ChangeEvent<HTMLInputElement>
    ) => {
        setPassengerData({
            ...passengerData,
            [event.target.name]: event.target.value,
        });
    };

    const handleSubmit = () => {
        console.log("Booking Confirmed:", { cabin, passengerData });
        alert("Booking Submitted Successfully!");
        setActiveStep(0); // Reset the stepper
    };

    const fetchAvailableCabins = async () => {
        try {
            const response = await axios.get(route("cabins.available"), {
                params: {
                    type_id: cabinType?.id,
                    category_id: cabinCategory?.id,
                    deck: selectedDeck,
                    balcony: onlyBalcony,
                    location: selectedLocation,
                    accessible: onlyAccessible,
                },
            });
            setAvailableCabins(response.data.cabins || []);
            setCabinNumber(null);
        } catch (error) {
            showSnackbar("Error fetching available cabins!", "error");
            console.error("Error fetching available cabins:", error);
            setAvailableCabins([]);
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

            <Box sx={{ mt: 3 }} sx={{ maxWidth: '100%' }}>
                {activeStep === 0 && (
                    <Box>
                        <Typography variant="h6">Select a Cabin Type</Typography>
                        <FormControl fullWidth sx={{ mt: 2 }}>
                            <InputLabel id="cabin-type-label">Cabin Type</InputLabel>
                            <Autocomplete
                                fullWidth
                                options={cabinTypes}
                                getOptionLabel={(option) => option.cabin_type}
                                value={cabinType}
                                onChange={(event, newValue) => setCabinType(newValue)}
                                renderInput={(params) => <TextField {...params} label="Cabin Type" />}
                                sx={{ mb: 2 }}
                            />
                        </FormControl>
                        <FormControl fullWidth sx={{ mt: 2 }}>
                            <InputLabel id="cabin-label">Cabin</InputLabel>
                            <Autocomplete
                                fullWidth
                                options={cabinCategories}
                                getOptionLabel={(option) => option.title}
                                value={cabinCategory}
                                onChange={(event, newValue) => setCabinCategory(newValue)}
                                renderInput={(params) => <TextField {...params} label="Cabin Category" />}
                                sx={{ mb: 2 }}
                            />
                        </FormControl>

                        <Box sx={{ mb: 2 }}>
                            <ToggleButton
                                value="advancedFilters"
                                selected={advancedFilters}
                                onChange={() => setAdvancedFilters(!advancedFilters)}
                            >
                                <FilterListIcon />
                                Advanced Filters
                            </ToggleButton>
                        </Box>
                        {advancedFilters && (
                            <>
                                <Autocomplete
                                    fullWidth
                                    options={Object.values(DeckEnum).filter((value) => typeof value === "number")}
                                    getOptionLabel={(option) => `Deck ${option}`}
                                    value={selectedDeck}
                                    onChange={(event, newValue) => setSelectedDeck(newValue)}
                                    renderInput={(params) => <TextField {...params} label="Cabin Deck" />}
                                    sx={{ mb: 2 }}
                                />
                                <FormControlLabel
                                    control={
                                        <Switch
                                            checked={onlyBalcony}
                                            onChange={(e) => setOnlyBalcony(e.target.checked)}
                                        />
                                    }
                                    label="Only Balcony"
                                />
                                <FormControl fullWidth sx={{ mt: 2 }}>
                                    <InputLabel id="location-label">Location</InputLabel>
                                    <Select
                                        labelId="location-label"
                                        value={selectedLocation}
                                        onChange={(e) => setSelectedLocation(e.target.value)}
                                    >
                                        {Object.values(LocationEnum).map((location) => (
                                            <MenuItem key={location} value={location}>
                                                {location}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                                <FormControlLabel
                                    control={
                                        <Switch
                                            checked={onlyAccessible}
                                            onChange={(e) => setOnlyAccessible(e.target.checked)}
                                        />
                                    }
                                    label="Only Accessible"
                                />
                            </>
                        )}

                        <Box>
                            <Typography variant="body1" sx={{ mb: 1 }}>
                                Select a cabin for this booking.
                            </Typography>
                            <Autocomplete
                                fullWidth
                                options={availableCabins}
                                getOptionLabel={(option) => option.cabin_number}
                                value={availableCabins.find((cabin) => cabin.cabin_number === cabinNumber) || null}
                                onChange={(event, newValue) => setCabinNumber(newValue?.cabin_number || null)}
                                renderInput={(params) => <TextField {...params} label="Available Cabins" />}
                            />
                        </Box>
                    </Box>
                )}

                {activeStep === 1 && (
                    <Box>
                        <Typography variant="h6">Lead Passenger Details</Typography>
                        <Box sx={{mb:2}}>
                            <Grid container spacing={2} alignItems="center">
                                <Grid item xs>
                                    <Autocomplete
                                        size="small"
                                        options={suggestions}
                                        getOptionLabel={(option) => `${option.first_name} ${option.last_name} (${option.email})`}
                                        loading={loading}
                                        value={selectedUser}
                                        inputValue={searchQuery}
                                        onInputChange={(e, value) => setSearchQuery(value)}
                                        onChange={(e, value) => setSelectedUser(value)}
                                        renderInput={(params) => (
                                            <TextField
                                                {...params}
                                                label="Search by Email or Name"
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
                                    />
                                </Grid>
                                <Grid item>
                                    <Button
                                        variant="outlined"
                                        color="secondary"
                                        onClick={handlePrefill}
                                    >
                                        PREFILL
                                    </Button>
                                </Grid>
                            </Grid>
                        </Box>


                        <Grid container spacing={2}>
                            {/* First Column */}
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="First Name"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.first_name || ""}
                                    onChange={(e) => onChange("first_name", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Middle Name"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.middle_name || ""}
                                    onChange={(e) => onChange("middle_name", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Last Name"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.last_name || ""}
                                    onChange={(e) => onChange("last_name", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Date of Birth"
                                    variant="outlined"
                                    fullWidth
                                    type="date"
                                    size="small"
                                    value={passenger?.dob || ""}
                                    onChange={(e) => onChange("dob", e.target.value)}
                                    InputLabelProps={{ shrink: true }}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth size="small" >
                                    <InputLabel>Gender</InputLabel>
                                    <Select
                                        value={passenger?.gender || ""}
                                        onChange={(e) => onChange("gender", e.target.value)}

                                    >
                                        <MenuItem value="M">Male</MenuItem>
                                        <MenuItem value="F">Female</MenuItem>
                                        <MenuItem value="O">Other</MenuItem>
                                    </Select>
                                </FormControl>
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Citizenship"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.citizenship || ""}
                                    onChange={(e) => onChange("citizenship", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Survivor Number"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.survivor_number || ""}
                                    onChange={(e) => onChange("survivor_number", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Email"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.email || ""}
                                    onChange={(e) => onChange("email", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Phone"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.phone || ""}
                                    onChange={(e) => onChange("phone", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Address Line 1"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.address_first || ""}
                                    onChange={(e) => onChange("address_first", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Address Line 2"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.address_second || ""}
                                    onChange={(e) => onChange("address_second", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="City"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.city || ""}
                                    onChange={(e) => onChange("city", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="State"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.state || ""}
                                    onChange={(e) => onChange("state", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Postal Code"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.postal_code || ""}
                                    onChange={(e) => onChange("postal_code", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Country"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.country || ""}
                                    onChange={(e) => onChange("country", e.target.value)}


                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Emergency Contact Name"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.emergency_c_name || ""}
                                    onChange={(e) => onChange("emergency_c_name", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Emergency Contact Phone"
                                    variant="outlined"
                                    fullWidth
                                    size="small"
                                    value={passenger?.emergency_c_phone || ""}
                                    onChange={(e) => onChange("emergency_c_phone", e.target.value)}

                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth size="small" >
                                    <InputLabel>Payment Method</InputLabel>
                                    <Select
                                        value={passenger?.payment_method || ""}
                                        onChange={(e) => onChange("payment_method", e.target.value)}
                                    >
                                        <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
                                        <MenuItem value="BANK_TRANSFER">Bank Transfer</MenuItem>
                                    </Select>
                                    {/* {validation?.payment_method?.[0] && (
                                            <FormHelperText>{validation.payment_method[0]}</FormHelperText>
                                        )} */}
                                </FormControl>
                            </Grid>

                            <Grid item xs={12} md={3}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.confirmed_booking_email || false}
                                            onChange={(e) => onChange("confirmed_booking_email", e.target.checked)}
                                        />
                                    }
                                    label="Confirmed booking email"
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.terms_n_cons || false}
                                            onChange={(e) => onChange("terms_n_cons", e.target.checked)}
                                        />
                                    }
                                    label="Terms"
                                />
                            </Grid>
                            <Grid item xs={12}>
                                <TextField
                                    label="Special Request"
                                    variant="outlined"
                                    fullWidth
                                    multiline
                                    rows={3}
                                    size="small"
                                    value={passenger?.special_request || ""}
                                    onChange={(e) => onChange("special_request", e.target.value)}
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.newsletter || false}
                                            onChange={(e) => onChange("Newsletter", e.target.checked)}
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
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.single_t_agreement || false}
                                            onChange={(e) => onChange("single_t_agreement", e.target.checked)}
                                        />
                                    }
                                    label="STA"
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.cabin_conf_accp || false}
                                            onChange={(e) => onChange("cabin_conf_accp", e.target.checked)}
                                        />
                                    }
                                    label="CCA"
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.was_on_board || false}
                                            onChange={(e) => onChange("was_on_board", e.target.checked)}
                                        />
                                    }
                                    label="WOB"
                                />
                            </Grid>

                        </Grid>
                    </Box>
                )}

                {activeStep === 2 && (
                    <Box>
                        <Typography variant="h6">Confirm Your Booking</Typography>
                        <Typography variant="body1" sx={{ mt: 2 }}>
                            <strong>Cabin:</strong> {cabin}
                        </Typography>
                        <Typography variant="body1" sx={{ mt: 1 }}>
                            <strong>Name:</strong> {passengerData.name}
                        </Typography>
                        <Typography variant="body1" sx={{ mt: 1 }}>
                            <strong>Email:</strong> {passengerData.email}
                        </Typography>
                    </Box>
                )}

                <Box sx={{ display: "flex", justifyContent: "space-between", mt: 4 }}>
                    <Button
                        disabled={activeStep === 0}
                        onClick={handleBack}
                        variant="outlined"
                    >
                        Back
                    </Button>
                    {activeStep === steps.length - 1 ? (
                        <Button onClick={handleSubmit} variant="contained" color="primary">
                            Submit
                        </Button>
                    ) : (
                        <Button
                            onClick={handleNext}
                            variant="contained"
                            color="primary"
                            disabled={activeStep === 0 && (!cabinType || !cabinCategory || !cabinNumber)}
                        >
                            Next
                        </Button>
                    )}
                </Box>
            </Box>
        </Box>
    );
};

export default BookingStepper;
