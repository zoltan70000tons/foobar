import { Box, Typography } from '@mui/material';

export default function RecoverAccount() {
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
        Recover Your Account
      </Typography>
      <Typography variant="body1" gutterBottom>
        Please enter your email address to receive a password reset link.
      </Typography>
    </Box>
  );
} 