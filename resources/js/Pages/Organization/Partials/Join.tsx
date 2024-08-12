import { useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";
import { TextField, Button, Box, Stack, Alert, Divider } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

interface FormData {
  user_nickname: string;
  user_name: string;
  user_lastname: string;
  email: string;
  password: string;
}

export default function JoinOrganization({ email }: { email: string }) {
  const { flash } = usePage().props;
  const { data, setData, post, put, errors, processing, recentlySuccessful } = useForm<FormData>({
    user_nickname: "",
    user_name: "",
    user_lastname: "",
    email: email,
    password: "",
  });

  const [validationErrors, setValidationErrors] = useState<Partial<FormData>>({});
  const [isButtonDisabled, setIsButtonDisabled] = useState(true);

  const validateForm = (field?: keyof FormData) => {
    const newErrors: Partial<FormData> = {};
    const { user_nickname, user_name, user_lastname, email, password } = data;

    // Validación de todos los campos
    if (field === undefined || field === "user_nickname") {
      if (!user_nickname) newErrors.user_nickname = "Your nickname is required.";
    }
    if (field === undefined || field === "user_name") {
      if (!user_name) newErrors.user_name = "Your name is required.";
    }
    if (field === undefined || field === "user_lastname") {
      if (!user_lastname) newErrors.user_lastname = "Your lastname is required.";
    }
    if (field === undefined || field === "email") {
      if (!email) newErrors.email = "Email is required.";
    }
    if (field === undefined || field === "password") {
      if (!password) newErrors.password = "Password is required.";
    }

    setValidationErrors(newErrors);
    setIsButtonDisabled(Object.keys(newErrors).length > 0);
    return newErrors; // Devuelve los errores para el caso de validación en el submit
  };

  const handleInputChange = (field: keyof FormData, value: string) => {
    setData(field, value);
    validateForm(field);
  };

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    const errors = validateForm(); // Validar todo el formulario
    if (Object.keys(errors).length === 0) {
       put("/join-organization");
    }
  };

  return (
    <Box display="flex" justifyContent="center" alignItems="center" minHeight="100vh">
      <section style={{ maxWidth: "400px" }}>
        <header>
          <h2>Join the 70000 Tons Of Metal Team.</h2>
          <Divider flexItem />
          <p>You have been invited to be part of 70K Tons of Metal.</p>
          <br />
        </header>
        <form onSubmit={submit}>
          <Box
            sx={{
              display: "flex",
              flexDirection: "row",
              gap: "20px",
              width: "100%",
              flexWrap: "wrap",
            }}
          >
            <TextField
              fullWidth
              error={Boolean(errors.user_nickname || validationErrors.user_nickname)}
              label="Username"
              type="text"
              name="user_nickname"
              value={data.user_nickname}
              onChange={(e) => handleInputChange("user_nickname", e.target.value)}
              helperText={validationErrors.user_nickname}
            />

            <TextField
              fullWidth
              error={Boolean(errors.user_name || validationErrors.user_name)}
              label="Your name"
              type="text"
              name="user_name"
              value={data.user_name}
              onChange={(e) => handleInputChange("user_name", e.target.value)}
              helperText={validationErrors.user_name}
            />

            <TextField
              fullWidth
              error={Boolean(errors.user_lastname || validationErrors.user_lastname)}
              label="Your lastname"
              type="text"
              name="user_lastname"
              value={data.user_lastname}
              onChange={(e) => handleInputChange("user_lastname", e.target.value)}
              helperText={validationErrors.user_lastname}
            />

            <TextField
              fullWidth
              label="Your Email"
              type="email"
              name="email"
              value={data.email}
              disabled={true}
            />

            <TextField
              fullWidth
              error={Boolean(errors.password || validationErrors.password)}
              label="Password"
              type="password"
              name="password"
              value={data.password}
              onChange={(e) => handleInputChange("password", e.target.value)}
              helperText={validationErrors.password}
            />

            <Stack sx={{ width: "100%" }}>
              {recentlySuccessful && (
                <Alert icon={<CheckIcon fontSize="inherit" />} severity="success">
                  Saved.
                </Alert>
              )}
              <Button variant="contained" disabled={isButtonDisabled || processing} type="submit">
                Join
              </Button>
            </Stack>
          </Box>
        </form>
      </section>
    </Box>
  );
}
