import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { TextField, Button, Box, Typography } from '@mui/material';
import OAuthLayout from '@/Layouts/OAuthLayout';
import Axios from 'axios';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
     ...Object.fromEntries(new URLSearchParams(window.location.search)),
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      const res = await Axios.post(route('oauth.login.submit'), data);

      if (res.data?.redirect) {
        window.location.href = res.data.redirect;
      }
    } catch (error: any) {
      console.error(error);
      // handle error display here
    }
  };
  
  return (
    <>
      <Head title="Login" />
      <Typography variant="h5" gutterBottom>Login</Typography>
      <Box component="form" onSubmit={handleSubmit} noValidate>
        <TextField
          label="Email"
          type="email"
          fullWidth
          margin="normal"
          value={data.email}
          onChange={e => setData('email', e.target.value)}
          error={!!errors.email}
          helperText={errors.email}
        />
        <TextField
          label="Password"
          type="password"
          fullWidth
          margin="normal"
          value={data.password}
          onChange={e => setData('password', e.target.value)}
          error={!!errors.password}
          helperText={errors.password}
        />
        <Button type="submit" fullWidth variant="contained" disabled={processing} sx={{ mt: 2 }}>
          Sign In
        </Button>
      </Box>
    </>
  );
}

Login.layout = (page: React.ReactNode) => <OAuthLayout>{page}</OAuthLayout>;