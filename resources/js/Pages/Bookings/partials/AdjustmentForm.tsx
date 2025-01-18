import React, { useState, useEffect } from "react";
import {
    Box,
    TextField,
    MenuItem,
    Button,
    Typography,
    Grid,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    List,
    ListItem,
    ListItemText,
    IconButton,
    Paper,
    ListItemIcon,
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import DiscountOutlined from "@mui/icons-material/DiscountOutlined";
import AddOutlined from "@mui/icons-material/AddOutlined";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";

type AdjustmentFormProps = {
    booking: Booking;
    editMode: boolean;
};

type Booking = {
    id: number;
    event_id: number;
    adjustments?: AdjustmentData[];
};

type AdjustmentData = {
    id?: number;
    type: "DISCOUNT" | "ADDON";
    operation: "FIXED" | "PERCENTAGE";
    value: number;
    code: string;
};

const defaultFormData: AdjustmentData = {
    type: "DISCOUNT",
    operation: "FIXED",
    value: 0,
    code: "",
};

const AdjustmentForm: React.FC<AdjustmentFormProps> = ({ booking, editMode }) => {
    const [formData, setFormData] = useState<AdjustmentData>(defaultFormData);
    const [adjustments, setAdjustments] = useState<AdjustmentData[]>(booking.adjustments || []);
    const [open, setOpen] = useState(false);
    const [currentEditingId, setCurrentEditingId] = useState<number | null>(null);
    const { showSnackbar } = useSnackbar();
    const {hasPermission} = usePermissions();
    const canAddAdjustment = hasPermission(Permissions.CreateAdjustments);
    const canDeleteAdjustment = hasPermission(Permissions.DeleteAdjustments);
    const canEditAdjustment = hasPermission(Permissions.EditAdjustments);

    useEffect(() => {
        setAdjustments(booking.adjustments || []);
    }, [booking]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: name === "value" ? parseFloat(value) : value,
        }));
    };

    const resetForm = () => {
        setFormData(defaultFormData);
        setCurrentEditingId(null);
    };

    const handleOpen = () => {
        resetForm();
        setOpen(true);
    };

    const handleEdit = (id: number) => {
        const adjustment = adjustments.find((adj) => adj.id === id);
        if (adjustment) {
            setFormData(adjustment);
            setCurrentEditingId(id);
            setOpen(true);
        }
    };

    const handleDelete =  (id: number) => {
        try {
            router.post(
                route("bookings.deleteAdjustment", { event_id: booking.event_id, booking_id: booking.id }),
                { id }
            );
           // setAdjustments((prev) => prev.filter((adj) => adj.id !== id));
            showSnackbar("Adjustment deleted successfully.", "success");
        } catch (error) {
            console.error("Failed to delete adjustment:", error);
            showSnackbar("An error occurred while deleting the adjustment.", "error");
        }
    };

    const createAdjustment = () => {
        try {
            router.post(
                route("bookings.createAdjustment", { event_id: booking.event_id, booking_id: booking.id }),
                formData
            );
            //setAdjustments((prev) => [...prev, response.data.adjustment]);
            showSnackbar("Adjustment created successfully.", "success");
        } catch (error) {
            console.error("Failed to create adjustment:", error);
            showSnackbar("An error occurred while creating the adjustment.", "error");
        }
    };

    const updateAdjustment = async () => {
        try {
            const response = await router.post(
                route("bookings.updateAdjustment", { event_id: booking.event_id, booking_id: booking.id }),
                { id: currentEditingId, ...formData }
            );
            // setAdjustments((prev) =>
            //     prev.map((adj) => (adj.id === currentEditingId ? response.data.adjustment : adj))
            // );
            showSnackbar("Adjustment updated successfully.", "success");
        } catch (error) {
            console.error("Failed to update adjustment:", error);
            showSnackbar("An error occurred while updating the adjustment.", "error");
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (formData.value <= 0) {
            showSnackbar("Value must be greater than 0.", "error");
            return;
        }
        if (currentEditingId) {
            await updateAdjustment();
        } else {
            await createAdjustment();
        }
        resetForm();
        setOpen(false);
    };

    return (
        <Box>
            <Typography variant="h5" mb={2}>
                Adjustments
            </Typography>
            <Paper variant="outlined" sx={{ p: 2, mb: 4 }}>
                {adjustments.length > 0 ? (
                    <List>
                        {adjustments.map((adjustment) => (
                            <ListItem
                                key={adjustment.id}
                                secondaryAction={
                                    <>
                                        <IconButton
                                            edge="end"
                                            aria-label="edit"
                                            onClick={() => handleEdit(adjustment.id!)}
                                            disabled={!canDeleteAdjustment || !editMode}
                                        >
                                            <EditIcon />
                                        </IconButton>
                                        <IconButton
                                            edge="end"
                                            aria-label="delete"
                                            onClick={() => handleDelete(adjustment.id!)}
                                            disabled={!canDeleteAdjustment || !editMode} 
                                        >
                                            <DeleteIcon />
                                        </IconButton>
                                    </>
                                }
                            >
                                <ListItemIcon>
                                    {adjustment.type === "DISCOUNT" ? (
                                        <DiscountOutlined color="primary" />
                                    ) : (
                                        <AddOutlined color="secondary" />
                                    )}
                                </ListItemIcon>
                                <ListItemText
                                    primary={`${adjustment.type} (${adjustment.code})`}
                                    secondary={`Value: ${adjustment.value} ${
                                        adjustment.operation === "PERCENTAGE" ? "%" : ""
                                    }`}
                                />
                            </ListItem>
                        ))}
                    </List>
                ) : (
                    <Typography>No adjustments associated yet.</Typography>
                )}
                <Button
                    variant="outlined"
                    color="secondary"
                    startIcon={<AddIcon />}
                    onClick={handleOpen}
                    sx={{ mt: 2 }}
                    disabled={!editMode || !canAddAdjustment}
                >
                    Add Adjustment
                </Button>
            </Paper>

            <Dialog open={open} onClose={() => setOpen(false)} fullWidth maxWidth="sm">
                <DialogTitle>{currentEditingId ? "Edit Adjustment" : "Create Adjustment"}</DialogTitle>
                <DialogContent>
                    <Grid container spacing={2}>
                        <Grid item xs={12}>
                            <TextField
                                label="Code"
                                name="code"
                                value={formData.code}
                                onChange={handleChange}
                                fullWidth
                            />
                        </Grid>
                        <Grid item xs={12} sm={6}>
                            <TextField
                                select
                                label="Type"
                                name="type"
                                value={formData.type}
                                onChange={handleChange}
                                fullWidth
                            >
                                <MenuItem value="DISCOUNT">Discount</MenuItem>
                                <MenuItem value="ADDON">Addon</MenuItem>
                            </TextField>
                        </Grid>
                        <Grid item xs={12} sm={6}>
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
                        </Grid>
                        <Grid item xs={12}>
                            <TextField
                                label="Value"
                                name="value"
                                type="number"
                                value={formData.value}
                                onChange={handleChange}
                                fullWidth
                                inputProps={{ step: 0.01, min: 0 }}
                            />
                        </Grid>
                    </Grid>
                </DialogContent>
                <DialogActions>
                    <Button variant="outlined" onClick={() => setOpen(false)} color="secondary">
                        Cancel
                    </Button>
                    <Button variant="contained" onClick={handleSubmit} color="primary">
                        {currentEditingId ? "Update Adjustment" : "Save Adjustment"}
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};

export default AdjustmentForm;
