import { useEffect, FormEventHandler } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { TextField, Button, Box, Checkbox, Stack, Alert } from '@mui/material';
import CheckIcon from '@mui/icons-material/Check';

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

      {status && <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">{status}</Alert>}

      <form
        autoComplete="true"
        onSubmit={submit}
      >
        <Box
          sx={{
            display: "flex",
            flexDirection: "row",
            gap: "20px",
            width: "100%",
            flexWrap: "wrap",
            maxWidth: "400px"
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

          <Box
            sx={{
              width: "100%"
            }}
          >
            <Checkbox
              name="remember"
              checked={data.remember}
              onChange={(e) => setData('remember', e.target.checked)}
            />
            <span>Remember me</span>
          </Box>
          <Stack
            spacing={2}
            sx={{
              width: "100%"
            }}
          >
            {canResetPassword && (
              <Link
                href={route('password.request')}
                className=""
              >
                Forgot your password?
              </Link>

            )}
            <Button
              variant="contained"
              disabled={processing}
              type="submit"
            >
              Log in
            </Button>
          </Stack>
        </Box>
      </form>
    </GuestLayout>
  );
}
