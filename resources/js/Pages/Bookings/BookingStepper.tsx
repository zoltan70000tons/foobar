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
    TableContainer,
    Table,
    TableCell,
    TableRow,
    TableBody,
    Paper,
    Tab,
    Tabs,
    ToggleButton, Tooltip
} from "@mui/material";

import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { LocationEnum } from "@/enums/LocationEnum";
import { DeckEnum } from "@/enums/DeckEnum";
import { router } from "@inertiajs/react";
import {FilterList} from "@mui/icons-material";
import Country from "@/Components/Country";

const TabPanel = ({ children, value, index }) => {
    return (
        <div role="tabpanel" hidden={value !== index}>
            {value === index && <Box sx={{ p: 2 }}>{children}</Box>}
        </div>
    );
};

const BookingStepper: React.FC = ({ cabinTypes, cabinCategories }) => {
    const [activeStep, setActiveStep] = useState(0);
    const [cabin, setCabin] = useState("");
    const [passenger, setPassenger] = useState({
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
    const [paymentPlan, setPaymentPlan] = useState(null);
    const [numberOfInstallments, setNumberOfInstallments] = useState(null);
    const [isNextDisabled, setIsNextDisabled] = useState(true);
    const [carbonOffset, setCarbonOffset] = useState(false);
    const [isSingleRoom, setIsSingleRoom] = useState(false);
    const paymentPlanOptions = [{
        id: 'INSTALLMENTS', value: 'INSTALLMENTS'
    }, {
        id: 'PAY_IN_FULL', value: 'PAY_IN_FULL'
    }];
    const [tabValue, setTabValue] = useState(0);

    const handleTabChange = (event, newValue) => {
        setTabValue(newValue);
    };

    useEffect(() => {
        setIsNextDisabled(!validateStep());
        console.log(isNextDisabled);
        console.log('Category', cabinCategory, 'CabinNumber', cabinNumber, 'PASSENGER', passenger, 'PAYMENT PLAN', paymentPlan);
    }, [activeStep, cabinType, cabinCategory, cabinNumber, passenger, paymentPlan, numberOfInstallments]);


    const { showSnackbar } = useSnackbar();

    const steps = ["Select Cabin", "Passenger Details", "Special Request", "Discounts/Addons", "Confirm & Submit"];

    const onChange = (field, value) => {
        setPassenger((prev) => ({ ...prev, [field]: value }));
    }

    const validateStep = () => {
        console.log(activeStep);
        switch (activeStep) {
            case 0:
                let rule = cabinType && cabinCategory && cabinNumber && paymentPlan && cabinNumber;
                if (paymentPlan?.value === 'INSTALLMENTS') {
                    rule = rule && numberOfInstallments;
                }
                return !!(rule);
            case 1:
                return !!(
                    passenger.first_name &&
                    passenger.last_name &&
                    passenger.address_first &&
                    passenger.city &&
                    passenger.country &&
                    passenger.email &&
                    passenger.dob &&
                    passenger.gender &&
                    passenger.payment_method
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


    const handlePrefill = () => {
        if (!selectedUser) return;

        setPassenger((prev) => ({
            ...prev,
            id: selectedUser.id,
            first_name: selectedUser.first_name,
            middle_name: selectedUser.middle_name || "",
            last_name: selectedUser.last_name,
            dob: selectedUser.dob || "",
            gender: selectedUser.gender || "",
            citizenship: selectedUser.citizenship || "",
            survivor_number: selectedUser.survivor_number || "",
            email: selectedUser.email,
            phone: selectedUser.phone || "",
            address_first: selectedUser.address_first || "",
            address_second: selectedUser.address_second || "",
            city: selectedUser.city || "",
            state: selectedUser.state || "",
            postal_code: selectedUser.postal_code || "",
            country: selectedUser.country || "",
            emergency_c_name: selectedUser.emergency_c_name || "",
            emergency_c_phone: selectedUser.emergency_c_phone || "",
            payment_method: selectedUser.payment_method || "",
            special_request: selectedUser.special_request || "",
            lead_passenger: selectedUser.lead_passenger || false,
            confirmed_booking_email: selectedUser.confirmed_booking_email || false,
            travel_info: selectedUser.travel_info || false,
            terms_n_cons: selectedUser.terms_n_cons || false,
            cabin_conf_accp: selectedUser.cabin_conf_accp || false,
            single_t_agreement: selectedUser.single_t_agreement || false,
            was_on_board: selectedUser.was_on_board || false,
            newsletter: selectedUser.newsletter || false,
            passenger_allocated_cost: selectedUser.passenger_allocated_cost || "",
            passenger_balance: selectedUser.passenger_balance || "",
        }));
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
        console.log(cabinCategory);
    }, [cabinCategory]);




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



    const handleSubmit = () => {
        const payload = {
            cabin_number: cabinNumber,
            payment_plan: paymentPlan.value,
            number_of_installments: numberOfInstallments?.value,
            carbon_offset: carbonOffset,
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
                lead_passenger: passenger.lead_passenger,
                confirmed_booking_email: passenger.confirmed_booking_email,
                travel_info: passenger.travel_info,
                terms_n_cons: passenger.terms_n_cons,
                cabin_conf_accp: passenger.cabin_conf_accp,
                single_t_agreement: passenger.single_t_agreement,
                was_on_board: passenger.was_on_board,
                newsletter: passenger.newsletter,
                passenger_allocated_cost: passenger.passenger_allocated_cost,
                passenger_balance: passenger.passenger_balance,
            },
        };

        if (!payload.cabin_number || !payload.passenger.first_name || !payload.passenger.email) {
            showSnackbar('Please fill all required fields!', 'error');
            return;
        }

        router.post(route('bookings.createManual', { id: 1 }), payload, {
            onSuccess: () => {
                showSnackbar('Booking created successfully!', 'success');
                setActiveStep(0);
            },
            onError: (errors) => {
                console.error('Error creating booking:', errors);
                showSnackbar('Failed to create booking. Please try again.', 'error');
            },
        });
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
console.log({cabinNumber})
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
                                        renderInput={(params) => <TextField {...params} label="Cabin Type" />}
                                        sx={{ mb: 2 }}
                                    />
                                </FormControl>
                            </Grid>

                            <Grid item xs={12} md={6}>
                                <FormControl fullWidth sx={{ mt: 2 }}>
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
                            </Grid>

                            <Grid item xs={12} md={3} sx={{ mt: 2 }}>
                                <FormControl fullWidth>
                                    {/* <FormControlLabel
                                        control={
                                            <Switch
                                                checked={advancedFilters}
                                                onChange={() => setAdvancedFilters(!advancedFilters)}
                                            />
                                        }
                                        label="Advanced Filters"
                                    /> */}
                                    <ToggleButton
                                    value="advancedFilters"
                                    selected={advancedFilters}
                                    onChange={() => setAdvancedFilters(!advancedFilters)}
                                >
                                    <FilterList />
                                    Advanced Filters
                                </ToggleButton>
                                </FormControl>

                            </Grid>
                            {advancedFilters && (
                                <>
                                    <Grid item xs={12} md={3}>
                                        <FormControl fullWidth >
                                            <Autocomplete
                                                fullWidth
                                                options={Object.values(DeckEnum).filter((value) => typeof value === "number")}
                                                getOptionLabel={(option) => `Deck ${option}`}
                                                value={selectedDeck}
                                                onChange={(event, newValue) => setSelectedDeck(newValue)}
                                                renderInput={(params) => <TextField {...params} label="Deck" />}
                                                sx={{ mb: 2 }}
                                            />
                                        </FormControl>
                                    </Grid>
                                    <Grid item xs={12} md={3}>
                                        <FormControl fullWidth >
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
                                    </Grid>
                                    <Grid item xs={12} md={3}>
                                        <FormControlLabel
                                            control={
                                                <Switch
                                                    checked={onlyBalcony}
                                                    onChange={(e) => setOnlyBalcony(e.target.checked)}
                                                />
                                            }
                                            label="Only Balcony"
                                        />
                                    </Grid>
                                    <Grid item xs={12} md={3}>
                                        <FormControlLabel
                                            control={
                                                <Switch
                                                    checked={onlyAccessible}
                                                    onChange={(e) => setOnlyAccessible(e.target.checked)}
                                                />
                                            }
                                            label="Only Accessible"
                                        />
                                    </Grid>



                                </>
                            )}

                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth>
                                    <Autocomplete
                                        fullWidth
                                        options={availableCabins}
                                        getOptionLabel={(option) => option.cabin_number}
                                        value={availableCabins.find((cabin) => cabin.cabin_number === cabinNumber) || null}
                                        onChange={(event, newValue) => {
                                            setIsSingleRoom(newValue?.cabin_type_id !== 1)
                                            setCabinNumber(newValue?.cabin_number || null)
                                        }}
                                        renderInput={(params) => <TextField {...params} label="Available Cabins" />}
                                    />
                                </FormControl>

                            </Grid>

                            {/*Payment Plan */}
                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth >
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
                            {paymentPlan?.value === 'INSTALLMENTS' && (
                                <Grid item xs={12} md={4}>
                                    <FormControl fullWidth >
                                        <Autocomplete
                                            fullWidth
                                            options={[{ id: 3, value: 3 }, { id: 4, value: 4 }]}
                                            getOptionLabel={(option) => `${option.value}`}
                                            value={numberOfInstallments}
                                            onChange={(event, newValue) => setNumberOfInstallments(newValue)}
                                            renderInput={(params) => <TextField {...params} label="Number of Installments" />}
                                            sx={{ mb: 2 }}
                                        />
                                    </FormControl>
                                </Grid>
                            )}
                        </Grid>
                    </Box>)}

                {activeStep === 1 && (
                    <Box sx={{ mt: 4 }}>
                        <Typography variant="h6">Lead Passenger Details</Typography>
                        <Box sx={{ mt: 2, mb: 2 }}>
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

                                    >   <MenuItem value=""></MenuItem>
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
                                    value={ passenger?.citizenship || "" }
                                    size="small"
                                    name={ "citizenship" }
                                    onChange={ (e) => onChange('citizenship', e) }
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
                                <Country
                                    fullWidth
                                    label="Country"
                                    variant="outlined"
                                    value={ passenger?.country || "" }
                                    size="small"
                                    name={ "country" }
                                    onChange={ (e) => onChange('country', e) }
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
                            {isSingleRoom && (<Grid item xs={12} md={2}>
                                <Tooltip title="Single Ticket Agreement">
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
                                </Tooltip>
                            </Grid>)}
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
                {activeStep === 2 && (<Box sx={{ mt: 4 }}>
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
                </Box>)}

                {activeStep === 3 && (

                    <Box>
                        <Typography variant="body1" sx={{ mt: 2 }}>
                            Carbon Offset
                        </Typography>
                        <Grid item xs={12} md={2}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        size="small"
                                        checked={carbonOffset}
                                        onChange={(e) => setCarbonOffset(!carbonOffset)}
                                    />
                                }
                                label="Carbon Offset"
                            />
                        </Grid>
                    </Box>
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
                            <Tab label="Payment Info"/>
                        </Tabs>

                        {/* Tab Panel for Cabin Details */}
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
                                                <strong>Number:</strong>
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
                                                <strong>Price Per Person:</strong>
                                            </TableCell>
                                            <TableCell>{cabinCategory.price}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </TableContainer>
                        </TabPanel>

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
                                                <strong>First Address:</strong>
                                            </TableCell>
                                            <TableCell>{passenger.first_address}</TableCell>
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
                    </Box>)}

                <Box sx={{ display: "flex", justifyContent: "space-between", mt: 4 }}>
                    <Button
                        disabled={activeStep === 0}
                        onClick={handleBack}
                        variant="outlined"
                    >
                        Back
                    </Button>
                    {activeStep === steps.length - 1 ? (
                        <Button onClick={handleSubmit} variant="outlined" color="success">
                            Create Booking
                        </Button>
                    ) : (
                        <Button
                            onClick={handleNext}
                            variant="contained"
                            color="primary"
                            disabled={isNextDisabled}
                        >
                            Next
                        </Button>
                    )}
                </Box>
            </Box>
        </Box >
    );
};

export default BookingStepper;
