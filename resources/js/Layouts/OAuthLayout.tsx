import React from 'react';
import { Box, Typography, Toolbar, Stack } from '@mui/material';
import Logo from '../Components/Logo';

interface Props {
  children: React.ReactNode;
}

const OAuthLayout: React.FC<Props> = ({ children }) => {
  return (
    <>
    <Toolbar
      sx={{
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        zIndex: 1002,
      }}
    >
      <Stack direction="row" spacing={5} alignItems="center">
        <Logo />
        Menu items
      </Stack>
    </Toolbar>
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
    </>
  );
};

export default OAuthLayout;