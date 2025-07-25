import { Box, Typography } from '@mui/material';

export default function Register() {
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
        Register
      </Typography>
      <Typography variant="body1" gutterBottom>
        Please fill in the form below to create an account.
      </Typography>
    </Box>
  );
} 