import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";
import { Alert, Button } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

export default function VerifyEmail({ status }: { status?: string }) {
  const { post, processing } = useForm({});

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route("verification.send"));
  };

  return (
    <GuestLayout>
      <Head title="Email Verification" />

      <div>
        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we
        just emailed to you? If you didn't receive the email, we will gladly send you another.
      </div>

      {status === "verification-link-sent" && (
        <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">
          A new verification link has been sent to the email address you provided during registration.
        </Alert>
      )}

      <form onSubmit={submit}>
        <div>
          <Button disabled={processing}>Resend Verification Email</Button>

          <Link href={route("logout")} method="post" as="button">
            Log Out
          </Link>
        </div>
      </form>
    </GuestLayout>
  );
}
