import React from 'react';
import Snackbar from '@mui/material/Snackbar';
import Alert from '@mui/material/Alert';

interface SnackAlertProps {
  message: string;
  severity?: "success" | "error" | "warning" | "info";
  open: boolean;
  vertical: string,
  horizontal: string
  onClose: () => void;
}

const SnackbarAlert: React.FC<SnackAlertProps> = ({ message, severity = 'success', open, onClose, vertical='top', horizontal='center' }) => {
  return (
    <Snackbar
      open={open}
      autoHideDuration={6000}
      onClose={onClose}
      anchorOrigin={{ vertical, horizontal }}
      key={vertical + horizontal}
    >
      <Alert onClose={onClose} severity={severity} sx={{ width: '100%' }}>
        {message}
      </Alert>
    </Snackbar>
  );
};

export default SnackbarAlert;
