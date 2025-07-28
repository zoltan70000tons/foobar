import React from 'react';
import { Box, Typography, Toolbar, Stack, AppBar, CssBaseline } from '@mui/material';
import Logo from '../Components/Logo';
import Footer from '@/Pages/OAuth/components/Footer';
import themeClient from '@/Theme/themeClient';
import { styled, ThemeProvider } from "@mui/material/styles";

interface Props {
  children: React.ReactNode;
}

const defaultTheme = themeClient;

const OAuthLayout: React.FC<Props> = ({ children }) => {
  return (
      <ThemeProvider theme={defaultTheme}>
        <CssBaseline />
        <AppBar
          position="static"
          sx={{
            position: "relative",
            zIndex: 1001,
          }}
        >
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
        </AppBar>
        <Box
          sx={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            minHeight: '100vh',
            backgroundColor: '#121212',
          }}
        >
          {children}
        </Box>
        <Footer />
      </ThemeProvider>
  );
};

export default OAuthLayout;