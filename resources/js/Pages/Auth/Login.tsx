import { useEffect, FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { TextField, Button, Box, Checkbox, Stack, Alert, Container } from '@mui/material';
import CheckIcon from '@mui/icons-material/Check';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Login({ status, canResetPassword }: { status?: string, canResetPassword: boolean }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  useEffect(() => {
    return () => {
      reset('password');
    };
  }, []);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('login'));
  };

  return (
    <GuestLayout>
      <Head title="Log in" />
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
                error={errors.email ? true : false}
                id="outlined-error"
                label="Email"
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
              />
              <TextField
                required
                fullWidth
                error={errors.password ? true : false}
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
    </GuestLayout>
  );
}
