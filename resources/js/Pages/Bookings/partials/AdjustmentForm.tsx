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
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";

type AdjustmentFormProps = {
  booking: Booking;
  editMode: boolean;
  onSubmit: (data: AdjustmentData) => void;
};

type Booking = {
  id: number;
  adjustments?: AdjustmentData[]; // List of adjustments associated with the booking
};

type AdjustmentData = {
  type: "DISCOUNT" | "ADDON";
  operation: "FIXED" | "PERCENTAGE";
  value: number;
};

const AdjustmentForm: React.FC<AdjustmentFormProps> = ({
  booking,
  editMode,
  onSubmit,
}) => {
  // State for adjustment form data
  const [formData, setFormData] = useState<AdjustmentData>({
    type: "DISCOUNT",
    operation: "FIXED",
    value: 0,
  });

  // Modal open/close state
  const [open, setOpen] = useState(false);

  // State to manage adjustments list
  const [adjustments, setAdjustments] = useState<AdjustmentData[]>(
    booking.adjustments || []
  );

  // Load adjustments when booking data changes
  useEffect(() => {
    if (booking.adjustments) {
      setAdjustments(booking.adjustments);
    }
  }, [booking]);

  // Handle form input changes
  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: name === "value" ? parseFloat(value) : value,
    }));
  };

  // Handle form submission
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (formData.value <= 0) {
      alert("Value must be greater than 0");
      return;
    }

    // Call the onSubmit function provided by the parent component
    onSubmit(formData);

    // Update adjustments list and reset form data
    setAdjustments((prev) => [...prev, formData]);
    setFormData({
      type: "DISCOUNT",
      operation: "FIXED",
      value: 0,
    });
    setOpen(false);
  };

  // Open modal
  const handleOpen = () => setOpen(true);

  // Close modal
  const handleClose = () => setOpen(false);

  return (
    <Box p={2}>
      {/* Title displaying the booking ID */}
      <Typography variant="h5" mb={2}>
        Adjustments for Booking #{booking.booking_code}
      </Typography>

      {/* List of associated adjustments */}
      <Box mb={3}>
        {adjustments.length > 0 ? (
          <List>
            {adjustments.map((adjustment, index) => (
              <ListItem key={index}>
                <ListItemText
                  primary={`${adjustment.type} (${adjustment.operation})`}
                  secondary={`Value: ${adjustment.value}`}
                />
              </ListItem>
            ))}
          </List>
        ) : (
          <Typography>No adjustments associated yet.</Typography>
        )}
      </Box>

      {/* Button to open the modal for adding a new adjustment */}
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

      {/* Modal for creating a new adjustment */}
      <Modal open={open} onClose={handleClose}>
        <Box
          sx={{
            position: "absolute" as "absolute",
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
          {/* Modal title */}
          <Typography variant="h6" mb={2}>
            Create Adjustment
          </Typography>

          <Grid container spacing={2}>
            {/* Dropdown for selecting type */}
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

            {/* Dropdown for selecting operation */}
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

            {/* Input for entering value */}
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

          {/* Submit button for the modal */}
          <Box mt={3}>
            <Button
              type="submit"
              variant="contained"
              color="primary"
              fullWidth
            >
              Save Adjustment
            </Button>
          </Box>
        </Box>
      </Modal>
    </Box>
  );
};

export default AdjustmentForm;
