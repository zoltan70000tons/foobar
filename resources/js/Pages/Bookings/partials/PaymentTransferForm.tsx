import React, { useEffect, useState } from 'react';
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
  Paper,
  Divider,
  TableContainer,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  IconButton,
  Select,
} from '@mui/material';

import { router } from '@inertiajs/react';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import { usePermissions } from '@/Providers/PermissionContext';
import { Permissions } from '@/enums/PermissionEnum';
import LoadingOverlay from '@/Components/LoadingOverlay';
import AccountBalanceWalletIcon from '@mui/icons-material/AccountBalanceWallet';
import { formatDate , formatCurrency } from "@/Helpers/stringUtils";
import { Delete } from "@mui/icons-material";
import Payment, { Booking, Passenger } from "@/Pages/Bookings/partials/Payment";

type PaymentTransferFormProps = {
  passenger: Passenger;
  booking: Booking;
  editMode: boolean;
};

type PaymentTransferFormData = {
  amount: number;
};

type CreatePaymentTransferRequest = {
  passenger_id: number;
  amount: number;
  transfer_to_passenger: number;
}

export type PaymentTransfer = {
  id: number;
  created_at: string;
  passenger_id_from: number;
  passenger_id_to: number;
  payment_id_from: number;
  payment_id_to: number;
}

const PaymentTransferForm: React.FC<PaymentTransferFormProps> = ({ passenger, booking, editMode }) => {
  const passengers: Passenger[] = booking.passengers;
  const openedPassenger = passenger;
  const otherPassengers: Passenger[] = passengers.filter((pax: Passenger) => pax.id != openedPassenger.id);

  const [selectedPassengerId, setSelectedPassengerId] = useState(otherPassengers?.[0].id);

  const [transferableBalance, setTransferableBalance] = useState(
    Math.max(0, parseFloat(openedPassenger.passenger_balance) - parseFloat(openedPassenger.passenger_allocated_cost))
  );

  const [manualTransfers, setManualTransfers] = useState<Payment[]>([]);

  useEffect(() => {
    const transfers = openedPassenger.payments.filter((payment) => payment.type === 'TRANSFER')
    setManualTransfers(transfers);
  }, [openedPassenger]);

  useEffect(() => {
    setTransferableBalance(Math.max(0, parseFloat(openedPassenger.passenger_balance) - parseFloat(openedPassenger.passenger_allocated_cost)));
  }, [passenger, openedPassenger]);

  const [open, setOpen] = useState(false);
  const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
  const [transferIdToDelete, setTransferIdToDelete] = useState(null);
  const [formData, setFormData] = useState<PaymentTransferFormData>({ amount: 0 });
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);
  const { hasPermission } = usePermissions();
  const canDeleteTransfer = hasPermission(Permissions.DeletePayments);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;

    setFormData((prev) => {
      const newValue = name === 'amount' ? parseFloat(value) : value;
      return {
        ...prev,
        [name]: name === 'amount' ? Math.min(newValue as number, transferableBalance) : newValue, // Limit value to transferableBalance
      };
    });
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPassengerId || formData.amount <= 0) {
      showSnackbar('Please fill in all fields correctly.', 'error');
      return;
    }

    setLoading(true);

    // Send form data to the server using the router
    router.post(
      route('manual.payment-transfer', {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      {
        passenger_id: passenger.id,
        amount: formData.amount,
        transfer_to_passenger: selectedPassengerId,
      } as CreatePaymentTransferRequest,
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({ amount: 0 });
          setOpen(false);
          showSnackbar('Payment transferred successfully', 'success');
          router.reload({ only: ['user'] });
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error('Validation errors:', errors);
          showSnackbar('Failed to save payment transfer. Please check your inputs.', 'error');
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleOpenDelete = (paymentTransferId) => {
    setTransferIdToDelete(paymentTransferId);
    setConfirmDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!transferIdToDelete) return;

    setLoading(true);
    router.post(
      route('delete.payment-transfer', {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      {
        transfer_id: transferIdToDelete,
      },
      {
        onSuccess: () => {
          showSnackbar("Payment transfer deleted successfully.", "success");
          router.reload({ only: ['passenger', 'booking'] });
        },
        onError: () => {
          showSnackbar("Could not delete payment transfer.", "error");
        },
        onFinish: () => {
          setLoading(false);
          setConfirmDeleteOpen(false);
          setTransferIdToDelete(null);
        },
      },
    );
  };

  return (
    <Box>
      <Button
        fullWidth
        variant="outlined"
        sx={{ color: 'white', borderColor: 'gray' }}
        onClick={handleOpen}
        disabled={!editMode}
        startIcon={<AccountBalanceWalletIcon />}
      >
        Add Payment Transfer
      </Button>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
        <DialogTitle>Add Payment Transfer</DialogTitle>
        <DialogContent>
          <form onSubmit={handleSubmit}>
            <Box component="form" onSubmit={handleSubmit}>
              <Typography variant="div">
                Transferable amount: {transferableBalance}
              </Typography>
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
                sx={{ mb: 2 }}
                disabled={transferableBalance === 0}
              />
              <Select
                labelId="transfer-label"
                value={selectedPassengerId}
                onChange={(e) => setSelectedPassengerId(e.target.value)}
                label="Passenger to transfer to"
                fullWidth
                sx={{ mb: 2 }}
              >
                {Object.values(otherPassengers).map((pax: Passenger) => (
                  <MenuItem key={pax.id} value={pax.id}>
                    {pax.full_name}
                  </MenuItem>
                ))}
              </Select>
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
            Payment Transfer History for {passenger?.full_name || "Unknown Passenger"}
          </Typography>

          {manualTransfers.length > 0 ? (
            <TableContainer component={Paper}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Payment From</TableCell>
                    <TableCell>Payment To</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Created At</TableCell>
                    <TableCell>Delete</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {manualTransfers.map((transfer) => {
                    const manualTransfer: PaymentTransfer = transfer.payment_transfer_from ?? transfer.payment_transfer_to!;

                    return (
                      <TableRow key={transfer.id}>
                        <TableCell>{booking.passengers.find(passenger => passenger.id === manualTransfer.passenger_id_from)?.full_name || 'Unknown'}</TableCell>
                        <TableCell>{booking.passengers.find(passenger => passenger.id === manualTransfer.passenger_id_to)?.full_name || 'Unknown'}</TableCell>
                        <TableCell>
                          {formatCurrency(transfer.amount)}
                        </TableCell>
                        <TableCell>{formatDate(transfer.created_at)}</TableCell>
                        <TableCell>
                          {canDeleteTransfer && (
                            <IconButton
                              aria-label="delete"
                              color="error"
                              size="small"
                              disabled={!editMode}
                              onClick={() => handleOpenDelete(manualTransfer.id)}
                            >
                              <Delete fontSize="small" />
                            </IconButton>
                          )}
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </TableContainer>
          ) : (
            <Typography>No payment transfer found for this passenger.</Typography>
          )}
        </DialogContent>
        <LoadingOverlay open={loading} />
      </Dialog>
      {/* Delete Confirmation */}
      <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
        <DialogTitle>Delete Payment Transfer</DialogTitle>
        <DialogContent>
          <Typography>Are you sure you want to delete this transfer?</Typography>
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

export default PaymentTransferForm;
