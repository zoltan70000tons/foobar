import { Box, Typography } from '@mui/material';

export default function VerifyEmail() {
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
        Verify Your Email
      </Typography>
      <Typography variant="body1" gutterBottom>
        Please check your email for a verification link.
      </Typography>
    </Box>
  );
} 