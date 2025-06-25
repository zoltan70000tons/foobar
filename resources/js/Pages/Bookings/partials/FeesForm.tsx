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
  Paper,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  IconButton, Tooltip,
} from "@mui/material";
import InfoIcon from '@mui/icons-material/Info';

import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import LoadingOverlay from "@/Components/LoadingOverlay";
import PriceChangeIcon from "@mui/icons-material/PriceChange";
import { Passenger } from "@/Pages/Bookings/partials/Payment";
import { formatCurrency, formatDate } from "@/Helpers/stringUtils";
import { Delete } from "@mui/icons-material";
import { getOrdinalName } from "@/Helpers/stringUtils";
import { Installment } from "@/interfaces/Installments";

type FeesFormProps = {
  passenger: Passenger;
  event_id: number;
  booking_id: number;
  editMode: boolean;
};

export type Fee = {
  type: string;
  amount: number;
  created_at: string;
  id: number;
  due_date?: string; // Optional, only if the fee is related to an installment
  notes?: string;
};

const FeesForm: React.FC<FeesFormProps> = ({ passenger, event_id, booking_id, editMode }) => {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState<
    Pick<Fee, "type" | "amount"> & { due_date?: string; installment_id?: number, notes?: string }
  >({
    type: "",
    amount: 0,
    due_date: undefined,
    installment_id: undefined,
    notes: "",
  });
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);
  const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
  const [feeIdToDelete, setFeeIdToDelete] = useState<number | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === "amount" ? parseFloat(value) : value,
    }));
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  const handleOpenDelete = (feeId: number) => {
    setFeeIdToDelete(feeId);
    setConfirmDeleteOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.type || formData.amount <= 0 || !formData.due_date) {
      showSnackbar("Please fill in all fields correctly.", "error");
      return;
    }

    setLoading(true);

    // Send form data to the server using the router
    router.post(
      route("manual.fee", {
        event_id: event_id,
        booking_id: booking_id,
      }),
      {
        passenger_id: passenger.id,
        amount: formData.amount,
        type: formData.type,
        due_date: formData.due_date,
        notes: formData.notes ?? "",
      },
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({ type: "", amount: 0, notes: "", due_date: "", installment_id: 0 });
          setOpen(false);
          showSnackbar("Fee created successfully", "success");
          router.reload({ only: ["user"] });
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error("Validation errors:", errors);
          showSnackbar("Failed to save fee. Please check your inputs.", "error");
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleConfirmDelete = () => {
    if (!feeIdToDelete) return;

    setLoading(true);
    router.post(
      route("fees.delete", { event_id, booking_id }),
      {
        passenger_id: passenger.id,
        fee_id: feeIdToDelete,
      },
      {
        onSuccess: () => {
          showSnackbar("Fee deleted successfully.", "success");
        },
        onError: () => {
          showSnackbar("Could not delete fee.", "error");
        },
        onFinish: () => {
          setLoading(false);
          setConfirmDeleteOpen(false);
          setFeeIdToDelete(null);
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
        startIcon={<PriceChangeIcon />}
      >
        Fees
      </Button>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
        <DialogTitle>Add Fee</DialogTitle>
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
                select
                label="Fee/Installment Plan"
                name="installment_id"
                helperText="This fee must be paid before the selected installment can be processed."
                value={formData.installment_id ?? ""}
                required
                onChange={(e) => {
                  const selected = passenger.installments.find((i) => i.id === Number(e.target.value));
                  if (selected) {
                    const dueDate = new Date(selected.due_date);
                    dueDate.setDate(dueDate.getDate() - 1);
                    const formattedDueDate = dueDate.toISOString().split("T")[0]; // yyyy-mm-dd

                    setFormData((prev) => ({
                      ...prev,
                      installment_id: selected.id,
                      due_date: formattedDueDate,
                    }));
                  }
                }}
                fullWidth
                margin="normal"
              >
                {passenger.installments
                  .filter((install) => install.type === "PAYMENT")
                  .sort((a: Installment, b: Installment) => new Date(a.due_date).getTime() - new Date(b.due_date).getTime())
                  .map((inst, index) => (
                    <MenuItem key={inst.id} value={inst.id} sx={{ textTransform: "capitalize" }}>
                      {`${getOrdinalName(index + 1)} Installment - Due: ${formatDate(inst.due_date)}`}
                    </MenuItem>
                  ))}
              </TextField>
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
            Fee History for {passenger?.full_name || "Unknown Passenger"}
          </Typography>

          {passenger.fees.length > 0 ? (
            <TableContainer component={Paper}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Type</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Created At</TableCell>
                    <TableCell>Delete</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {passenger.fees.map((fee) => (
                    <TableRow key={fee.id}>
                      <TableCell>
                        {fee.type}
                        <Tooltip title={fee.notes}>
                          <IconButton>
                            <InfoIcon fontSize={"small"} />
                          </IconButton>
                        </Tooltip>
                      </TableCell>
                      <TableCell>{formatCurrency(fee.amount)}</TableCell>
                      <TableCell>{formatDate(fee.created_at)}</TableCell>
                      <TableCell>
                        <IconButton
                          aria-label="delete"
                          color="error"
                          size="small"
                          disabled={!editMode}
                          onClick={() => handleOpenDelete(fee.id)}
                        >
                          <Delete fontSize="small" />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          ) : (
            <Typography>No fee found for this passenger.</Typography>
          )}
        </DialogContent>
        <LoadingOverlay open={loading} />
      </Dialog>
      {/* Delete Confirmation */}
      <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
        <DialogTitle>Delete Fee</DialogTitle>
        <DialogContent>
          <Typography>Are you sure you want to delete this fee?</Typography>
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

export default FeesForm;
