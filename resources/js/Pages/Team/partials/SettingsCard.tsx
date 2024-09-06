import React, { useState, useEffect } from "react";
import Card from "@mui/material/Card";
import Divider from "@mui/material/Divider";
import Grid from "@mui/material/Grid";
import FormControl from "@mui/material/FormControl";
import Button from "@mui/material/Button";
import Tabs from "@mui/material/Tabs";
import Tab from "@mui/material/Tab";
import CustomInput from "./CustomInput";
import AssignRoles from "@/Components/AssignRoles";
import { Box } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { useForm, usePage } from "@inertiajs/react";
import { MuiTelInput } from 'mui-tel-input';
import { CardContent, Snackbar, Alert } from "@mui/material";
import { useTeamData } from "@/Hooks/useTeamData";
import CustomSelect from "./CustomSelect";
import SnackbarAlert from "@/Components/SnackbarAlert";
import { Permissions } from "@/enums/PermissionEnum";

interface User {
  id: number;
  email: string;
  status: string;
  roles: string[];
  organization_id: number;
  organization_name: string;
  survivor_number?: string;
  lastname?: string;
  middlename?: string;
  username?: string;
  firstname?: string;
  phone_number?: string;
  gender?: string;
}

interface SettingsCardProps {
  user: User;
}

const SettingsCard: React.FC<SettingsCardProps> = ({ user }) => {
  const { hasPermission } = usePermissions();
  const { rows, fetchData, loading } = useTeamData();
  const { errors, flash } = usePage().props;
  const [snackbar, setSnackbar] = useState({ open: false, severity: 'success', message: '' });




  const { data, setData, post, processing } = useForm({
    id: user.id,
    email: user.email,
    phone_number: user?.detail?.phone,
    pass: '',
    survivor_number: user.survivor_number || '',
    lastname: user?.detail?.last_name || '',
    middlename: user?.detail?.middle_name || '',
    username: user.user_name || '',
    firstname: user?.detail?.first_name || '',
    gender: user?.detail?.gender || ''
  });

  const canEdit = hasPermission(Permissions.EditUsers);

  const handleUserChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setData(event.target.name, event.target.value);
  };

  const handleChangePhone = (val) => {
    setData('phone_number', val);
  }

  const [edit, setEdit] = useState({
    disabled: !canEdit,
  });

  const [tabValue, setTabValue] = useState("one");

  const handleTabChange = (event: React.SyntheticEvent, newValue: string) => {
    setTabValue(newValue);
  };

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    if (canEdit) {
      post(route('member.update', user.id), {
        onSuccess: () => {
          setSnackbar({ open: true, severity: 'success', message: 'User updated successfully' });
          fetchData();
        },
        onError: () => {
          setSnackbar({ open: true, severity: 'error', message: 'Error updating user' });
        },
      });
    }
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleChangeSelect = (value: object) => {
    setData('gender', value.target.value);
    console.log(value.target);

  }
  return (
    <Card variant="outlined" sx={{ height: "100%", width: "100%" }}>
      <Tabs
        value={tabValue}
        onChange={handleTabChange}
        textColor="secondary"
        indicatorColor="secondary"
      >
        <Tab value="one" label="Account" />
        <Tab value="two" label="Manage Role" />
      </Tabs>
      <Divider />

      {tabValue === "one" && (
        <form onSubmit={handleSubmit}>
          <CardContent sx={{ p: 3, maxHeight: { md: "40vh" }, textAlign: { xs: "center", md: "start" } }}>
            <FormControl fullWidth>
              <Grid container spacing={3}>

                {/* First row */}
                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="firstname"
                    value={data.firstname}
                    title="First Name"
                    onChange={handleUserChange}
                    dis={edit.disabled}
                    error={errors.firstname}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="lastname"
                    value={data.lastname}
                    onChange={handleUserChange}
                    title="Last Name"
                    dis={edit.disabled}
                    error={errors.lastname}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="middlename"
                    value={data.middlename}
                    onChange={handleUserChange}
                    title="Middle Name"
                    dis={edit.disabled}
                    error={errors.middlename}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <Box>
                    <MuiTelInput
                      name="phone_number"
                      value={data.phone_number}
                      onChange={handleChangePhone}
                      fullWidth size="small"
                      disabled={edit.disabled}
                      helperText={errors.phone_number}
                      error={errors.phone_number} />
                  </Box>
                </Grid>

                {/* Third row */}
                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="email"
                    value={data.email}
                    title="Email Address"
                    dis={true}
                    error={errors.email}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomSelect
                    name="gender"
                    value={data.gender}
                    title="Gender"
                    onChange={handleChangeSelect}
                    options={[
                      { value: 'M', label: 'Male' },
                      { value: 'F', label: 'Female' },

                    ]}
                    dis={edit.disabled}
                    error={errors.example}
                  />
                </Grid>

                {canEdit && (
                  <Grid item xs={12}>
                    <Button
                      sx={{ p: "1rem 2rem", my: 2, height: "3rem" }}
                      component="button"
                      size="large"
                      variant="contained"
                      color="primary"
                      type="submit"
                      disabled={processing}
                    >
                      {processing ? "Saving..." : "Save"}
                    </Button>
                  </Grid>
                )}
              </Grid>
            </FormControl>
          </CardContent>
        </form>
      )}

      {tabValue === "two" && (
        <CardContent sx={{ p: 3, maxHeight: { md: "40vh" }, textAlign: { xs: "center", md: "start" }, width: "100%" }}>
          <AssignRoles userId={user.id} orgId={user.organization_id} />
        </CardContent>
      )}

      <SnackbarAlert
        open={snackbar.open}
        severity={snackbar.severity}
        message={snackbar.message}
        onClose={handleCloseSnackbar}
      />
    </Card>
  );
};

export default SettingsCard;
