import React, { useState } from "react";
import Card from "@mui/material/Card";
import Divider from "@mui/material/Divider";
import InputAdornment from "@mui/material/InputAdornment";
import MenuItem from "@mui/material/MenuItem";
import IconButton from "@mui/material/IconButton";
import VisibilityOff from "@mui/icons-material/VisibilityOff";
import Visibility from "@mui/icons-material/Visibility";
import CardContent from "@mui/material/CardContent";
import { Grid, Chip } from "@mui/material";
import FormControl from "@mui/material/FormControl";
import Button from "@mui/material/Button";
import Tabs from "@mui/material/Tabs";
import Tab from "@mui/material/Tab";
import CustomInput from "./CustomInput";
import AssignRoles from "@/Components/AssignRoles";

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
  organization_id: number;
  organization_name: string;
}

interface SettingsCardProps {
  user: User;
}

const SettingsCard: React.FC<SettingsCardProps> = ({ user }) => {
  const [userState, setUserState] = useState<User>(user);

  const handleUserChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setUserState({ ...userState, [event.target.name]: event.target.value });
  };

  const [edit, setEdit] = useState({
    disabled: true,
    isEdit: true,
    showPassword: false
  });

  const [tabValue, setTabValue] = useState("one");

  const handleTabChange = (event: React.SyntheticEvent, newValue: string) => {
    setTabValue(newValue);
  };

  const changeButton = (event: any) => {
    event.preventDefault();
    setEdit({
      ...edit,
      disabled: !edit.disabled,
      isEdit: !edit.isEdit,
      showPassword: false
    });
  };

  const handlePasswordVisibility = () => {
    setEdit({ ...edit, showPassword: !edit.showPassword });
  };

  const genderSelect = [
    { value: "male", label: "Male" },
    { value: "female", label: "Female" }
  ];

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
        {/* <Tab value="three" label="Permissions" /> */}
      </Tabs>
      <Divider />

      {tabValue === "one" && (
        <form>
          <CardContent sx={{ p: 3, maxHeight: { md: "40vh" }, textAlign: { xs: "center", md: "start" } }}>
            <FormControl fullWidth>
              <Grid container direction={{ xs: "column", md: "row" }} columnSpacing={5} rowSpacing={3}>
                <Grid item xs={6}>
                  <CustomInput
                    name="firstName"
                    value={userState.name}
                    title="First Name"
                    onChange={handleUserChange}
                    dis={edit.disabled}
                  />
                </Grid>

                <Grid item xs={6}>
                  <CustomInput
                    name="lastName"
                    value={userState.name}
                    onChange={handleUserChange}
                    title="Last Name"
                    dis={edit.disabled}
                  />
                </Grid>

                <Grid item xs={6}>
                  <CustomInput
                    name="midName"
                    value={userState.name}
                    onChange={handleUserChange}
                    title="Middle Name"
                    dis={edit.disabled}
                  />
                </Grid>

                <Grid item xs={6}>
                  <CustomInput
                    name="phone"
                    value={userState.phone}
                    onChange={handleUserChange}
                    title="Phone Number"
                    dis={edit.disabled}
                    InputProps={{
                      startAdornment: <InputAdornment position="start">63+</InputAdornment>
                    }}
                  />
                </Grid>

                <Grid item xs={6}>
                  <CustomInput
                    name="email"
                    value={userState.email}
                    onChange={handleUserChange}
                    title="Email Address"
                    dis={edit.disabled}
                  />
                </Grid>

                <Grid item xs={6}>
                  <CustomInput
                    name="pass"
                    value={userState.pass}
                    onChange={handleUserChange}
                    title="Password"
                    dis={edit.disabled}
                    type={edit.showPassword ? "text" : "password"}
                    InputProps={{
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton onClick={handlePasswordVisibility} edge="end" disabled={edit.disabled}>
                            {edit.showPassword ? <VisibilityOff /> : <Visibility />}
                          </IconButton>
                        </InputAdornment>
                      )
                    }}
                  />
                </Grid>

                <Grid container justifyContent={{ xs: "center", md: "flex-end" }} item xs={6}>
                  <Button
                    sx={{ p: "1rem 2rem", my: 2, height: "3rem" }}
                    component="button"
                    size="large"
                    variant="contained"
                    color="secondary"
                    onClick={changeButton}
                  >
                    {edit.isEdit ? "EDIT" : "UPDATE"}
                  </Button>
                </Grid>
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
