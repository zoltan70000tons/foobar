import React from 'react';
import Snackbar from '@mui/material/Snackbar';
import MuiAlert, { AlertProps } from '@mui/material/Alert';
import Slide from '@mui/material/Slide';
import { CheckCircle, Error, Info, Warning } from '@mui/icons-material';
import { Box, Typography } from '@mui/material';

const icons = {
  success: <CheckCircle fontSize="inherit" />,
  error: <Error fontSize="inherit" />,
  warning: <Warning fontSize="inherit" />,
  info: <Info fontSize="inherit" />,
};

interface SnackAlertProps {
  message: string;
  severity?: 'success' | 'error' | 'warning' | 'info';
  open: boolean;
  vertical?: 'top' | 'bottom';
  horizontal?: 'left' | 'center' | 'right';
  onClose: () => void;
}

const Alert = React.forwardRef<HTMLDivElement, AlertProps>(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const SnackbarAlert: React.FC<SnackAlertProps> = ({
  message,
  severity = 'success',
  open,
  onClose,
  vertical = 'top',
  horizontal = 'center',
}) => {
  return (
    <Snackbar
      open={open}
      autoHideDuration={5000}
      onClose={onClose}
      anchorOrigin={{ vertical, horizontal }}
      TransitionComponent={(props) => <Slide {...props} direction="down" />}
      key={vertical + horizontal}
    >
      <Alert
        onClose={onClose}
        severity={severity}
        icon={icons[severity]}
        sx={{
          width: '100%',
          bgcolor: 'background.paper',
          color: 'text.primary',
          borderRadius: 3,
          boxShadow: 3,
          px: 2,
          py: 1.5,
        }}
      >
        <Box>
          {message.split('\n').map((line, index) => (
            <Typography key={index} variant="body2" sx={{ lineHeight: 1.6 }}>
              {line}
            </Typography>
          ))}
        </Box>
      </Alert>
    </Snackbar>
  );
};

export default SnackbarAlert;
