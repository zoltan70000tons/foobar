import React from 'react';
import { Box, Typography, Toolbar, Stack, AppBar, CssBaseline } from '@mui/material';
import Logo from '../Components/Logo';
import Footer from '@/Pages/OAuth/components/Footer';
import themeClient from '@/Theme/themeClient';
import { styled, ThemeProvider } from "@mui/material/styles";
import TopNavigation from '@/Pages/OAuth/components/TopNavigation';

interface Props {
  children: React.ReactNode;
}

const defaultTheme = themeClient;

const OAuthLayout: React.FC<Props> = ({ children }) => {

  // get language param from url
  const urlParams = new URLSearchParams(window.location.search);
  const language = urlParams.get("language") || "en";

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
          <Stack direction="row" spacing={5} alignItems="center" sx={{ width: '100%', justifyContent: 'space-between' }}>
            <Logo language={language} />
            <TopNavigation language={language} />
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