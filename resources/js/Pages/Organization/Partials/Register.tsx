import { useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, useState, useEffect } from "react";
import { TextField, Button, Box, Stack, Alert, Divider } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

interface FormData {
  name: string;
  user_name: string;
  email: string;
  password: string;
  confirm_password: string;
}

export default function RegisterOrganization() {
  const { flash } = usePage().props;
  const { data, setData, post, errors, processing, recentlySuccessful } =
    useForm<FormData>({
      name: "",
      user_name: "",
      email: "",
      password: "",
      confirm_password: "",
    });

  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Partial<FormData>>({});
  const [isButtonDisabled, setIsButtonDisabled] = useState(true);

  const validate = (field: keyof FormData, value: string) => {
    const newErrors: Partial<FormData> = { ...validationErrors };

    if (field === "name" && !value) {
      newErrors.name = "Organization name is required.";
    } else if (field === "user_name" && !value) {
      newErrors.user_name = "Your name is required.";
    } else if (field === "email" && !value) {
      newErrors.email = "Email is required.";
    } else if (field === "password" && !value) {
      newErrors.password = "Password is required.";
    } else if (field === "confirm_password" && !value) {
      newErrors.confirm_password = "Confirm password is required.";
    } else {
      delete newErrors[field];
    }

    if ((field === "password" || field === "confirm_password") && data.password !== data.confirm_password) {
      newErrors.confirm_password = "Passwords do not match.";
    }

    setValidationErrors(newErrors);
    setIsButtonDisabled(Object.keys(newErrors).length > 0);
  };

  const handleInputChange = (field: keyof FormData, value: string) => {
    setData(field, value);
    validate(field, value);
  };

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    const newErrors = validateAll();
    if (Object.keys(newErrors).length === 0) {
      post("/api/organizations");
    } else {
      setValidationErrors(newErrors);
    }
  };

  const validateAll = () => {
    const newErrors: Partial<FormData> = {};
    if (!data.name) newErrors.name = "Organization name is required.";
    if (!data.user_name) newErrors.user_name = "Your name is required.";
    if (!data.email) newErrors.email = "Email is required.";
    if (!data.password) newErrors.password = "Password is required.";
    if (!data.confirm_password) newErrors.confirm_password = "Confirm password is required.";
    if (data.password !== data.confirm_password)
      newErrors.confirm_password = "Passwords do not match.";
    return newErrors;
  };

  useEffect(() => {
    const newErrors = validateAll();
    setValidationErrors(newErrors);
    setIsButtonDisabled(Object.keys(newErrors).length > 0);
  }, [data]);

  return (
    <Box
      display="flex"
      justifyContent="center"
      alignItems="center"
      minHeight="100vh"
    >
      <section style={{ maxWidth: "400px" }}>
        <header>
          <h2>Create Organization</h2>
          <Divider flexItem />
          <p>Name of your organization can not be changed in the future.</p>
          <p>
            Your account will have administrator role, You can invite another
            users to crew and set permissions.{" "}
          </p>
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
              error={Boolean(errors.name || validationErrors.name)}
              label="Name of your organization"
              type="text"
              name="name"
              value={data.name}
              onChange={(e) => handleInputChange("name", e.target.value)}
              helperText={validationErrors.name}
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
              error={Boolean(errors.email || validationErrors.email)}
              label="Your Email"
              type="email"
              name="email"
              value={data.email}
              onChange={(e) => handleInputChange("email", e.target.value)}
              helperText={validationErrors.email}
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
            <TextField
              fullWidth
              error={Boolean(errors.confirm_password || validationErrors.confirm_password)}
              label="Confirm password"
              type="password"
              name="confirm_password"
              value={data.confirm_password}
              onChange={(e) => handleInputChange("confirm_password", e.target.value)}
              helperText={validationErrors.confirm_password}
            />
            <Stack
              sx={{
                width: "100%",
              }}
            >
              {recentlySuccessful && (
                <Alert
                  icon={<CheckIcon fontSize="inherit" />}
                  severity="success"
                >
                  Saved.
                </Alert>
              )}
              <Button variant="contained" disabled={isButtonDisabled || processing} type="submit">
                Create Organization
              </Button>
            </Stack>
          </Box>
        </form>
      </section>
    </Box>
  );
}