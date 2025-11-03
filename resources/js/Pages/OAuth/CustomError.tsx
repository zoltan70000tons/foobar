import React, { useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { TextField, Button, Box, Container, Typography, Alert } from "@mui/material";
import OAuthLayout from "@/Layouts/OAuthLayout";
import Axios from "axios";
import SurvivorLogin from "@/Pages/OAuth/components/SurvivorLogin";
import { blue } from "@mui/material/colors";
import { styled } from "@mui/material/styles";

type OAuthErrorPageProps = {
  tAuth: any;
  flash: {
    message?: string;
  };
  error?: string;
  error_description?: string;
};

export default function CustomError() {
  const { tAuth, flash, error, error_description } = usePage<OAuthErrorPageProps>().props;

  return (
    <>
      <Head title={tAuth?.error_title ?? "Error"}>
        <meta name="robots" content="noindex" />
      </Head>
      <Box sx={{ width: "100%", py: 6, display: "flex", justifyContent: "center", alignItems: "center" }}>
        <Container maxWidth="sm" sx={{ mt: 8 }}>
          <Typography component="h1" variant="h4" sx={{ mb: 2, fontWeight: "bold" }}>
            {tAuth?.error_title ?? "Error"}
          </Typography>
          {flash.message && (
            <Alert severity="error" sx={{ mb: 2 }}>
              {flash.message}
            </Alert>
          )}
          {error && (
            <Alert severity="error" sx={{ mb: 2 }}>
              {error}
            </Alert>
          )}
          {error_description && (
            <Typography variant="body2" color="text.secondary">
              {error_description}
            </Typography>
          )}
        </Container>
      </Box>
    </>
  );
}

CustomError.layout = (page: React.ReactNode) => <OAuthLayout>{page}</OAuthLayout>;
