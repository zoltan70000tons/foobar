import React from 'react';
import { Box, Typography } from '@mui/material';

interface Props {
  children: React.ReactNode;
}

const OAuthLayout: React.FC<Props> = ({ children }) => {
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
        OAuth Authentication
      </Typography>
      {children}
    </Box>
  );
};

export default OAuthLayout;