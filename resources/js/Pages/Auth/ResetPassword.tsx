import { useEffect, FormEventHandler } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { TextField, Button, Box } from '@mui/material';

export default function ResetPassword({ token, email }: { token: string, email: string }) {

  const { data, setData, post, processing, errors, reset } = useForm({
    token: token,
    email: email,
    password: '',
    password_confirmation: '',
  });

  useEffect(() => {
    return () => {
      reset('password', 'password_confirmation');
    };
  }, []);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('password.store'));
  };

  return (
    <GuestLayout>
      <Head title="Reset Password" />

      <form onSubmit={submit}>
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
            autoComplete="new-password"
            onChange={(e) => setData('password', e.target.value)}
          />

          <TextField
            required
            fullWidth
            name="password_confirmation"
            error={errors.password_confirmation ? true : false}
            id="standard-password-input"
            label="Password confirmation"
            type="password"
            value={data.password_confirmation}
            onChange={(e) => setData('password_confirmation', e.target.value)}
          />
          <Button
            variant="contained"
            disabled={processing}
            type="submit"
          >
            Reset Password
          </Button>
        </Box>
      </form>
    </GuestLayout>
  );
}
