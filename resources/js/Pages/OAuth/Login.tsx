import React, { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { TextField, Button, Box, Container, Typography, Alert } from '@mui/material';
import OAuthLayout from '@/Layouts/OAuthLayout';
import Axios from 'axios';
import SurvivorLogin from '@/Pages/OAuth/components/SurvivorLogin';
import { blue } from '@mui/material/colors';
import { styled } from '@mui/material/styles';


type OAuthLoginProps = {
  tAuth: any;
  language: 'en' | 'de' | 'es' | string;
};

export default function Login() {
 const frontURL = import.meta.env.VITE_FRONTEND_URL;
  const { tAuth, language } = usePage<OAuthLoginProps>().props;

  // Parse URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  const verified = urlParams.get('verified');
  const reset = urlParams.get('reset');
  const registered = urlParams.get('registered');
  const activated = urlParams.get('activated');

  const [form, setForm] = useState({
    identifier: '',
    password: '',
    ...Object.fromEntries(new URLSearchParams(window.location.search)),
  });

  const [error, setError] = useState<string | null>(null);
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [loading, setLoading] = useState(false);

  const handleChange = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  // Handle form submission
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    setErrors({});

    try {
      const res = await Axios.post(route('oauth.login.submit'), {...form, lang: language});
      if (res.data?.redirect) {
        window.location.href = res.data.redirect;
        return;
      }
    } catch (error: any) {
   
      setLoading(false); // only on error
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors);
      } else {
        setError(error?.response?.data?.message || 'An unexpected error occurred.');
        console.error(error);
      }
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
      <Head title={tAuth?.login_title ?? 'Login'} />
        <Box sx={{ width: "100%", py: 6, display: "flex", justifyContent: "center", alignItems: "center" }}>
        <Container maxWidth="sm" sx={{ mt: 8 }}>
          <Typography component="h1" variant="h4" sx={{ mb: 2, fontWeight: "bold" }}>
            {tAuth?.login_title ?? 'Sign In'}
          </Typography>
          <SurvivorLogin />
          <Typography variant="body1" gutterBottom>
            {tAuth?.login_to_your ?? 'Please use your eMail or Survivor Number to access your account.'}
          </Typography>
          {verified === "1" && (
            <Alert
              severity="success"
              sx={{
                marginBottom: 2,
              }}
            >
              {tAuth?.Success?.email_verified ?? 'Your eMail has been verified!'}
            </Alert>
          )}
          {verified === "errorSignature" && (
            <Alert
              severity="error"
              sx={{
                marginBottom: 2,
              }}
            >
              {tAuth?.Error?.invalid_session_request ?? 'Invalid request or time expired, please refresh the page and try again.'}
            </Alert>
          )}
          {reset === "true" && (
            <Alert
              severity="success"
              sx={{
                marginBottom: 2,
              }}
            >
              {tAuth?.Success?.password_reset_success ?? 'Your password has been reset!'}
            </Alert>
          )}
          {registered === "true" && (
            <Alert
              severity="success"
              sx={{
                marginBottom: 2,
              }}
            >
              {tAuth?.check_email ?? 'Please check your email for a verification link.'}
            </Alert>
          )}
          {activated === "true" && (
            <Alert
              severity="success"
              sx={{
                marginBottom: 2,
              }}
            >
              {tAuth?.account_recovered ?? 'Link sent successfully'}
            </Alert>
          )}
          {error && (
            <Alert
              severity="error"
              sx={{
                marginBottom: 2,
              }}
            >
              {error}
            </Alert>
          )}
          <form onSubmit={handleSubmit} noValidate>
            <TextField
              label={tAuth?.identifier ?? 'eMail or Survivor Number'}
              type="email"
              fullWidth
              margin="normal"
              value={form.identifier}
              onChange={(e) => handleChange('identifier', e.target.value)}
              error={!!errors.identifier}
              helperText={errors.identifier}
            />
            <TextField
              label={tAuth?.password ?? 'Password'}
              type="password"
              fullWidth
              margin="normal"
              value={form.password}
              onChange={(e) => handleChange('password', e.target.value)}
              error={!!errors.password}
              helperText={errors.password}
            />
            <Button type="submit" fullWidth variant="contained" disabled={loading} sx={{ mt: 2 }}>
              {loading ? (tAuth?.login ?? 'Sign In') : (tAuth?.login ?? 'Sign In')}
            </Button>
          </form>
          <Box sx={{ mt: 2, display: "flex", flexDirection: "column", gap: "8px", textAlign: "center" }}>
            <StyledLink>
              {(tAuth?.dont_have_account ?? "Don't have an account?") + ' '}<a href={`${frontURL}/${language}/register`}>{tAuth?.register ?? 'Register'}</a>
            </StyledLink>
            <StyledLink>
              {(tAuth?.forgot_password ?? 'Forgot your password?') + ' '}<a href={`${frontURL}/${language}/forgot-password`}>{tAuth?.reset_password ?? 'Reset password'}</a>
            </StyledLink>
          </Box>
        </Container>
      </Box>
    </>
  );
}

Login.layout = (page: React.ReactNode) => <OAuthLayout>{page}</OAuthLayout>;
