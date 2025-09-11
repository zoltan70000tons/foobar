import { useState } from "react";
import { usePage } from "@inertiajs/react";
import {
  Container,
  Typography,
  Button,
  Stack,
  Box,
  Alert,
  LinearProgress,
} from "@mui/material";
import OAuthLayout from "@/Layouts/OAuthLayout";
import Axios from "axios";

type PageProps = {
  tAuth: any;
  tGeneral: any;
  language: 'en' | 'de' | 'es' | string;
  user?: {
    id: number;
    email: string;
    name: string;
  } | null;
};

export default function EmailVerify() {
  const { tAuth, tGeneral, language, user } = usePage<PageProps>().props;

  const [success, setSuccess] = useState(false);
  const [errors, setErrors] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleResendEmail = async () => {
    // Resend email verification link
    setIsLoading(true);
    try {
      await Axios.post(route('oauth.email.verify.resend'), { language });
      setSuccess(true);
      setErrors(null);
    } catch (error) {
      setErrors(tAuth?.Error?.invalid_session_request ?? "Failed to resend email verification link.");
    } finally {
      setIsLoading(false);
    }
  }

  const handleLogout = async () => {
    // Logout user
    await Axios.post(route('oauth.web.logout'));
    // refresh the page
    window.location.href = import.meta.env.VITE_FRONTEND_URL;
  }

  return (
    <OAuthLayout>
      <Container
        sx={{
          minHeight: "100vh",
          position: "relative",
          marginTop: "160px",
          marginBottom: "60px",
        }}
      >
        <Typography
          variant="h6"
          sx={{
            textTransform: "uppercase",
            marginBottom: "20px",
          }}
        >
          {tAuth?.verify_email_title ?? 'Verify your eMail'}
        </Typography>
        {success && (
          <Alert
            severity="success"
            sx={{
              marginBottom: "20px",
            }}
          >
            {tAuth?.verify_email_sent ?? 'A verification link has been sent to your email.'}
          </Alert>
        )}
        {errors && (
          <Alert
            severity="error"
            sx={{
              marginBottom: "20px",
            }}
          >
            {errors}
          </Alert>
        )}
        {isLoading && <LinearProgress sx={{ marginBottom: 2 }} />}
        <Stack direction={{ xs: "column", sm: "row" }} spacing={2}>
          <Box
            sx={{
              position: "relative",
              overflow: "hidden",
              p: 4,
              marginTop: "20px",
              borderRadius: "5px",
              border: "1px solid rgba(255, 255, 255, 0.1)",
              boxShadow: "0 0 5px rgba(0, 0, 0, 0.1)",
              flex: 1,
            }}
          >
            <Typography variant="body1">{tAuth?.check_email ?? 'Please check your email for a verification link.'}</Typography>
            <Button
              onClick={handleLogout}
              variant="contained"
              sx={{
                marginTop: "20px",
              }}
            >
              {tAuth?.logout ?? 'Log Out'}
            </Button>
          </Box>
          <Box
            sx={{
              position: "relative",
              overflow: "hidden",
              padding: "20px",
              marginTop: "20px",
              borderRadius: "5px",
              border: "1px solid rgba(255, 0, 0, 0.4)",
              boxShadow: "0 0 5px rgba(0, 0, 0, 0.1)",
              backgroundColor: "rgba(255, 0, 0, 0.1)",
              width: "100%",
              "@media (min-width: 600px)": {
                maxWidth: "300px",
              },
            }}
          >
            <Typography variant="body1">{tAuth?.receive_email ?? 'If you did not receive the email, click here to request another.'}</Typography>
            <Button
              onClick={handleResendEmail}
              variant="contained"
              disabled={isLoading}
              color="error"
              fullWidth={true}
              sx={{
                marginTop: "20px",
              }}
            >
              {tAuth?.send_email ?? 'Send eMail'}
            </Button>
          </Box>
        </Stack>
      </Container>
    </OAuthLayout>
  );
}
