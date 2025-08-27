import React, { useEffect, useState } from "react";
import {
  TextField,
  MenuItem,
  Button,
  Typography,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  IconButton,
  Paper,
  Divider,
  Tooltip,
  Box,
} from "@mui/material";
import { DatePicker } from "@mui/x-date-pickers";
import { router } from "@inertiajs/react";
import dayjs, { Dayjs } from "dayjs";
import { Warning, Delete } from "@mui/icons-material";
import { sanitizeInput } from "@/Helpers/inputSanitizer";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { formatCurrency } from "@/Helpers/stringUtils";
import { Passenger } from "@/interfaces/Passenger";

type PaymentModalProps = {
  passenger: Passenger;
  booking_id: number;
  event_id: number;
  editMode: boolean;
  paymentHistory: {
    id: number;
    type: string;
    amount: number;
    transaction_date: string;
    BIP_ID: string;
    source: string;
    splitAmount: boolean;
  }[];
  open: boolean;
  onClose: () => void;
};

type Payment = {
  id: number;
  BIP_ID: string;
  amount: number;
  type: "PAYMENT" | "REFUND";
  notes?: string;
  transaction_date: Dayjs | null;
  splitAmount: boolean;
};

enum PaymentType {
  PAYMENT = "Payment",
  REFUND = "Refund",
}

const PaymentModal: React.FC<PaymentModalProps> = ({
  passenger,
  booking_id,
  event_id,
  editMode,
  paymentHistory,
  open,
  onClose,
}) => {
  const [loading, setLoading] = useState(false);
  const [selectedPaymentId, setSelectedPaymentId] = useState<number | null>(null);
  const [selectedPaymentSplitAmount, setSelectedPaymentSplitAmount] = useState<boolean>(false);
  const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
  const [localPaymentHistory, setLocalPaymentHistory] = useState(paymentHistory);

  useEffect(() => {
    setLocalPaymentHistory(paymentHistory);
  }, [paymentHistory])

  const { showSnackbar } = useSnackbar();

  const [formData, setFormData] = useState<Payment>({
    BIP_ID: "",
    amount: 0,
    type: "PAYMENT",
    notes: "",
    transaction_date: dayjs(),
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === "amount" ? parseFloat(value) : sanitizeInput(value),
    }));
  };

  const handleDateChange = (newDate: Dayjs | null) => {
    setFormData((prev) => ({
      ...prev,
      transaction_date: newDate,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.BIP_ID || formData.amount <= 0) {
      showSnackbar("Please fill in all fields correctly.", "error");
      return;
    }

    setLoading(true);
    router.post(
      route("manual.payment", { event_id, booking_id }),
      {
        passenger_id: passenger.id,
        BIP_ID: formData.BIP_ID,
        amount: formData.amount,
        type: formData.type,
        notes: formData.notes,
        transaction_date: formData.transaction_date?.format("YYYY-MM-DD"),
      },
      {
        onSuccess: () => {
          showSnackbar(`${PaymentType[formData.type.toUpperCase()]} created successfully`, "success");
          setFormData({
            BIP_ID: "",
            amount: 0,
            type: "PAYMENT",
            notes: "",
            transaction_date: dayjs(),
          });
          onClose();
        },
        onError: (err) => {
          console.error(err);
          showSnackbar(`Failed to save the ${PaymentType[formData.type.toUpperCase()]}.`, "error");
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleOpenDelete = (payment: Payment) => {
    const { id, splitAmount } = payment;

    setSelectedPaymentId(id);
    setSelectedPaymentSplitAmount(splitAmount);
    setConfirmDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!selectedPaymentId) return;

    setLoading(true);
    router.post(
      route("payments.delete", { event_id, booking_id }),
      {
        passenger_id: passenger.id,
        payment_id: selectedPaymentId,
      },
      {
        onSuccess: () => {
          setLocalPaymentHistory(prevHistory =>
            prevHistory.filter(payment => payment.id !== selectedPaymentId)
          );

          showSnackbar("Payment deleted successfully.", "success");
        },
        onError: () => {
          showSnackbar("Could not delete payment.", "error");
        },
        onFinish: () => {
          setLoading(false);
          setConfirmDeleteOpen(false);
          setSelectedPaymentId(null);
        },
      },
    );
  };

  return (
    <>
      <Dialog open={open} onClose={onClose} fullWidth maxWidth="md" >
        <DialogTitle>Payments and Refunds</DialogTitle>
        <DialogContent>
          <form onSubmit={handleSubmit}>
            <Grid container spacing={2} mt={1}>
              <Grid item xs={12} md={6}>
                <TextField
                  label="BIP ID"
                  name="BIP_ID"
                  value={formData.BIP_ID}
                  onChange={handleChange}
                  fullWidth
                  required
                />
              </Grid>
              <Grid item xs={6} md={3}>
                <TextField
                  label="Amount"
                  name="amount"
                  type="number"
                  value={formData.amount}
                  onChange={handleChange}
                  fullWidth
                  inputProps={{ step: 0.01, min: 0 }}
                  required
                />
              </Grid>
              <Grid item xs={6} md={3}>
                <TextField
                  select
                  label="Type"
                  name="type"
                  value={formData.type}
                  onChange={handleChange}
                  fullWidth
                  required
                >
                  <MenuItem value="PAYMENT">Payment</MenuItem>
                  <MenuItem value="REFUND">Refund</MenuItem>
                </TextField>
              </Grid>
              <Grid item xs={12} md={6}>
                <DatePicker
                  label="Transaction Date"
                  value={formData.transaction_date}
                  onChange={handleDateChange}
                  slotProps={{ textField: { fullWidth: true, required: true } }}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  label="Notes"
                  name="notes"
                  value={formData.notes}
                  onChange={handleChange}
                  fullWidth
                  multiline
                  rows={3}
                  inputProps={{ maxLength: 255 }}
                />
              </Grid>
            </Grid>

            <DialogActions>
              <Button onClick={onClose} color="secondary">
                Cancel
              </Button>
              <Button type="submit" color="primary" variant="contained" disabled={loading}>
                Save
              </Button>
            </DialogActions>
          </form>

          <Divider sx={{ my: 3 }} />

          <Typography variant="h6" gutterBottom>
            Payment History for {passenger?.full_name || "Unknown Passenger"}
          </Typography>

          {localPaymentHistory.length > 0 ? (
            <TableContainer component={Paper}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Type</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Date</TableCell>
                    <TableCell>BIP ID</TableCell>
                    <TableCell>Source</TableCell>
                    <TableCell>Delete</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {localPaymentHistory.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell>{payment.type}</TableCell>
                      <TableCell>
                        {payment.type === "PAYMENT" ? "+" : payment.type === "REFUND" ? "-" : ""}
                        {formatCurrency(payment.amount)}
                      </TableCell>
                      <TableCell>{new Date(payment.transaction_date).toLocaleDateString()}</TableCell>
                      <TableCell>{payment.BIP_ID || "N/A"}</TableCell>
                      <TableCell>{payment.source || "N/A"}</TableCell>
                      <TableCell>
                        {payment.source === "MANUAL" && (
                          <IconButton
                            aria-label="delete"
                            color="error"
                            size="small"
                            disabled={!editMode}
                            onClick={() => handleOpenDelete(payment)}
                          >
                            <Delete fontSize="small" />
                          </IconButton>
                        )}
                        {payment.splitAmount && (
                          <Tooltip title="Split Payment!">
                            <IconButton color="warning">
                              <Warning />
                            </IconButton>
                          </Tooltip>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          ) : (
            <Typography>No payments found for this passenger.</Typography>
          )}
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation */}
      <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
        <DialogTitle>Delete Payment</DialogTitle>
        <DialogContent>
          <Typography>Are you sure you want to delete this payment?</Typography>
          {selectedPaymentSplitAmount && (
            <Box display="flex" justifyContent="center" alignItems="center" mt={2}>
              <IconButton color="warning">
                <Warning />
              </IconButton>
              <Typography>
                The payment you are about to delete is part of a split payment. Deleting this payment will result in deleting all that share the same Transaction ID!
              </Typography>
            </Box>
          )}
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
    </>
  );
};

export default PaymentModal;
