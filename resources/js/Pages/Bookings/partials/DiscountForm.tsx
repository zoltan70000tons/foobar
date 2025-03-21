import React, { useState } from "react";
import {
    Box,
    TextField,
    MenuItem,
    Button,
    Typography,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Grid,
} from "@mui/material";

import { router } from "@inertiajs/react";
import { sanitizeInput } from '@/Helpers/inputSanitizer';
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import LoadingOverlay from "@/Components/LoadingOverlay";
import LocalOfferIcon from '@mui/icons-material/LocalOffer';

type DiscountFormProps = {
    passenger_id: number;
    event_id: number;
    booking_id: number;
    editMode: boolean;
};

type Discount = {
    type: string;
    amount: number;
    operation: string;
};

const DiscountForm: React.FC<DiscountFormProps> = ({ passenger_id, event_id, booking_id, editMode }) => {
    const [open, setOpen] = useState(false);
    const [formData, setFormData] = useState<Discount>({ type: "", amount: 0, operation: "" });
    const { showSnackbar } = useSnackbar();
    const [loading, setLoading] = useState(false);


    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => {
        const { name, value } = e.target;
        console.log(e.target);
        setFormData((prev) => ({
            ...prev,
            [name]: name === "amount" ? parseFloat(value) : value,
        }));
        console.log(formData);
    };

    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.type || formData.amount <= 0) {
            showSnackbar("Please fill in all fields correctly.", 'error');
            return;
        }

        setLoading(true);

        // Send form data to the server using the router
        router.post(
            route("manual.discount", {
                event_id: event_id,
                booking_id: booking_id,
            }),
            {
                passenger_id,
                amount: formData.amount,
                type: formData.type,
                operation: formData.operation
            },
            {
                onSuccess: () => {
                    // Reset form and close dialog only on success
                    setFormData({ type: "", amount: 0 , operation:""});
                    setOpen(false);
                    showSnackbar('Discount created successfully', 'success');
                    router.reload({ only: ['user'] })

                },
                onError: (errors) => {
                    // Log or display errors if needed
                    console.error("Validation errors:", errors);
                    showSnackbar('Failed to save discount. Please check your inputs.', 'error');
                },
                onFinish: () => {
                    setLoading(false);
                }
            }
        );
    };

    return (
        <Box>
            <Button
                fullWidth
                variant="outlined"
                sx={{ color: "white", borderColor: "gray" }}
                onClick={handleOpen}
                disabled={!editMode}
                startIcon={<LocalOfferIcon />}
            >
                Add Discount
            </Button>
            <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
                <DialogTitle>Add Discount</DialogTitle>
                <DialogContent>
                    <Box component="form" onSubmit={handleSubmit}>
                        <TextField
                            label="Type"
                            name="type"
                            type="text"
                            value={formData.type}
                            onChange={handleChange}
                            fullWidth
                            margin="normal"
                            required
                        />
                        <TextField
                            select
                            label="Operation"
                            name="operation"
                            value={formData.operation}
                            onChange={handleChange}
                            fullWidth
                        >
                            <MenuItem value="FIXED">Fixed</MenuItem>
                            <MenuItem value="PERCENTAGE">Percentage</MenuItem>
                        </TextField>
                        <TextField
                            label="Amount"
                            name="amount"
                            type="number"
                            value={formData.amount}
                            onChange={handleChange}
                            fullWidth
                            margin="normal"
                            inputProps={{ step: 0.01, min: 0 }}
                            required
                        />
                    </Box>
                </DialogContent>
                <DialogActions>
                    <Grid container spacing={2} sx={{ px: 2 }}>
                        <Grid item xs={6}>
                            <Button
                                variant="outlined"
                                color="secondary"
                                fullWidth
                                onClick={handleClose}
                            >
                                Cancel
                            </Button>
                        </Grid>
                        <Grid item xs={6}>
                            <Button
                                type="submit"
                                variant="contained"
                                color="primary"
                                fullWidth
                                onClick={handleSubmit}

                            >
                                Save
                            </Button>
                        </Grid>
                    </Grid>
                </DialogActions>
                <LoadingOverlay open={loading} />
            </Dialog>
        </Box>
    );
};

export default DiscountForm;
