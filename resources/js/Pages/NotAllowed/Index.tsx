import * as React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps } from "@/types";
import { Head, router, useForm } from "@inertiajs/react";
import {
  Box,
  Button,
  Container,
  Grid,
  SelectChangeEvent,
  TextField,
  Toolbar,
  Typography,
  Alert, // Import the Alert component from MUI
} from "@mui/material";

const Index = ({ auth }: PageProps) => {
  return (
    <AuthenticatedLayout user={auth.user} header="Not Allowed">
      <Head title="Not Allowed" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Alert severity="error" sx={{ mb: 2 }}>
          You do not have access to perform this action. Please contact your administrator.
        </Alert>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
