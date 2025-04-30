import React, { useState } from 'react';
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
  Grid, Paper, Divider, TableContainer, Table, TableHead, TableRow, TableCell, TableBody, IconButton,
} from '@mui/material';

import { router } from '@inertiajs/react';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import { usePermissions } from '@/Providers/PermissionContext';
import { Permissions } from '@/enums/PermissionEnum';
import LoadingOverlay from '@/Components/LoadingOverlay';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import AccountBalanceWalletIcon from '@mui/icons-material/AccountBalanceWallet';
import { formatDate , formatCurrency } from "@/Helpers/stringUtils";
import { Delete } from "@mui/icons-material";

type OnboardCredit = {
  id: number;
  reason: string;
  amount: number;
  passenger_id: number;
  created_at: string;
}

type Passenger = {
  id: number;
  full_name?: string;
  onboard_credits: OnboardCredit[];
}

type OnboardCreditFormProps = {
  passenger: Passenger;
  event_id: number;
  booking_id: number;
  editMode: boolean;
};

type OnboardCreditFormData = {
  reason: string;
  amount: number;
};

const OnboardCreditForm: React.FC<OnboardCreditFormProps> = ({ passenger, event_id, booking_id, editMode }) => {
  const [open, setOpen] = useState(false);
  const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
  const [creditIdToDelete, setCreditIdToDelete] = useState(null);
  const [formData, setFormData] = useState<OnboardCreditFormData>({ reason: '', amount: 0 });
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);

  const onboardCredit = passenger.onboard_credits;

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    console.log(e.target);
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'amount' ? parseFloat(value) : value,
    }));
    console.log(formData);
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.reason || formData.amount <= 0) {
      showSnackbar('Please fill in all fields correctly.', 'error');
      return;
    }

    setLoading(true);

    // Send form data to the server using the router
    router.post(
      route('manual.onboard-credit', {
        event_id: event_id,
        booking_id: booking_id,
      }),
      {
        passenger_id: passenger.id,
        amount: formData.amount,
        reason: formData.reason,
      },
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({ reason: '', amount: 0 });
          setOpen(false);
          showSnackbar('Onboard credit created successfully', 'success');
          router.reload({ only: ['user'] });
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error('Validation errors:', errors);
          showSnackbar('Failed to save onboard credit. Please check your inputs.', 'error');
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleOpenDelete = (onboardCreditId) => {
    setCreditIdToDelete(onboardCreditId);
    setConfirmDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!creditIdToDelete) return;

    setLoading(true);
    router.post(
      route("delete.onboard-credit", { event_id, booking_id }),
      {
        passenger_id: passenger.id,
        onboard_credit_id: creditIdToDelete,
      },
      {
        onSuccess: () => {
          showSnackbar("Onboard credit deleted successfully.", "success");
        },
        onError: () => {
          showSnackbar("Could not delete onboard credit.", "error");
        },
        onFinish: () => {
          setLoading(false);
          setConfirmDeleteOpen(false);
          setCreditIdToDelete(null);
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
        Add Onboard Credit
      </Button>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
        <DialogTitle>Add Onboard Credit</DialogTitle>
        <DialogContent>
          <Box component="form" onSubmit={handleSubmit}>
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
              label="Reason"
              name="reason"
              type="text"
              value={formData.reason}
              onChange={handleChange}
              fullWidth
              margin="normal"
              required
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Grid container spacing={2} sx={{ px: 2 }}>
            <Grid item xs={6}>
              <Button variant="outlined" color="secondary" fullWidth onClick={handleClose}>
                Cancel
              </Button>
            </Grid>
            <Grid item xs={6}>
              <Button type="submit" variant="contained" color="primary" fullWidth onClick={handleSubmit}>
                Save
              </Button>
            </Grid>
          </Grid>
        </DialogActions>
        <DialogContent>
          <Divider sx={{ my: 3 }} />

          <Typography variant="h6" gutterBottom>
            Onboard Credit History for {passenger?.full_name || "Unknown Passenger"}
          </Typography>

          {onboardCredit.length > 0 ? (
            <TableContainer component={Paper}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Reason</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Created At</TableCell>
                    <TableCell>Delete</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {onboardCredit.map((credit) => (
                    <TableRow key={credit.id}>
                      <TableCell>{credit.reason}</TableCell>
                      <TableCell>
                        {formatCurrency(credit.amount)}
                      </TableCell>
                      <TableCell>{formatDate(credit.created_at)}</TableCell>
                      <TableCell>
                        <IconButton
                          aria-label="delete"
                          color="error"
                          size="small"
                          disabled={!editMode}
                          onClick={() => handleOpenDelete(credit.id)}
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
            <Typography>No onboard credit found for this passenger.</Typography>
          )}
        </DialogContent>
        <LoadingOverlay open={loading} />
      </Dialog>
      {/* Delete Confirmation */}
      <Dialog open={confirmDeleteOpen} onClose={() => setConfirmDeleteOpen(false)}>
        <DialogTitle>Delete Onboard Credit</DialogTitle>
        <DialogContent>
          <Typography>Are you sure you want to delete this credit?</Typography>
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

export default OnboardCreditForm;
