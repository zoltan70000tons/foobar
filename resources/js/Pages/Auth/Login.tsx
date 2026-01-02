import { FormEventHandler, useState } from "react";
import { Link, useForm } from "@inertiajs/react";
import {
  FormControlLabel,
  TextField,
  Button,
  Box,
  Checkbox,
  Stack,
  Alert,
  Container,
  Typography,
  alpha,
} from "@mui/material";
import { blue } from "@mui/material/colors";
import CheckIcon from "@mui/icons-material/Check";
import GuestLayout from "@/Layouts/GuestLayout";
// import LoadingOverlay from "@/Components/LoadingOverlay";

export default function Login({ status, canResetPassword }: { status?: string; canResetPassword: boolean }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: "",
    password: "",
    remember: false,
  });
  const [loading, setLoading] = useState(false);

  // const submit: FormEventHandler = async (e) => {
  //   e.preventDefault();
  //   setLoading(true);

  //   try {
  //     await post(route('login'), {
  //       onSuccess: () => {
  //         window.location.href = route('dashboard');
  //       },
  //       onError: (err) => {
  //         console.error('Login error:', err);
  //       },
  //       onFinish: () => {
  //         setLoading(false);
  //       },
  //     });
  //   } catch (err) {
  //     console.error('Login error:', err);
  //   }
  // };

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route("login"), {
      onError: (err) => {
        console.error("Login error:", err);
      },
      onSuccess: () => {
        window.location.reload();
      },
    });
  };

  return (
    <GuestLayout>
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
        <Box
          sx={{
            display: "flex",
            flexDirection: "column",
            alignItems: "start",
            width: "100%",
            mb: 2,
          }}
        >
          <Typography variant="h4" component="h1" gutterBottom>
            Log in
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Please enter your credentials to log in.
          </Typography>
        </Box>
        <Box
          sx={{
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            width: "100%",
            gap: 4,
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
          {errors.password && (
            <Alert icon={<CheckIcon fontSize="inherit" />} severity="error">
              {errors.password}
            </Alert>
          )}
          <form autoComplete="true" onSubmit={submit} style={{ width: "100%", maxWidth: "400px" }}>
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
                onChange={(e) => setData("email", e.target.value)}
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
                onChange={(e) => setData("password", e.target.value)}
              />

              {/* <Box sx={{ width: "100%" }}>
                <Checkbox
                  name="remember"
                  checked={data.remember}
                  onChange={(e) => setData("remember", e.target.checked)}
                />
                <span>Remember me</span>
              </Box> */}
              <FormControlLabel
                control={
                  <Checkbox
                    name="remember"
                    checked={data.remember}
                    onChange={(e) => setData("remember", e.target.checked)}
                  />
                }
                label="Remember me"
              />
              <Stack spacing={2} sx={{ width: "100%" }}>
                {canResetPassword && (
                  <Link
                    href={route("password.request")}
                    style={{
                      textDecoration: "none",
                      color: blue[500],
                      textAlign: "left",
                      fontSize: "0.875rem",
                      fontWeight: 500,
                    }}
                  >
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
      {/* <LoadingOverlay open={loading} /> */}
    </GuestLayout>
  );
}
