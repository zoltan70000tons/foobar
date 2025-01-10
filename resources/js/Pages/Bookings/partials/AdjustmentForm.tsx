import axios from "axios";
import React, { useState, useEffect } from "react";
import {
    Box,
    TextField,
    MenuItem,
    Button,
    Typography,
    Grid,
    Modal,
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

type AdjustmentFormProps = {
    booking: Booking;
    editMode: boolean;
    onSubmit: (data: AdjustmentData) => void;
};

type Booking = {
    id: number;
    adjustments?: AdjustmentData[];
};

type AdjustmentData = {
    id: number;
    type: "DISCOUNT" | "ADDON";
    operation: "FIXED" | "PERCENTAGE";
    value: number;
    code: string;
};

const AdjustmentForm: React.FC<AdjustmentFormProps> = ({
    booking,
    editMode,
    onSubmit,
}) => {
    const [formData, setFormData] = useState<AdjustmentData>({
        type: "DISCOUNT",
        operation: "FIXED",
        value: 0,
        code: "",
    });

    const [adjustments, setAdjustments] = useState<AdjustmentData[]>(
        booking.adjustments || []
    );

    const [open, setOpen] = useState(false);
    const [currentEditingIndex, setCurrentEditingIndex] = useState<number | null>(
        null
    );

    useEffect(() => {
        if (booking.adjustments) {
            setAdjustments(booking.adjustments);
        }
    }, [booking]);

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => {
        const { name, value } = e.target;
        setFormData((prevData) => ({
            ...prevData,
            [name]: name === "value" ? parseFloat(value) : value,
        }));
    };

    const handleOpen = () => {
        setFormData({
            type: "DISCOUNT",
            operation: "FIXED",
            value: 0,
            code: "",
        });
        setCurrentEditingIndex(null);
        setOpen(true);
    };

    const handleEdit = (index: number) => {
        setFormData(adjustments[index]);
        setCurrentEditingIndex(index);
        setOpen(true);
    };

    const handleDelete = async (index: number) => {
        const adjustmentToDelete = adjustments[index];
        try {

            await axios.post(route('bookings.deleteAdjustment'), { id: adjustmentToDelete.id, booking_id: booking.id, event_id: booking.event_id });
            const updatedAdjustments = adjustments.filter((_, i) => i !== index);
            setAdjustments(updatedAdjustments);
        } catch (error) {
            console.error("Failed to delete adjustment:", error);
            alert("An error occurred while trying to delete the adjustment.");
        }
    };

    const handleClose = () => setOpen(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (formData.value <= 0) {
            alert("Value must be greater than 0");
            return;
        }

        try {
            if (currentEditingIndex !== null) {

                const response = await axios.post(route('bookings.updateAdjustment'), {
                    id: formData.id,
                    code: formData.code,
                    type: formData.type,
                    operation: formData.operation,
                    value: formData.value,
                    restrictions: null,
                    booking_id: booking.id,
                    event_id: booking.event_id,
                });

                const updatedAdjustments = adjustments.map((adj, index) =>
                    index === currentEditingIndex ? response.data.adjustment : adj
                );
                setAdjustments(updatedAdjustments);
            } else {
                // Crear un nuevo ajuste
                const response = await axios.post(route('bookings.createAdjustment'), {
                    code: formData.code,
                    type: formData.type,
                    operation: formData.operation,
                    value: formData.value,
                    restrictions: null,
                    booking_id: booking.id,
                    event_id: booking.event_id,
                });

                setAdjustments((prev) => [...prev, response.data.adjustment]);
            }

            // Cerrar el modal y reiniciar el formulario
            setOpen(false);
            setFormData({
                type: "DISCOUNT",
                operation: "FIXED",
                value: 0,
                code: "",
            });
            setCurrentEditingIndex(null);
        } catch (error) {
            console.error("Failed to save adjustment:", error);
            alert("An error occurred while trying to save the adjustment.");
        }
    };


    return (
        <Box>
            <Typography variant="h5" mb={2}>
                Adjustments
            </Typography>
            <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
                <Box mb={3}>
                    {adjustments.length > 0 ? (
                        <List>
                            {adjustments.map((adjustment, index) => (
                                <ListItem key={index} sx={{ p: 0 }} secondaryAction={
                                    editMode && (
                                        <>
                                            <IconButton
                                                edge="end"
                                                aria-label="edit"
                                                onClick={() => handleEdit(index)}
                                            >
                                                <EditIcon />
                                            </IconButton>
                                            <IconButton
                                                edge="end"
                                                aria-label="delete"
                                                onClick={() => handleDelete(index)}
                                            >
                                                <DeleteIcon />
                                            </IconButton>
                                        </>
                                    )
                                }>
                                    {adjustment && (
                                        <>
                                            <ListItemIcon>
                                                {adjustment.type === "DISCOUNT" ? (
                                                    <DiscountOutlined color="primary" />
                                                ) : (
                                                    <AddOutlined color="secondary" />
                                                )}
                                            </ListItemIcon>
                                            <ListItemText
                                                primary={`${adjustment.type} (${adjustment.code})`}
                                                secondary={`Value: ${adjustment.value} ${adjustment.operation === "PERCENTAGE" ? "%" : ""
                                                    }`}
                                            />
                                        </>
                                    )}
                                </ListItem>
                            ))}
                        </List>
                    ) : (
                        <Typography>No adjustments associated yet.</Typography>
                    )}
                </Box>
                {editMode && (
                    <Button
                        variant="contained"
                        color="primary"
                        startIcon={<AddIcon />}
                        onClick={handleOpen}
                    >
                        Add Adjustment
                    </Button>
                )}
                <Modal open={open} onClose={handleClose}>
                    <Box
                        sx={{
                            position: "absolute",
                            top: "50%",
                            left: "50%",
                            transform: "translate(-50%, -50%)",
                            width: 400,
                            bgcolor: "background.paper",
                            borderRadius: 2,
                            boxShadow: 24,
                            p: 4,
                        }}
                        component="form"
                        onSubmit={handleSubmit}
                    >
                        <Typography variant="h6" mb={2}>
                            {currentEditingIndex !== null ? "Edit Adjustment" : "Create Adjustment"}
                        </Typography>
                        <Grid container spacing={2}>
                            <Grid item xs={12}>
                                <TextField
                                    label="Code"
                                    name="code"
                                    type="text"
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
                        <Box mt={3}>
                            <Button type="submit" variant="contained" color="primary" fullWidth>
                                {currentEditingIndex !== null ? "Update Adjustment" : "Save Adjustment"}
                            </Button>
                        </Box>
                    </Box>
                </Modal>
            </Paper>
        </Box>
    );
};

export default AdjustmentForm;
