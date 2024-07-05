import { useRef, FormEventHandler } from 'react';
import { useForm } from '@inertiajs/react';
import { TextField, Button, Box, Stack, Alert } from '@mui/material';
import CheckIcon from '@mui/icons-material/Check';

export default function UpdatePasswordForm() {

  const passwordInput = useRef<HTMLInputElement>(null);
  const currentPasswordInput = useRef<HTMLInputElement>(null);

  const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const updatePassword: FormEventHandler = (e) => {
    e.preventDefault();

    put(route('password.update'), {
      preserveScroll: true,
      onSuccess: () => reset(),
      onError: (errors) => {
        if (errors.password) {
          reset('password', 'password_confirmation');
          passwordInput.current?.focus();
        }

        if (errors.current_password) {
          reset('current_password');
          currentPasswordInput.current?.focus();
        }
      },
    });
  };

  return (
    <section>
      <header>
        <h2>Update Password</h2>

        <p>
          Ensure your account is using a long, random password to stay secure.
        </p>
      </header>

      <form onSubmit={updatePassword}>
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
            error={errors.current_password ? true : false}
            id="standard-password-input"
            label="Current password"
            type="password"
            value={data.current_password}
            autoComplete="current-password"
            onChange={(e) => setData('current_password', e.target.value)}
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

          <Stack
            spacing={2}
            sx={{
              width: "100%"
            }}
          >
            <Button
              variant="contained"
              disabled={processing}
              type="submit"
            >
              Register
            </Button>
          </Stack>
          {recentlySuccessful && <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">Saved.</Alert>}
        </Box>
      </form>
    </section>
  );
}
