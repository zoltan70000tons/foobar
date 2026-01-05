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
  Divider,
  TableContainer,
  Table,
  Paper,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  IconButton,
  Tooltip,
} from "@mui/material";
import InfoIcon from "@mui/icons-material/Info";

import { router } from "@inertiajs/react";
import { sanitizeInput } from "@/Helpers/inputSanitizer";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import LoadingOverlay from "@/Components/LoadingOverlay";
import LocalOfferIcon from "@mui/icons-material/LocalOffer";
import { formatCurrency, formatDate } from "@/Helpers/stringUtils";
import { Passenger } from "@/Pages/Bookings/partials/Payment";
import { Delete } from "@mui/icons-material";
import { Discount } from "@/types/discount";

type DiscountFormProps = {
  passenger: Passenger;
  event_id: number;
  booking_id: number;
  editMode: boolean;
};

const DiscountForm: React.FC<DiscountFormProps> = ({ passenger, event_id, booking_id, editMode }) => {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState<Pick<Discount, "type" | "amount" | "operation"> & { notes?: string }>({
    type: "",
    amount: 0,
    operation: "",
    notes: "",
  });
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);
  const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
  const [discountIdToDelete, setDiscountIdToDelete] = useState(null);
  const { hasPermission } = usePermissions();
  const canDeletePassengerDiscounts = hasPermission(Permissions.DeletePassengerDiscounts);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
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

  const handleOpenDelete = (discountId) => {
    setDiscountIdToDelete(discountId);
    setConfirmDeleteOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.type || formData.amount <= 0) {
      showSnackbar("Please fill in all fields correctly.", "error");
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
        passenger_id: passenger.id,
        amount: formData.amount,
        type: formData.type,
        operation: formData.operation,
        notes: formData.notes ?? "",
      },
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({ type: "", amount: 0, operation: "", notes: "" });
          setOpen(false);
          showSnackbar("Discount created successfully", "success");
          router.reload({ only: ["user"] });
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error("Validation errors:", errors);
          showSnackbar("Failed to save discount. Please check your inputs.", "error");
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleConfirmDelete = () => {
    if (!discountIdToDelete) return;

    setLoading(true);
    router.post(
      route("delete.discount", { event_id, booking_id }),
      {
        passenger_id: passenger.id,
        discount_id: discountIdToDelete,
      },
      {
        onSuccess: () => {
          showSnackbar("Discount deleted successfully.", "success");
        },
        onError: () => {
          showSnackbar("Could not delete discount.", "error");
        },
        onFinish: () => {
          setLoading(false);
          setConfirmDeleteOpen(false);
          setDiscountIdToDelete(null);
        },
      },
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
          <form onSubmit={handleSubmit}>
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
              <TextField
                label="Notes"
                name="notes"
                type="text"
                value={formData.notes}
                onChange={handleChange}
                fullWidth
                margin="normal"
                multiline
                minRows={3} // or rows={3}
              />
            </Box>
            <DialogActions>
              <Grid container spacing={2} sx={{ px: 2 }}>
                <Grid item xs={6}>
                  <Button variant="outlined" color="secondary" fullWidth onClick={handleClose}>
                    Cancel
                  </Button>
                </Grid>
                <Grid item xs={6}>
                  <Button type="submit" variant="contained" color="primary" fullWidth>
                    Save
                  </Button>
                </Grid>
              </Grid>
            </DialogActions>
          </form>
        </DialogContent>
        <DialogContent>
          <Divider sx={{ my: 3 }} />

          <Typography variant="h6" gutterBottom>
            Discount History for {passenger?.full_name || "Unknown Passenger"}
          </Typography>

          {passenger.discounts.length > 0 ? (
            <TableContainer component={Paper}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Type</TableCell>
                    <TableCell>Operation</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Created At</TableCell>
                    <TableCell>Delete</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {passenger.discounts.map((discount) => (
                    <TableRow key={discount.id}>
                      <TableCell>
                        {discount.type}
                        <Tooltip title={discount.notes}>
                          <IconButton>
                            <InfoIcon fontSize={"small"} />
                          </IconButton>
                        </Tooltip>
                      </TableCell>
                      <TableCell>{discount.operation}</TableCell>
                      <TableCell>{formatCurrency(discount.amount)}</TableCell>
                      <TableCell>{formatDate(discount.created_at)}</TableCell>
                      <TableCell>
                        {canDeletePassengerDiscounts && (
                          <IconButton
                            aria-label="delete"
                            color="error"
                            size="small"
                            disabled={!editMode}
                            onClick={() => handleOpenDelete(discount.id)}
                          >
                            <Delete fontSize="small" />
                          </IconButton>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          ) : (
            <Typography>No discount found for this passenger.</Typography>
          )}
        </DialogContent>
        <LoadingOverlay open={loading} />
      </Dialog>
      {/* Delete Confirmation */}
      <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
        <DialogTitle>Delete Discount</DialogTitle>
        <DialogContent>
          <Typography>Are you sure you want to delete this discount?</Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setConfirmDeleteOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirmDelete} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default DiscountForm;
