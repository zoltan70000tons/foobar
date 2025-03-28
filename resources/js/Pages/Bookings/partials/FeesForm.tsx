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

import { router } from '@inertiajs/react';
import { sanitizeInput } from '@/Helpers/inputSanitizer';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';
import { usePermissions } from '@/Providers/PermissionContext';
import { Permissions } from '@/enums/PermissionEnum';
import LoadingOverlay from '@/Components/LoadingOverlay';
import PriceChangeIcon from '@mui/icons-material/PriceChange';

type FeesFormProps = {
  passenger_id: number;
  event_id: number;
  booking_id: number;
  editMode: boolean;
};

type Fee = {
  type: string;
  amount: number;
};

const FeesForm: React.FC<FeesFormProps> = ({ passenger_id, event_id, booking_id, editMode }) => {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState<Fee>({ type: '', amount: 0 });
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'amount' ? parseFloat(value) : value,
    }));
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.type || formData.amount <= 0) {
      showSnackbar('Please fill in all fields correctly.', 'error');
      return;
    }

    setLoading(true);

    // Send form data to the server using the router
    router.post(
      route('manual.fee', {
        event_id: event_id,
        booking_id: booking_id,
      }),
      {
        passenger_id,
        amount: formData.amount,
        type: formData.type,
      },
      {
        onSuccess: () => {
          // Reset form and close dialog only on success
          setFormData({ type: '', amount: 0 });
          setOpen(false);
          showSnackbar('Fee created successfully', 'success');
          router.reload({ only: ['user'] });
        },
        onError: (errors) => {
          // Log or display errors if needed
          console.error('Validation errors:', errors);
          showSnackbar('Failed to save fee. Please check your inputs.', 'error');
        },
        onFinish: () => {
          setLoading(false);
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
        startIcon={<PriceChangeIcon />}
      >
        Add Fee
      </Button>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
        <DialogTitle>Add Fee</DialogTitle>
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
        <LoadingOverlay open={loading} />
      </Dialog>
    </Box>
  );
};

export default FeesForm;
