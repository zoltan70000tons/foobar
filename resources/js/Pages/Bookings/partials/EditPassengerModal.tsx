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
} from "@mui/material";
import axios from "axios";

const EditPassengerModal = ({
    open,
    onClose,
    passenger,
    onSave,
    onChange,
}) => {
    const [searchQuery, setSearchQuery] = useState("");
    const [suggestions, setSuggestions] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const isLeadPassenger = passenger?.lead_passenger;

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

        //select
        onChange("gender", selectedUser.gender || "");
        onChange("payment_method", selectedUser.payment_method || "");
        onChange("citizenship", selectedUser.citizenship || "");

        //date
        onChange("dob", selectedUser.dob || "");


        //text area
        onChange("special_request", selectedUser.special_request || "");

        //checkboxs
        onChange("lead_passenger", selectedUser.lead_passenger);
        onChange("confirmed_booking_email", selectedUser.confirmed_booking_email || "");
        onChange("travel_info", selectedUser.travel_info || "");
        onChange("term_n_cons", selectedUser.term_n_cons || "");
        onChange("cabin_conf_accp", selectedUser.cabin_conf_accp || "");
        onChange("single_t_agreement", selectedUser.single_t_agreement || "");
        onChange("was_on_board", selectedUser.was_on_board || "");
        onChange("newsletter", selectedUser.newsletter || "");



    };

    return (
        <Modal
            open={open}
            onClose={onClose}
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
                        options={suggestions}
                        getOptionLabel={(option) => `${option.first_name} ${option.last_name} (${option.email})`}
                        loading={loading}
                        value={selectedUser}
                        onInputChange={(e, value) => setSearchQuery(value)}
                        onChange={(e, value) => setSelectedUser(value)}
                        renderInput={(params) => (
                            <TextField
                                {...params}
                                label="Search by Email or Name"
                                variant="outlined"
                                //fullWidth
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
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="First Name"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.first_name || ""}
                            onChange={(e) => onChange("first_name", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Middle Name"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.middle_name || ""}
                            onChange={(e) => onChange("middle_name", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Last Name"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.last_name || ""}
                            onChange={(e) => onChange("last_name", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Date of Birth"
                            variant="outlined"
                            fullWidth
                            type="date"
                            size="small"
                            value={passenger?.dob || ""}
                            onChange={(e) => onChange("dob", e.target.value)}
                            InputLabelProps={{ shrink: true }}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <FormControl fullWidth size="small">
                            <InputLabel>Gender</InputLabel>
                            <Select
                                value={passenger?.gender || ""}
                                onChange={(e) => onChange("gender", e.target.value)}
                                disabled={isLeadPassenger}
                            >
                                <MenuItem value="M">Male</MenuItem>
                                <MenuItem value="F">Female</MenuItem>
                                <MenuItem value="O">Other</MenuItem>
                            </Select>
                        </FormControl>
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Citizenship"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.citizenship || ""}
                            onChange={(e) => onChange("citizenship", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Email"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.email || ""}
                            onChange={(e) => onChange("email", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Phone"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.phone || ""}
                            onChange={(e) => onChange("phone", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Address Line 1"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.address_first || ""}
                            onChange={(e) => onChange("address_first", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Address Line 2"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.address_second || ""}
                            onChange={(e) => onChange("address_second", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="City"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.city || ""}
                            onChange={(e) => onChange("city", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="State"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.state || ""}
                            onChange={(e) => onChange("state", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Postal Code"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.postal_code || ""}
                            onChange={(e) => onChange("postal_code", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Country"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.country || ""}
                            onChange={(e) => onChange("country", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Emergency Contact Name"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.emergency_c_name || ""}
                            onChange={(e) => onChange("emergency_c_name", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Emergency Contact Phone"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.emergency_c_phone || ""}
                            onChange={(e) => onChange("emergency_c_phone", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Passenger Allocated Cost"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.passenger_allocated_cost || ""}
                            onChange={(e) => onChange("passenger_allocated_cost", e.target.value)}
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            label="Passenger Balance"
                            variant="outlined"
                            fullWidth
                            size="small"
                            value={passenger?.passenger_balance || ""}
                            onChange={(e) => onChange("passenger_balance", e.target.value)}
                            disabled={isLeadPassenger}
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
                            disabled={isLeadPassenger}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <FormControl fullWidth size="small">
                            <InputLabel>Payment Method</InputLabel>
                            <Select
                                value={passenger?.payment_method || ""}
                                onChange={(e) => onChange("payment_method", e.target.value)}
                                disabled={isLeadPassenger}
                            >
                                <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
                                <MenuItem value="BANK_TRANSFER">
                                Bank Transfer</MenuItem>

                            </Select>
                        </FormControl>
                    </Grid>
                    <Grid item xs={12} md={3}>
                        <FormControlLabel
                            control={
                                <Checkbox
                                    size="small"
                                    checked={passenger?.confirmed_booking_email || false}
                                    onChange={(e) => onChange("confirmed_booking_email", e.target.checked)}
                                    disabled={isLeadPassenger}
                                />
                            }
                            label="Confirmed booking email"
                        />
                    </Grid>
                    <Grid item xs={12} md={3}>
                        <FormControlLabel
                            control={
                                <Checkbox
                                    size="small"
                                    checked={passenger?.newsletter || false}
                                    onChange={(e) => onChange("Newsletter", e.target.checked)}
                                    disabled={isLeadPassenger}
                                />
                            }
                            label="Newsletter"
                        />
                    </Grid>
                    <Grid item xs={12} md={3}>
                        <FormControlLabel
                            control={
                                <Checkbox
                                    size="small"
                                    checked={passenger?.term_n_cons || false}
                                    onChange={(e) => onChange("terms_n_con", e.target.checked)}
                                    disabled={isLeadPassenger}
                                />
                            }
                            label="Terms"
                        />
                    </Grid>
                </Grid>


                <Box mt={2} display="flex" justifyContent="space-between">
                    {!isLeadPassenger && (
                        <Button variant="contained" color="primary" onClick={onSave}>
                            Save Changes
                        </Button>
                    )}
                    <Button variant="outlined" color="secondary" onClick={onClose}>
                        {isLeadPassenger ? "Close" : "Cancel"}
                    </Button>
                </Box>
            </Paper>
        </Modal>
    );
};

export default EditPassengerModal;
