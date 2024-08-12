import React, { useState } from "react";
import Card from "@mui/material/Card";
import Divider from "@mui/material/Divider";
import InputAdornment from "@mui/material/InputAdornment";
import IconButton from "@mui/material/IconButton";
import VisibilityOff from "@mui/icons-material/VisibilityOff";
import Visibility from "@mui/icons-material/Visibility";
import CardContent from "@mui/material/CardContent";
import { Grid } from "@mui/material";
import FormControl from "@mui/material/FormControl";
import Button from "@mui/material/Button";
import Tabs from "@mui/material/Tabs";
import Tab from "@mui/material/Tab";
import CustomInput from "./CustomInput";
import AssignRoles from "@/Components/AssignRoles";
import { usePermissions } from "@/Providers/PermissionContext";
import { useForm } from "@inertiajs/react";

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
}

interface SettingsCardProps {
  user: User;
}

const SettingsCard: React.FC<SettingsCardProps> = ({ user }) => {
  const { hasPermission } = usePermissions();
  const { data, setData, post, errors, processing } = useForm({
    id: user.id,
    email: user.email,
    phone_number: user.phone_number,
    pass: '',
    survivor_number: user.survivor_number || '',
    lastname: user.lastname || '',
    middlename: user.middlename || '',
    username: user.user_name || '',
    firstname: user.firstname || ''
  });

  const canEdit = hasPermission('Edit User');

  const handleUserChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setData(event.target.name, event.target.value);
  };

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
          console.log("User updated successfully");
        },
        onError: () => {
          console.error("Error updating user");
        },
      });
    }
  };

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
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="lastname"
                    value={data.lastname}
                    onChange={handleUserChange}
                    title="Last Name"
                    dis={edit.disabled}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="middlename"
                    value={data.middlename}
                    onChange={handleUserChange}
                    title="Middle Name"
                    dis={edit.disabled}
                  />
                </Grid>

                {/* Second row */}
                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="username"
                    value={data.username}
                    title="Username"
                    dis={true}  
                    InputProps={{ readOnly: true }} 
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="survivornumber"
                    value={data.survivor_number}
                    title="Survivor Number"
                    dis={true}  
                    InputProps={{ readOnly: true }}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="phone_number"
                    value={data.phone_number}
                    onChange={handleUserChange}
                    title="Phone Number"
                    dis={edit.disabled}
                    InputProps={{
                      startAdornment: <InputAdornment position="start">63+</InputAdornment>
                    }}
                  />
                </Grid>

                {/* Third row */}
                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="email"
                    value={data.email}
                    title="Email Address"
                    dis={true}  // Make this field read-only
                    InputProps={{ readOnly: true }} // Additional property to make it read-only
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
                      {processing ? "Saving..." : "EDIT"}
                    </Button>
                  </Grid>
                )}
              </Grid>
            </FormControl>
          </CardContent>
        </form>
      )}

      {tabValue === "two" && (
        <CardContent sx={{ p: 3, maxHeight: { md: "40vh" }, textAlign: { xs: "center", md: "start" } }}>
          <AssignRoles userId={user.id} orgId={user.organization_id} />
        </CardContent>
      )}
    </Card>
  );
};

export default SettingsCard;
