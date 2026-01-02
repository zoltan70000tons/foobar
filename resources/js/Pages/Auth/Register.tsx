import { useEffect, FormEventHandler } from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { TextField, Button, Box, Stack } from "@mui/material";

export default function Register() {
  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  useEffect(() => {
    return () => {
      reset("password", "password_confirmation");
    };
  }, []);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route("register"));
  };

  return (
    <GuestLayout>
      <Head title="Register" />

      <form onSubmit={submit}>
        <Box
          sx={{
            display: "flex",
            flexDirection: "row",
            gap: "20px",
            width: "100%",
            flexWrap: "wrap",
            maxWidth: "400px",
          }}
        >
          <TextField
            required
            fullWidth
            error={errors.name ? true : false}
            id="outlined-error"
            label="Name"
            type="text"
            name="name"
            value={data.name}
            onChange={(e) => setData("name", e.target.value)}
          />

          <TextField
            required
            fullWidth
            error={errors.email ? true : false}
            id="outlined-error"
            label="Email"
            type="email"
            value={data.email}
            onChange={(e) => setData("email", e.target.value)}
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
            onChange={(e) => setData("password", e.target.value)}
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
            onChange={(e) => setData("password_confirmation", e.target.value)}
          />

          <Stack
            spacing={2}
            sx={{
              width: "100%",
            }}
          >
            <Link href={route("login")}>Already registered?</Link>

            <Button variant="contained" disabled={processing} type="submit">
              Register
            </Button>
          </Stack>
        </Box>
      </form>
    </GuestLayout>
  );
}
