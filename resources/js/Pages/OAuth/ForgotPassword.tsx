import { Box, Typography } from '@mui/material';

export default function ForgotPassword() {
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        minHeight: '100vh',
        backgroundColor: '#f5f5f5',
      }}
    >
      <Typography variant="h4" gutterBottom>
        Forgot Your Password?
      </Typography>
      <Typography variant="body1" gutterBottom>
        Please enter your email address to receive a password reset link.
      </Typography>
    </Box>
  );
} 