import { useState } from 'react';
import { 
  Button, 
  Box, 
  Typography,
  Dialog,
  DialogTitle,
  DialogActions,
  DialogContent, 
  Chip,
} from '@mui/material';
import axios from "axios";
import { green } from '@mui/material/colors';
import { router } from '@inertiajs/react';
import { Customer } from '@/interfaces/Customer';

type Props = {
  customer: Customer;
  isTemporaryPassword: boolean;
}


export default function TemporaryPassword({customer, isTemporaryPassword}: Props) {

  const customerId = customer.id;

  const [dialogOpen, setDialogOpen] = useState(false);
  const [temporaryPassword, setTemporaryPassword] = useState(null);
  const [loading, setLoading] = useState(false);

  const generateTemporaryPassword = async () => {
    setLoading(true);

    axios.post(route('customers.temporaryPassword.create', { user: customerId }), {
        customer_id: customerId
    })
      .then(response => {
        setTemporaryPassword(response.data.raw_password);
        setLoading(false);
      })
      .catch(error => {
        console.error("Error generating temporary password:", error);
        setLoading(false);
      });
  };

  const deleteTemporaryPassword = async () => {
    setLoading(true);

    axios.delete(route('customers.temporaryPassword.delete', { user: customerId }))
      .then(response => {
        setTemporaryPassword(null);
        setLoading(false);
        router.reload();
      })
      .catch(error => {
        console.error("Error deleting temporary password:", error);
        setLoading(false);
      });
  }

  return (
    <>
      <Box
        sx={{
          display: 'flex',
          alignItems: 'center',
          gap: 1,
        }}
      >
        {(isTemporaryPassword || temporaryPassword) && (
          <Chip
            label="Temporary Password Active"
            color="error"
          />
        )}
        <Button
          variant="outlined"
          color="primary"
          onClick={() => setDialogOpen(true)}
        >
          Temporary Password
        </Button>
      </Box>
      <Dialog
        open={dialogOpen}
        onClose={() => setDialogOpen(false)}
        aria-labelledby="temporary-password-dialog-title"
        aria-describedby="temporary-password-dialog-description"
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle id="temporary-password-dialog-title">
          Temporary Password
        </DialogTitle>
        <DialogContent>
          {isTemporaryPassword && (
            <Typography variant="body1" color="textSecondary" gutterBottom>
              A temporary password has already been generated for this customer. Delete first to generate a new one.
            </Typography>
          )}
          {!temporaryPassword ? (
            <Typography variant="body1" color="textSecondary" gutterBottom>
              No temporary password.
            </Typography>
          ) : (
          <Typography variant="body1">
            Temporary Password: {" "}
            <Box component={'span'} 
              sx={{
              color: green[200],
              }}
            >
              {temporaryPassword}
            </Box>
          </Typography>
        )}
        </DialogContent>
        <DialogActions>
          {temporaryPassword || isTemporaryPassword ? (
            <Button 
              disabled={loading}
              variant="outlined"
              color="error"
              onClick={deleteTemporaryPassword}
            >
              Delete Temporary Password
            </Button>
          ) : (
            <Button 
              variant="contained"
              disabled={loading}
              color="primary"
              onClick={generateTemporaryPassword}
            >
              Generate Temporary Password
            </Button>
          )}
          <Button onClick={() => setDialogOpen(false)} color="primary">
            Close
          </Button>
        </DialogActions>
      </Dialog>
    </>
  )
}
