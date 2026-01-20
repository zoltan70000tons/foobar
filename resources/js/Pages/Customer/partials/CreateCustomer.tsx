import React, { useEffect, useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Button,
  Typography,
  Select,
  MenuItem,
  FormHelperText,
  FormControl,
  InputLabel,
  Alert,
} from "@mui/material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { Permissions } from "@/enums/PermissionEnum";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import { usePermissions } from "@/Providers/PermissionContext";
import dayjs from "dayjs";
import iso3166 from "iso-3166-2";
import axios from "axios";
import { Customer } from "@/interfaces/Customer";

type CountryWithSubs = ReturnType<typeof iso3166.country> & {
  sub?: Record<string, { type: string; name: string }>;
};

type Props = {
  isCloseBtn?: boolean;
  handleClose?: () => void;
  setCreatedCustomer?: (customer: Customer) => void;
};

export default function CreateCustomer({ isCloseBtn, handleClose, setCreatedCustomer }: Props) {
  const { hasPermission } = usePermissions();

  const [customerAlert, setCustomerAlert] = useState<Record<string, string> | null>(null);
  const { data, setData, errors, reset, processing } = useForm({
    email: "",
    survivor_number: "",
    first_name: "",
    last_name: "",
    middle_name: "",
    gender: "M",
    dob: "",
    citizenship: "",
    phone: "",
    address_first: "",
    address_second: "",
    city: "",
    state: "",
    postal_code: "",
    country: "",
    emergency_c_name: "",
    emergency_c_phone: "",
    language: "",
  });

  // const { showSnackbar } = useSnackbar();

  const selectedCountry = data.country;

  const getStateOptions = (countryCode: string) => {
    const country = iso3166.country(countryCode) as CountryWithSubs;
    const subdivisions = country?.sub || {};

    const seen = new Set<string>();

    return Object.entries(subdivisions)
      .map(([fullCode, { name }]) => ({ label: name, value: name }))
      .filter(({ label }) => {
        if (seen.has(label)) {
          return false;
        }
        seen.add(label);
        return true;
      })
      .sort((a, b) => a.label.localeCompare(b.label));
  };

  const stateOptions = getStateOptions(selectedCountry);

  useEffect(() => {
    setData("state", "");
  }, [selectedCountry]);

  const handleChange = <TForm extends Record<string, unknown>>(
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
  ) => {
    const { name, value } = e.target;

    setData(name as keyof TForm, value as TForm[keyof TForm]);
  };

  const handleSelectChange = (e: React.ChangeEvent<{ name?: string; value: unknown }>) => {
    const name = e.target.name;
    const value = e.target.value;

    setData("state", value as string);
  };

  const handleStringChange = <TForm extends Record<string, unknown>>(value: string, name: keyof TForm) => {
    setData(name, value as TForm[keyof TForm]);
  };

  // error helper
  function extractErrorMessages(error: any): string {
    const data = error?.response?.data ?? error;

    if (data.errors && typeof data.errors === "object") {
      const messages: string[] = [];

      Object.values(data.errors).forEach((value) => {
        if (Array.isArray(value)) {
          messages.push(...value);
        } else if (typeof value === "string") {
          messages.push(value);
        }
      });

      return messages.join("\n");
    }

    if (typeof data.message === "string") {
      return data.message;
    }

    if (typeof data === "object") {
      return JSON.stringify(data);
    }

    return "An unknown error occurred.";
  }

  // Submit
  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const formData = new FormData();
    for (const key in data) {
      formData.append(key, data[key]);
    }

    try {
      const res = await axios.post(route("customers.store"), formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      // Success
      // showSnackbar("Customer created successfully", "success");
      setCustomerAlert({ type: "success", message: "Customer created successfully" });
      reset();

      handleClose?.();
      setCreatedCustomer?.(res.data.customer);
    } catch (error: any) {
      const errorText = extractErrorMessages(error);
      setCustomerAlert({ type: "error", message: `Error creating customer\n${errorText}` });
    }
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4, position: "relative" }}>
      <Grid container spacing={3}>
        {hasPermission(Permissions.CreateCustomers) && (
          <Paper
            sx={{
              p: 2,
              display: "flex",
              flexDirection: "column",
              minHeight: 240,
              width: "100%",
            }}
          >
            <Box sx={{ display: "flex", justifyContent: "space-between", alignItems: "center", mb: 2 }}>
              <h1>Create Customer</h1>
              {isCloseBtn && (
                <Button
                  variant="outlined"
                  color="secondary"
                  onClick={handleClose}
                  sx={{
                    mb: 2,
                    position: "relative",
                  }}
                >
                  Close
                </Button>
              )}
            </Box>
            <form onSubmit={handleSubmit} encType="multipart/form-data">
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="eMail"
                      variant="outlined"
                      value={data.email}
                      name={"email"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="First Name"
                      variant="outlined"
                      value={data.first_name}
                      name={"first_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Middle Name"
                      variant="outlined"
                      name={"middle_name"}
                      value={data.middle_name}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Last Name"
                      variant="outlined"
                      value={data.last_name}
                      name={"last_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Select
                      fullWidth
                      label="Gender"
                      variant="outlined"
                      value={data.gender}
                      name={"gender"}
                      onChange={handleChange}
                    >
                      <MenuItem value={"M"}>Male</MenuItem>
                      <MenuItem value={"F"}>Female</MenuItem>
                    </Select>
                  </Grid>
                </Grid>
              </Box>

              <Box sx={{ width: "100%", mt: 2, mb: 2 }}>
                <Grid container spacing={2}>
                  <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <Grid item xs={6}>
                      <DatePicker
                        label="Date of Birth"
                        sx={{ width: "100%" }}
                        value={data.dob ? dayjs(data.dob) : null}
                        maxDate={dayjs()} // Restricts future dates
                        format="YYYY-MM-DD" // Ensures consistent formatting
                        onChange={(e) => handleStringChange(e, "dob")}
                        renderInput={(params) => <TextField {...params} fullWidth />}
                      />
                    </Grid>
                  </LocalizationProvider>
                  <Grid item xs={6}>
                    <Country
                      fullWidth
                      label="Citizenship"
                      variant="outlined"
                      value={data.citizenship}
                      name={"citizenship"}
                      onChange={(e) => handleStringChange(e, "citizenship")}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Phone Number
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <PhoneNumber
                      value={data.phone || ""}
                      forceDialCode={true}
                      name={"phone"}
                      onChange={(e) => handleStringChange(e, "phone")}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Address Information
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Address Line 1"
                      variant="outlined"
                      value={data.address_first}
                      name={"address_first"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Address Line 2"
                      variant="outlined"
                      value={data.address_second}
                      name={"address_second"}
                      onChange={handleChange}
                    />
                  </Grid>

                  <Grid item xs={6}>
                    <Country
                      fullWidth
                      label="Country"
                      variant="outlined"
                      value={data.country}
                      name={"country"}
                      onChange={(e) => handleStringChange(e, "country")}
                    />
                  </Grid>

                  <Grid item xs={6}>
                    <FormControl
                      required={selectedCountry === "USA" || selectedCountry === "CAN"}
                      fullWidth
                      error={!!errors.state}
                    >
                      <InputLabel id="state-label">State</InputLabel>
                      <Select
                        labelId="state-label"
                        label="State"
                        value={data.state}
                        onChange={handleSelectChange}
                        variant="outlined"
                        error={!!errors.state}
                      >
                        {stateOptions.map(({ value, label }) => (
                          <MenuItem key={value} value={value}>
                            {label}
                          </MenuItem>
                        ))}
                      </Select>
                      <FormHelperText>{errors.state}</FormHelperText>
                    </FormControl>
                  </Grid>

                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="City"
                      variant="outlined"
                      value={data.city}
                      name={"city"}
                      onChange={handleChange}
                    />
                  </Grid>

                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Zip Code"
                      variant="outlined"
                      value={data.postal_code}
                      name={"postal_code"}
                      onChange={handleChange}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Emergency Contact
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <TextField
                      fullWidth
                      label="Contact Full Name"
                      variant="outlined"
                      value={data.emergency_c_name}
                      name={"emergency_c_name"}
                      onChange={handleChange}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <PhoneNumber
                      value={data.emergency_c_phone || ""}
                      forceDialCode={true}
                      name={"emergency_c_phone"}
                      onChange={(e) => handleStringChange(e, "emergency_c_phone")}
                    />
                  </Grid>
                </Grid>
              </Box>

              <Typography variant="h6" sx={{ mt: 2, mb: 2 }}>
                Preferred Language
              </Typography>
              <Box sx={{ width: "100%" }}>
                <Grid container spacing={2}>
                  <Grid item xs={6}>
                    <Select
                      fullWidth
                      variant="outlined"
                      value={data.language}
                      name={"language"}
                      onChange={handleChange}
                    >
                      <MenuItem value={"de"}>Deutsch</MenuItem>
                      <MenuItem value={"en"}>English</MenuItem>
                      <MenuItem value={"es"}>Español</MenuItem>
                    </Select>
                  </Grid>
                </Grid>
              </Box>
              <Box sx={{ mt: 4 }}>
                {customerAlert && (
                  <Alert
                    severity={customerAlert.type === "success" ? "success" : "error"}
                    onClose={() => setCustomerAlert(null)}
                    sx={{
                      my: 2,
                      zIndex: (theme) => theme.zIndex.modal + 1,
                    }}
                  >
                    {customerAlert.message.split("\n").map((msg, index) => (
                      <div key={index}>{msg}</div>
                    ))}
                  </Alert>
                )}
                <Button variant="contained" color="primary" fullWidth type="submit" disabled={processing}>
                  {processing ? "Submitting..." : "Submit"}
                </Button>
              </Box>
            </form>
          </Paper>
        )}
      </Grid>
    </Container>
  );
}
