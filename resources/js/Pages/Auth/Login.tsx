import React, { useEffect, FormEventHandler, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { TextField, Button, Box, Checkbox, Stack, Alert, Container } from '@mui/material';
import CheckIcon from '@mui/icons-material/Check';
import GuestLayout from '@/Layouts/GuestLayout';
import LoadingOverlay from "@/Components/LoadingOverlay";

export default function Login({ status, canResetPassword }: { status?: string, canResetPassword: boolean }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: false,
  });
  const [loading, setLoading] = useState(false);

  const submit: FormEventHandler = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      await post(route('login'), {
        onSuccess: () => {
           window.location.href = route('dashboard');
        },
        onError: (err) => {
          console.error('Login error:', err);
        },
        onFinish: () => {
          setLoading(false);
        }
      });
    } catch (err) {
      console.error('Login error:', err);
    }
  };

  return (
    <GuestLayout>
      <Container
        component="main"
        maxWidth="xs"
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          minHeight: '100vh',
        }}
      >
        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', width: '100%' }}>
          {status && <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">{status}</Alert>}
          {errors.email && <Alert icon={<CheckIcon fontSize="inherit" />} severity="error">{errors.email}</Alert>}
          {errors.password && <Alert icon={<CheckIcon fontSize="inherit" />} severity="error">{errors.password}</Alert>}
          <form
            autoComplete="true"
            onSubmit={submit}
            style={{ width: '100%', maxWidth: '400px' }}
          >
            <Box
              sx={{
                display: "flex",
                flexDirection: "column",
                gap: "20px",
                width: "100%",
              }}
            >
              <TextField
                required
                fullWidth
                error={Boolean(errors.email)}
                id="outlined-error"
                label="Email"
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
              />
              <TextField
                required
                fullWidth
                error={Boolean(errors.password)}
                id="standard-password-input"
                label="Password"
                type="password"
                value={data.password}
                autoComplete="current-password"
                onChange={(e) => setData('password', e.target.value)}
              />

              <Box sx={{ width: "100%" }}>
                <Checkbox
                  name="remember"
                  checked={data.remember}
                  onChange={(e) => setData('remember', e.target.checked)}
                />
                <span>Remember me</span>
              </Box>
              <Stack spacing={2} sx={{ width: "100%" }}>
                {canResetPassword && (
                  <Link href={route('password.request')} className="">
                    Forgot your password?
                  </Link>
                )}
                <Button variant="contained" disabled={processing} type="submit">
                  Log in
                </Button>
              </Stack>
            </Box>
          </form>
        </Box>
      </Container>
      <LoadingOverlay open={loading}/>
    </GuestLayout>
  );
}
