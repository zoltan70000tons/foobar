import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { TextField, Button, Box, Container, Typography } from '@mui/material';
import OAuthLayout from '@/Layouts/OAuthLayout';
import Axios from 'axios';
import SurvivorLogin from '@/Pages/OAuth/components/SurvivorLogin';
import { blue } from '@mui/material/colors';
import { styled } from '@mui/material/styles';


export default function Login() {
 const frontURL = import.meta.env.VITE_FRONTEND_URL;

  const [form, setForm] = useState({
    email: '',
    password: '',
    ...Object.fromEntries(new URLSearchParams(window.location.search)),
  });

  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [loading, setLoading] = useState(false);

  const handleChange = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const res = await Axios.post(route('oauth.login.submit'), form);
      if (res.data?.redirect) {
        window.location.href = res.data.redirect;
      }
    } catch (error: any) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors);
      } else {
        alert('Unexpected error occurred. Please try again.');
        console.error(error);
      }
    } finally {
      setLoading(false);
    }
  };

    // styled link
  const StyledLink = styled("span")({
    display: "block",
    " > a": {
      color: blue[400],
      textDecoration: "none",
      "&:hover": {
        textDecoration: "underline",
      },
    },
  });



  return (
    <>
      <Head title="Login" />
              <Box sx={{ width: "100%", py: 6, display: "flex", justifyContent: "center", alignItems: "center" }}>
        <Container maxWidth="sm" sx={{ mt: 8 }}>
          <Typography component="h1" variant="h4" sx={{ mb: 2, fontWeight: "bold" }}>
            Sign In
          </Typography>
          <SurvivorLogin />
          <Typography variant="body1" gutterBottom>
            Please use your eMail or Survivor Number to access your account.
          </Typography>
          <form onSubmit={handleSubmit} noValidate>
            <TextField
              label="Email"
              type="email"
              fullWidth
              margin="normal"
              value={form.email}
              onChange={(e) => handleChange('email', e.target.value)}
              error={!!errors.email}
              helperText={errors.email}
            />
            <TextField
              label="Password"
              type="password"
              fullWidth
              margin="normal"
              value={form.password}
              onChange={(e) => handleChange('password', e.target.value)}
              error={!!errors.password}
              helperText={errors.password}
            />
            <Button type="submit" fullWidth variant="contained" disabled={loading} sx={{ mt: 2 }}>
              {loading ? 'Signing in...' : 'Sign In'}
            </Button>
          </form>
          <Box sx={{ mt: 2, display: "flex", flexDirection: "column", gap: "8px", textAlign: "center" }}>
            <StyledLink>
              Don't have an account? <a href={`${frontURL}/en/register`}>Register</a>
            </StyledLink>
            <StyledLink>
              Forgot your password? <a href={`${frontURL}/en/forgot-password`}>Reset Password</a>
            </StyledLink>
          </Box>
        </Container>
      </Box>
    </>
  );
}

Login.layout = (page: React.ReactNode) => <OAuthLayout>{page}</OAuthLayout>;