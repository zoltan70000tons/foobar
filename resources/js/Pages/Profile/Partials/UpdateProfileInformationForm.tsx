import { Link, useForm, usePage } from "@inertiajs/react";
import { FormEventHandler } from "react";
import { PageProps } from "@/types";
import { TextField, Button, Box, Stack, Alert } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

export default function UpdateProfileInformation({
  mustVerifyEmail,
  status,
}: {
  mustVerifyEmail: boolean;
  status?: string;
}) {
  const user = usePage<PageProps>().props.auth.user;

  const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
    name: user.name,
    email: user.email,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    patch(route("profile.update"));
  };

  return (
    <section>
      <header>
        <h2>Profile Information</h2>

        <p>Update your account's profile information and email address.</p>
      </header>
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
            fullWidth
            error={errors.name ? true : false}
            label="Name"
            type="text"
            name="name"
            value={data.name}
            onChange={(e) => setData("name", e.target.value)}
          />
          <TextField
            fullWidth
            error={errors.email ? true : false}
            label="Email"
            type="email"
            name="email"
            value={data.email}
            onChange={(e) => setData("email", e.target.value)}
          />
          {mustVerifyEmail && user.email_verified_at === null && (
            <div>
              <p>
                Your email address is unverified.
                <Link href={route("verification.send")} method="post" as="button">
                  Click here to re-send the verification email.
                </Link>
              </p>

              {status === "verification-link-sent" && (
                <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">
                  A new verification link has been sent to your email address.
                </Alert>
              )}
            </div>
          )}
          <Stack
            sx={{
              width: "100%",
            }}
          >
            {recentlySuccessful && (
              <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">
                Saved.
              </Alert>
            )}

            <Button variant="contained" disabled={processing} type="submit">
              Save
            </Button>
          </Stack>
        </Box>
      </form>
    </section>
  );
}
