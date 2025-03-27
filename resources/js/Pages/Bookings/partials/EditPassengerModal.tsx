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
} from "@mui/material";

import axios from "axios";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { LoadingButton } from "@mui/lab";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";

const EditPassengerModal = ({
    open,
    onClose,
    passenger,
    onSave,
    onDelete,
    isSingleRoom,
    onChange,
    errors,
    editMode,
    savingLoading,
    releaseLoading
}) => {
    const [searchQuery, setSearchQuery] = useState("");
    const [suggestions, setSuggestions] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const isLeadPassenger = passenger?.lead_passenger;
    const validation = errors?.response?.data?.errors;
    const { hasPermission } = usePermissions();

    const canEdit = hasPermission(Permissions.EditPassengers);
    const canReset = hasPermission(Permissions.ResetSeat);

    const editable = isLeadPassenger || !canEdit || !editMode;

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

    return (
        <>
            <Modal
                open={open}
                onClose={handleOnClose}
                sx={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                }}
            >
                <Paper sx={{ p: 4, maxWidth: 900, width: "100%" }}>
                    <Typography variant="h6" gutterBottom>
                        {isLeadPassenger ? "View Lead Passenger" : "Edit Passenger"}
                    </Typography>

                    {/* Autocomplete Search */}
                    <Box mb={3}>
                        <Autocomplete
                            size="small"
                            options={suggestions}
                            getOptionLabel={(option) => `${option.first_name} ${option.last_name} (${option.email})`}
                            loading={loading}
                            value={selectedUser}
                            inputValue={searchQuery}
                            onInputChange={(e, value) => setSearchQuery(value)}
                            onChange={(e, value) => setSelectedUser(value)}
                            disabled={!canEdit || isLeadPassenger}
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
                        <Button
                            variant="contained"
                            color="primary"
                            onClick={handlePrefill}
                            disabled={!selectedUser || isLeadPassenger}
                            sx={{ mt: 1 }}
                        >
                            Prefill
                        </Button>
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
                                disabled={editable}
                                error={!!validation?.first_name}
                                helperText={validation?.first_name?.[0]}
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
                                disabled={editable}
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
                                disabled={editable}
                                error={!!validation?.last_name}
                                helperText={validation?.last_name?.[0]}
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
                                disabled={editable}
                            />
                        </Grid>
                        <Grid item xs={12} md={3}>
                            <FormControl fullWidth size="small" >
                                <InputLabel>Gender</InputLabel>
                                <Select
                                    value={passenger?.gender || ""}
                                    onChange={(e) => onChange("gender", e.target.value)}
                                    disabled={editable}
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
                              size="small"
                              name={ "citizenship" }
                              onChange={ (e) => onChange('citizenship', e) }
                              disabled={editable}
                              error={!!validation?.citizenship}
                              helperText={validation?.citizenship?.[0]}
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
                                disabled={editable}
                                error={!!validation?.survivor_number}
                                helperText={validation?.survivor_number?.[0]}
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
                                disabled={editable}
                                error={!!validation?.email}
                                helperText={validation?.email?.[0]}
                            />
                        </Grid>
                        <Grid item xs={12} md={3}>
                            <PhoneNumber
                              label="Phone"
                              variant="outlined"
                              fullWidth
                              size="small"
                              value={passenger?.phone || ""}
                              forceDialCode={ true }
                              name={ "phone" }
                              onChange={(e) => onChange("phone", e)}
                              disabled={editable}
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
                                disabled={editable}
                                error={!!validation?.address_first}
                                helperText={validation?.address_first?.[0]}
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
                                disabled={editable}
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
                                disabled={editable}
                                error={!!validation?.city}
                                helperText={validation?.city?.[0]}
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
                                disabled={editable}
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
                                disabled={editable}
                            />
                        </Grid>
                        <Grid item xs={12} md={3}>
                            <Country
                              fullWidth
                              label="Country"
                              variant="outlined"
                              value={passenger?.country || ""}
                              size="small"
                              name={ "country" }
                              onChange={ (e) => onChange('country', e) }
                              disabled={editable}
                              error={!!validation?.country}
                              helperText={validation?.country?.[0]}
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
                                disabled={editable}
                            />
                        </Grid>
                        <Grid item xs={12} md={3}>
                            <PhoneNumber
                              label="Emergency Contact Phone"
                              variant="outlined"
                              fullWidth
                              size="small"
                              value={passenger?.emergency_c_phone || ""}
                              forceDialCode={ true }
                              name={ "emergency_c_phone" }
                              onChange={(e) => onChange("emergency_c_phone", e)}
                              disabled={editable}
                            />
                        </Grid>
                        <Grid item xs={12} md={3}>
                            <FormControl fullWidth size="small" error={!!validation?.payment_method}>
                                <InputLabel>Payment Method</InputLabel>
                                <Select
                                    value={passenger?.payment_method || ""}
                                    onChange={(e) => onChange("payment_method", e.target.value)}
                                    disabled={editable}
                                >
                                    <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
                                    <MenuItem value="BANK_TRANSFER">Bank Transfer</MenuItem>
                                </Select>
                                {validation?.payment_method?.[0] && (
                                    <FormHelperText>{validation.payment_method[0]}</FormHelperText>
                                )}
                            </FormControl>
                        </Grid>

                        <Grid item xs={12} md={3}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        size="small"
                                        checked={passenger?.confirmed_booking_email || false}
                                        onChange={(e) => onChange("confirmed_booking_email", e.target.checked)}
                                        disabled={editable}
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
                                        disabled={editable}
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
                                disabled={editable}
                            />
                        </Grid>
                        <Grid item xs={12} md={2}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        size="small"
                                        checked={passenger?.newsletter || false}
                                        onChange={(e) => onChange("newsletter", e.target.checked)}
                                        disabled={editable}
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
                                        disabled={editable}
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
                                                disabled={editable}
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
                                        disabled={editable}
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
                                        disabled={editable}
                                    />
                                }
                                label="WOB"
                            />
                        </Grid>

                    </Grid>


                    <Box mt={2} display="flex" justifyContent="space-between">
                        <Box display="flex" gap={1}>
                            {!isLeadPassenger && canEdit && (
                                <>
                                    <LoadingButton loading={savingLoading} variant="outlined" color="primary" onClick={onSave}  disabled={editable}>
                                        Save Changes
                                    </LoadingButton>
                                    {canReset && (<LoadingButton loading={releaseLoading} variant="outlined" color="error" onClick={onDelete} disabled={editable}>
                                        Release
                                    </LoadingButton>)}
                                </>
                            )}
                        </Box>
                        <Button variant="outlined" color="secondary" onClick={onClose}>
                            {isLeadPassenger || !canEdit ? "Close" : "Cancel"}
                        </Button>
                    </Box>

                </Paper>
            </Modal>
        </>

    );
};

export default EditPassengerModal;
