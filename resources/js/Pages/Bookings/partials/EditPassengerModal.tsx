import React, { useState, useEffect } from "react";
import {
    Box,
    Button,
    Checkbox,
    FormControl,
    FormControlLabel,
    Grid,
    InputLabel,
    MenuItem,
    Modal,
    Paper,
    Select,
    TextField,
    Typography,
    Autocomplete,
    CircularProgress,
    FormHelperText,
    Tooltip,
    Chip,
    Dialog,
    Stack,
} from "@mui/material";
import CloseIcon from '@mui/icons-material/Close';
import IconButton from '@mui/material/IconButton';
import CreditCardIcon from '@mui/icons-material/CreditCard';
import DirectionsBoatIcon from '@mui/icons-material/DirectionsBoat';
import ReportProblemIcon from '@mui/icons-material/ReportProblem';
import HomeIcon from '@mui/icons-material/Home';
import ContactMailIcon from '@mui/icons-material/ContactMail';
import PersonIcon from '@mui/icons-material/Person';


import axios from "axios";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { LoadingButton } from "@mui/lab";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import iso3166 from 'iso-3166-2';
import SpecialRequest from "@/Pages/Bookings/partials/SpecialRequest";

const EditPassengerModal = ({
    open,
    onClose,
    passenger,
    onSave,
    onDelete,
    showModalSeatEmpty,
    isSingleRoom,
    booking,
    onChange,
    errors,
    editMode,
    savingLoading,
    releaseLoading,
    emptySeatLoading,

}) => {
    const [searchQuery, setSearchQuery] = useState("");
    const [suggestions, setSuggestions] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [selectedCountry, setSelectedCountry] = useState(null);
    const [selectedOptions, setSelectedOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const isLeadPassenger = passenger?.lead_passenger;
    const validation = errors?.response?.data?.errors;
    const { hasPermission } = usePermissions();
    const { showSnackbar } = useSnackbar();

    const canEdit = hasPermission(Permissions.EditPassengers);
    const canReset = hasPermission(Permissions.ResetSeat);

    const editable = isLeadPassenger || !canEdit || !editMode;

    const disabledByDesign = !canEdit || !editMode;

    const isDisabled = passenger?.survivor_number || passenger?.empty_seat || disabledByDesign;
    const bookingId = booking.id;
    const eventId = booking.event_id;

    useEffect(() => {
        if (searchQuery.length < 3) {
            setSuggestions([]);
            return;
        }
        const fetchSuggestions = async () => {
            setLoading(true);
            try {
                const response = await axios.get("/passengers/search", { params: { query: searchQuery, bookingId, eventId } });
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

    useEffect(() => {
        if (selectedCountry === null) return;
        setSelectedOptions(getStateOptions(selectedCountry));
    }, [selectedCountry]);

    useEffect(() => {
        if (passenger?.country && passenger.country !== selectedCountry) {
            setSelectedCountry(passenger.country);
        }
    }, [passenger?.country]);

    const handleOnClose = () => {
        setSearchQuery("");
        setSelectedUser(null);
        setSuggestions([]);
        if (onClose) {
            onClose();
        }
    }

    const handlePrefill = () => {
        if (!selectedUser) return;

        if (selectedUser.has_booking) {
            showSnackbar("User already has a booking for the same event!", "error");
            return;
        }

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

    const onChangeCountry = (e) => {
        setSelectedCountry(e);
        passenger.state = "";
        onChange('country', e);
    }


    const getStateOptions = (countryCode: 'US' | 'CA') => {
        const subdivisions = iso3166.country(countryCode)?.sub || {};
        return Object.entries(subdivisions).map(([fullCode, { name }]) => {
            // fullCode is like "US-CA", so we split and take the second part
            const shortCode = fullCode.split('-')[1];
            return {
                label: name,
                value: shortCode,
            };
        }).sort((a, b) => a.label.localeCompare(b.label));
    };


    const SectionTitle = ({ icon: Icon, title, color = 'primary.main' }) => (
        <Box sx={{ borderBottom: '1px solid', borderColor: 'divider', mb: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', pb: 1 }}>
                <Icon sx={{ mr: 1, color }} />
                <Typography variant="h6">{title}</Typography>
            </Box>
        </Box>
    );


    return (
        <>
            <Dialog
                open={open}
                onClose={handleOnClose}
                fullScreen
                fullWidth
                maxWidth="lg"
                disableEscapeKeyDown={false}
            >
                <Box
                    sx={{
                        position: 'sticky',
                        top: 0,
                        zIndex: 1201,
                        backgroundColor: 'background.paper',
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        px: 4,
                        py: 2,
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                        width: '100%',
                        boxShadow: '0px 2px 8px rgba(0, 0, 0, 0.05)', // sutil sombra elegante
                        backdropFilter: 'blur(6px)', // da efecto de fondo tipo "glass"
                    }}
                >
                    <Typography
                        variant="h5"
                        sx={{
                            fontWeight: 500,
                            color: 'text.primary',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis',
                        }}
                    >
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                            <Typography
                                variant="h5"
                                sx={{ fontWeight: 500, color: 'text.primary' }}
                            >
                                {isLeadPassenger ? "Edit Lead Passenger" : "Edit Passenger"}
                            </Typography>
                            <Chip
                                label={`BC: ${booking.booking_code}`}
                                color="info"
                                variant="outlined"
                                sx={{ fontWeight: 500 }}
                            />
                        </Box>
                    </Typography>

                    <IconButton
                        aria-label="close"
                        onClick={handleOnClose}
                        sx={{
                            color: 'text.secondary',
                            transition: 'color 0.2s ease',
                            '&:hover': {
                                color: 'error.main',
                            },
                        }}
                    >
                        <CloseIcon fontSize="medium" />
                    </IconButton>
                </Box>


                <Paper sx={{ p: 4, width: "100%" }}>
                    {passenger?.empty_seat && (
                        <Typography variant="body2" color="error" gutterBottom marginBottom={1}>
                            This passenger is marked as an Empty Seat. Please RELEASE the empty seat option to edit the passenger details.
                        </Typography>
                    )}

                    <Box mb={3}>
                        <Stack direction="row" spacing={1}>
                            <Autocomplete
                                options={suggestions}
                                getOptionLabel={(option) => `${option.first_name} ${option.last_name} (${option.email})`}
                                loading={loading}
                                value={selectedUser}
                                inputValue={searchQuery}
                                onInputChange={(e, value) => setSearchQuery(value)}
                                onChange={(e, value) => setSelectedUser(value)}
                                disabled={isDisabled}
                                sx={{ flexGrow: 1 }}
                                renderInput={(params) => (
                                    <TextField
                                        {...params}
                                        label="Search by Email or Name"
                                        variant="outlined"
                                        // size="small"
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
                                    <li {...props}>
                                        <div style={{ display: 'flex', alignItems: 'center' }}>
                                            <span>
                                                {`${option.first_name} ${option.last_name} (${option.email})`}
                                            </span>
                                            {option.has_booking && (
                                                <Chip label="ALREADY BOOKED" color="error" style={{ marginLeft: '20px' }} />
                                            )}
                                        </div>
                                    </li>
                                )}
                            />
                            <Button
                                variant="contained"
                                color="primary"
                                onClick={handlePrefill}
                                disabled={!selectedUser || isLeadPassenger}
                            >
                                Prefill
                            </Button>
                        </Stack>
                    </Box>


                    <Grid>

                        <SectionTitle icon={PersonIcon} title={'PERSONAL INFO'} />
                        {/* First group */}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            {/* First Column */}
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="First Name"
                                    variant="outlined"
                                    fullWidth
                                    //size="small"
                                    value={passenger?.first_name || ""}
                                    onChange={(e) => onChange("first_name", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.first_name}
                                    helperText={validation?.first_name?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Middle Name"
                                    variant="outlined"
                                    fullWidth
                                    //size="small"
                                    value={passenger?.middle_name || ""}
                                    onChange={(e) => onChange("middle_name", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.middle_name}
                                    helperText={validation?.middle_name?.[0]}
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Last Name"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.last_name || ""}
                                    onChange={(e) => onChange("last_name", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.last_name}
                                    helperText={validation?.last_name?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Date of Birth"
                                    variant="outlined"
                                    fullWidth
                                    type="date"
                                    //size="small"
                                    value={passenger?.dob || ""}
                                    onChange={(e) => onChange("dob", e.target.value)}
                                    InputLabelProps={{ shrink: true }}
                                    disabled={isDisabled}
                                    error={!!validation?.dob}
                                    helperText={validation?.dob?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth  >
                                    <InputLabel>Gender</InputLabel>
                                    <Select
                                        value={passenger?.gender || ""}
                                        onChange={(e) => onChange("gender", e.target.value)}
                                        disabled={isDisabled}
                                        label={'Gender'}
                                        error={!!validation?.gender}
                                        helperText={validation?.gender?.[0]}
                                        required
                                    >
                                        <MenuItem value="M">Male</MenuItem>
                                        <MenuItem value="F">Female</MenuItem>
                                        <MenuItem value="O">Other</MenuItem>
                                    </Select>
                                </FormControl>
                            </Grid>

                            <Grid item xs={12} md={3}>
                                <Country
                                    fullWidth
                                    label="Citizenship"
                                    variant="outlined"
                                    value={passenger?.citizenship || ""}
                                    // size="small"
                                    name={"citizenship"}
                                    onChange={(e) => onChange('citizenship', e)}
                                    disabled={isDisabled}
                                    error={!!validation?.citizenship}
                                    helperText={validation?.citizenship?.[0]}
                                    required
                                />
                            </Grid>

                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Survivor Number"
                                    variant="outlined"
                                    fullWidth
                                    //size="small"
                                    value={passenger?.survivor_number || ""}
                                    onChange={(e) => onChange("survivor_number", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.survivor_number}
                                    helperText={validation?.survivor_number?.[0]}
                                />
                            </Grid>
                        </Grid>

                        <SectionTitle icon={ContactMailIcon} title={'CONTACT INFO'} />
                        {/* Second group*/}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Email"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.email || ""}
                                    onChange={(e) => onChange("email", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.email}
                                    helperText={validation?.email?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <PhoneNumber
                                    label="Phone"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.phone || ""}
                                    forceDialCode={true}
                                    name={"phone"}
                                    onChange={(e) => onChange("phone", e)}
                                    disabled={isDisabled}
                                    error={!!validation?.phone}
                                    helperText={validation?.phone?.[0]}
                                    required
                                />
                            </Grid>
                        </Grid>


                        <SectionTitle icon={HomeIcon} title={'ADDRESS INFO'} />
                        {/* First group */}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            <Grid item xs={12} md={5}>
                                <TextField
                                    label="Address Line 1"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.address_first || ""}
                                    onChange={(e) => onChange("address_first", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.address_first}
                                    helperText={validation?.address_first?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={5}>
                                <TextField
                                    label="Address Line 2"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.address_second || ""}
                                    onChange={(e) => onChange("address_second", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.address_second}
                                    helperText={validation?.citizenship?.[0]}
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <TextField
                                    label="City"
                                    variant="outlined"
                                    fullWidth
                                    //  size="small"
                                    value={passenger?.city || ""}
                                    onChange={(e) => onChange("city", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.city}
                                    helperText={validation?.city?.[0]}
                                    required
                                />
                            </Grid>
                            {/* <Grid item xs={12} md={3}>
                                <TextField
                                    label="State"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.state || ""}
                                    onChange={(e) => onChange("state", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.state}
                                    helperText={validation?.state?.[0]}
                                />
                            </Grid> */}


                            <Grid item xs={12} md={6}>
                                <Country
                                    fullWidth
                                    label="Country"
                                    variant="outlined"
                                    value={passenger?.country || ""}
                                    // size="small"
                                    name={"country"}
                                    onChange={onChangeCountry}
                                    disabled={isDisabled}
                                    error={!!validation?.country}
                                    helperText={validation?.country?.[0]}
                                    required
                                />
                            </Grid>
                            {selectedCountry === "USA" || selectedCountry === "CAN" ? (<Grid item xs={12} md={3}>
                                <Autocomplete
                                    options={selectedOptions}
                                    getOptionLabel={(option) => option.label ? option.label : ""}
                                    value={selectedOptions.find(option => option.value === passenger?.state) || null}
                                    onChange={(e, value) => onChange("state", value)}
                                    disabled={isDisabled}
                                    isOptionEqualToValue={(option, value) => option.value === value.value}
                                    renderInput={(params) => (
                                        <TextField
                                            {...params}
                                            required
                                            label="State"
                                            variant="outlined"
                                            error={!!validation?.state}
                                            helperText={validation?.state?.[0]}
                                        />
                                    )} />
                            </Grid>) : (
                                <Grid item xs={12} md={3}><TextField
                                    fullWidth
                                    label="State"
                                    variant="outlined"
                                    value={passenger?.state || ""}
                                    // size="small"
                                    name={"state"}
                                    onChange={(e) => onChange("state", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.state}
                                    helperText={validation?.state?.[0]}
                                /></Grid>)}
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Postal Code"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.postal_code || ""}
                                    onChange={(e) => onChange("postal_code", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.postal_code}
                                    helperText={validation?.postal_code?.[0]}
                                    required
                                />
                            </Grid>
                        </Grid>

                        <SectionTitle icon={ReportProblemIcon} title={'EMERGENCY CONTACT'} />
                        {/* First group */}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            <Grid item xs={12} md={3}>
                                <TextField
                                    label="Emergency Contact Name"
                                    variant="outlined"
                                    fullWidth
                                    // size="small"
                                    value={passenger?.emergency_c_name || ""}
                                    onChange={(e) => onChange("emergency_c_name", e.target.value)}
                                    disabled={isDisabled}
                                    error={!!validation?.emergency_c_name}
                                    helperText={validation?.emergency_c_name?.[0]}
                                    required
                                />
                            </Grid>
                            <Grid item xs={12} md={3}>
                                <PhoneNumber
                                    label="Emergency Contact Phone"
                                    variant="outlined"
                                    fullWidth
                                    //size="small"
                                    value={passenger?.emergency_c_phone || ""}
                                    forceDialCode={true}
                                    name={"emergency_c_phone"}
                                    onChange={(e) => onChange("emergency_c_phone", e)}
                                    disabled={isDisabled}
                                    error={!!validation?.emergency_c_phone}
                                    helperText={validation?.emergency_c_phone?.[0]}
                                    required
                                />
                            </Grid>

                        </Grid>

                        <SectionTitle icon={CreditCardIcon} title="PAYMENT INFO" />
                        {/* First group */}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            <Grid item xs={12} md={3}>
                                <FormControl fullWidth error={!!validation?.payment_method}>
                                    <InputLabel>Payment Method</InputLabel>
                                    <Select
                                        value={passenger?.payment_method || ""}
                                        onChange={(e) => onChange("payment_method", e.target.value)}
                                        disabled={disabledByDesign}
                                        label={'Payment Method'}
                                        required
                                        error={!!validation?.payment_method}
                                        helperText={validation?.payment_method?.[0]}
                                    >
                                        {booking.payment_plan === 'PAY_IN_FULL' && (<MenuItem value="BANK_TRANSFER">Bank Transfer</MenuItem>)}
                                        <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
                                        
                                    </Select>
                                    {validation?.payment_method?.[0] && (
                                        <FormHelperText>{validation.payment_method[0]}</FormHelperText>
                                    )}
                                </FormControl>
                            </Grid>
                        </Grid>



                        <SectionTitle icon={DirectionsBoatIcon} title="TRAVEL INFO" />
                        {/* Second group*/}
                        <Grid container spacing={2} alignItems="center" sx={{ mb: '1rem' }}>
                            <Grid item xs={12} md={3}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.confirmed_booking_email || false}
                                            onChange={(e) => onChange("confirmed_booking_email", e.target.checked)}
                                            disabled={disabledByDesign}
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
                                            checked={passenger?.terms_n_cons || isLeadPassenger === false}
                                            onChange={(e) => onChange("terms_n_cons", e.target.checked)}
                                            disabled={disabledByDesign}
                                        />
                                    }
                                    label="Terms"
                                />
                            </Grid>
                            <Grid item xs={12}>
                                <SpecialRequest
                                  disabledByDesign={disabledByDesign}
                                  onChange={onChange}
                                  passenger={passenger}
                                />
                            </Grid>
                            <Grid item xs={12} md={2}>
                                <FormControlLabel
                                    control={
                                        <Checkbox
                                            size="small"
                                            checked={passenger?.newsletter || false}
                                            onChange={(e) => onChange("newsletter", e.target.checked)}
                                            disabled={disabledByDesign}
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
                                            disabled={disabledByDesign}
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
                                                disabled={disabledByDesign}
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
                                            checked={passenger?.cabin_conf_accp || isLeadPassenger === false}
                                            onChange={(e) => onChange("cabin_conf_accp", e.target.checked)}
                                            disabled={disabledByDesign}
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
                                            disabled={disabledByDesign}
                                        />
                                    }
                                    label="WOB"
                                />
                            </Grid>
                        </Grid>





                    </Grid>


                    <Box mt={2} display="flex" justifyContent="space-between" alignItems="center" flexWrap="wrap" gap={1}>
                        {/* Left Side Actions */}
                        <Stack direction="row" spacing={1}></Stack>

                        {/* Right Side Actions */}
                        <Stack direction="row" spacing={1} justifyContent="flex-end">
                            <LoadingButton
                                loading={emptySeatLoading}
                                variant="outlined"
                                color="warning"
                                onClick={showModalSeatEmpty}
                                disabled={editable}
                            >
                                {passenger?.empty_seat ? 'Unset empty seat' : 'Set empty seat'}
                            </LoadingButton>
                            {canReset && (
                                <LoadingButton
                                    loading={releaseLoading}
                                    variant="outlined"
                                    color="error"
                                    onClick={onDelete}
                                    disabled={editable}
                                >
                                    Release
                                </LoadingButton>
                            )}
                            <LoadingButton
                                loading={savingLoading}
                                variant="outlined"
                                color="primary"
                                onClick={onSave}
                                disabled={disabledByDesign}
                            >
                                Save Changes
                            </LoadingButton>
                            {passenger?.survivor_number && !disabledByDesign && (
                                <a
                                    href={route('customers.editBySurvivorNumber', { survivorNumber: passenger.survivor_number })}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    style={{ textDecoration: 'none' }}
                                >
                                    <Button variant="outlined" color="warning">
                                        Edit Customer
                                    </Button>
                                </a>
                            )}
                            <Button variant="outlined" color="secondary" onClick={onClose}>
                                {disabledByDesign ? "Close" : "Cancel"}
                            </Button>
                        </Stack>
                    </Box>

                </Paper>
            </Dialog>
        </>

    );
};

export default EditPassengerModal;
