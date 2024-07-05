import { useEffect, FormEventHandler } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { TextField, Button, Box } from '@mui/material';

export default function ConfirmPassword() {

  const { data, setData, post, processing, errors, reset } = useForm({
    password: '',
  });

  useEffect(() => {
    return () => {
      reset('password');
    };
  }, []);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('password.confirm'));
  };

  return (
    <GuestLayout>
      <Head title="Confirm Password" />

      <div>
        This is a secure area of the application. Please confirm your password before continuing.
      </div>

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
            error={errors.password ? true : false}
            id="standard-password-input"
            label="Password"
            type="password"
            value={data.password}
            autoComplete="current-password"
            onChange={(e) => setData('password', e.target.value)}
          />
          <div>
            <Button
              variant="contained"
              disabled={processing}
              type="submit"
            >
              Confirm
            </Button>
          </div>
        </Box>
      </form>
    </GuestLayout>
  );
}
