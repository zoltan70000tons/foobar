import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { 
  TextField, 
  Button, 
  Box, 
  Alert, 
  Container,
  Typography,
  alpha 
} from "@mui/material";
import CheckIcon from '@mui/icons-material/Check';


export default function ForgotPassword({ status }: { status?: string }) {

  const { data, setData, post, processing, errors } = useForm({
    email: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('password.email'));
  };

  return (
    <GuestLayout>
      <Head title="Forgot Password" />
      <Container
        component="main"
        maxWidth="xs"
        sx={{
          display: "flex",
          alignItems: "center",
          flexDirection: "column",
          justifyContent: "center",
          minHeight: "calc(100vh + 64px)",
        }}
      >
        <Typography variant="body2" sx={{ mb: 2 }}>
          Forgot your password? No problem. Just let us know your email address and we will email you a password
          reset link that will allow you to choose a new one.
        </Typography>
        <Box 
          sx={{ 
            display: "flex", 
            flexDirection: "column", 
            alignItems: "center", 
            width: "100%", 
            gap: 2,
            backgroundColor: alpha("#fff", 0.05),
            padding: 4,
            borderRadius: 2,
          }}
        >
          {status && (
            <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">
              {status}
            </Alert>
          )}
          {errors.email && (
            <Alert icon={<CheckIcon fontSize="inherit" />} severity="error">
              {errors.email}
            </Alert>
          )}
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
              <Button
                variant="contained"
                disabled={processing}
                type="submit"
              >
                Email Password Reset Link
              </Button>
            </Box>
          </form>
        </Box>
      </Container>
    </GuestLayout>
  );
}
