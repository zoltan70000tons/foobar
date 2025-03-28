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
  Grid,
} from '@mui/material';
import { DatePicker } from '@mui/x-date-pickers';
import { router } from '@inertiajs/react';
import dayjs, { Dayjs } from 'dayjs';
import { sanitizeInput } from '@/Helpers/inputSanitizer';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import LoadingOverlay from '@/Components/LoadingOverlay';
import PaymentIcon from '@mui/icons-material/Payment';

type PaymentModalProps = {
  passenger_id: number;
  booking_id: number;
  event_id: number;
  editMode: boolean;
};

type Payment = {
  BIP_ID: string;
  amount: number;
  type: 'PAYMENT' | 'REFUND';
  notes?: string;
  transaction_date: Dayjs | null;
};

const PaymentModal: React.FC<PaymentModalProps> = ({ passenger_id, booking_id, event_id, editMode }) => {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState<Payment>({
    BIP_ID: '',
    amount: 0,
    type: 'PAYMENT',
    notes: '',
    transaction_date: dayjs(), // Default to today
  });
  const [loading, setLoading] = useState(false);

  const { showSnackbar } = useSnackbar();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;

    setFormData((prev) => ({
      ...prev,
      [name]: name === 'amount' ? parseFloat(value) : sanitizeInput(value),
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
      showSnackbar('Please fill in all fields correctly.', 'error');
      return;
    }

    setLoading(true);

    // Send form data to the server using the router
    router.post(
      route('manual.payment', {
        event_id: event_id,
        booking_id: booking_id,
      }),
      {
        passenger_id,
        BIP_ID: formData.BIP_ID,
        amount: formData.amount,
        type: formData.type,
        notes: formData.notes,
        transaction_date: formData.transaction_date?.format('YYYY-MM-DD'), // Format the date for Laravel
      },
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({
            BIP_ID: '',
            amount: 0,
            type: 'PAYMENT',
            notes: '',
            transaction_date: dayjs(),
          });
          showSnackbar('Payment created successfully', 'success');
          setOpen(false);
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error('Validation errors:', errors);
          showSnackbar('Failed to save the payment. Please check your inputs.', 'error');
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  return (
    <Box>
      <Button
        fullWidth
        variant="outlined"
        sx={{ color: 'white', borderColor: 'gray' }}
        onClick={handleOpen}
        disabled={!editMode}
        startIcon={<PaymentIcon />}
      >
        Add Payment/Refund
      </Button>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="md">
        <DialogTitle>Add Payment</DialogTitle>
        <DialogContent>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                label="BIP ID"
                name="BIP_ID"
                type="text"
                value={formData.BIP_ID}
                onChange={handleChange}
                fullWidth
                margin="normal"
                required
              />
            </Grid>
            <Grid item xs={12} md={4}>
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
            </Grid>
            <Grid item xs={12} md={4}>
              <TextField
                select
                label="Type"
                name="type"
                value={formData.type}
                onChange={handleChange}
                fullWidth
                margin="normal"
                required
              >
                <MenuItem value="PAYMENT">Payment</MenuItem>
                <MenuItem value="REFUND">Refund</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} md={4}>
              <DatePicker
                sx={{ mt: '1rem', width: '100%' }}
                label="Transaction Date"
                value={formData.transaction_date}
                onChange={handleDateChange}
                renderInput={(params) => <TextField {...params} fullWidth margin="normal" required />}
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Notes"
                name="notes"
                value={formData.notes}
                onChange={handleChange}
                fullWidth
                margin="normal"
                multiline
                rows={4}
                inputProps={{ maxLength: 255 }}
                helperText={`${formData.notes?.length || 0}/255`}
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button variant="outlined" color="secondary" onClick={handleClose}>
            Cancel
          </Button>
          <Button type="submit" variant="contained" color="primary" onClick={handleSubmit}>
            Save
          </Button>
        </DialogActions>
        {/* <LoadingOverlay open={loading} /> */}
      </Dialog>
    </Box>
  );
};

export default PaymentModal;
