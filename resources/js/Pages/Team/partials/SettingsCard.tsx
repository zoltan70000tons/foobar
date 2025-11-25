import React, { ChangeEvent, useState } from "react";
import { Card, CardContent, Divider, Grid, Button, Tabs, Tab, Box, Typography, Chip, Avatar } from "@mui/material";
import FormControl from "@mui/material/FormControl";
import CustomInput from "./CustomInput";
import AssignRoles from "@/Components/AssignRoles";
import { usePermissions } from "@/Providers/PermissionContext";
import { router, useForm, usePage } from "@inertiajs/react";
import { useTeamData } from "@/Hooks/useTeamData";
import CustomSelect from "./CustomSelect";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { Permissions } from "@/enums/PermissionEnum";
import PhoneNumber from "@/Components/PhoneNumber";
import { User } from "@/interfaces/User";
import PersonAddIcon from '@mui/icons-material/PersonAdd';

interface SettingsCardProps {
  user: User;
  onUpdate: () => void;
}

interface TForm {
  id: number;
  email: string;
  phone_number?: string;
  pass: string;
  survivor_number: string;
  lastname: string;
  middlename: string;
  username: string;
  firstname: string;
  gender: string;
}

const SettingsCard: React.FC<SettingsCardProps> = ({ user, onUpdate }) => {
  const { hasPermission } = usePermissions();
  const fetchData = onUpdate;
  const { errors } = usePage().props;
  const { showSnackbar } = useSnackbar();

  const { data, setData, post, processing } = useForm<TForm>({
    id: user.id,
    email: user.email,
    phone_number: user?.detail?.phone || '',
    pass: '',
    survivor_number: user.survivor_number || '',
    lastname: user?.detail?.last_name || '',
    middlename: user?.detail?.middle_name || '',
    username: user.username || '',
    firstname: user?.detail?.first_name || '',
    gender: user?.detail?.gender || ''
  });

  const canEdit = hasPermission(Permissions.EditUsers);

  const { data: colorData, setData: setColorData, put: putColor, processing: processingColor } = useForm({
    badge: {
      text: user?.detail?.avatar?.badge?.text || "#FFFFFF",
      background: user?.detail?.avatar?.badge?.background || "#007BFF",
    },
  });

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    putColor(route("users.avatar.update", user.id), {
      preserveScroll: true,
      onSuccess: () => {
      showSnackbar("Colors updated!", "success");
      fetchData();
    },onError: () => showSnackbar("Error saving colors", "error"),
    });
  };



  const handleUserChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = event.target;
    setData(name, value);
  };

  const handleChangePhone = (value: string) => {
    setData('phone_number', value)
  };

  const [tabValue, setTabValue] = useState("one");

  const handleTabChange = (event: React.SyntheticEvent, newValue: string) => {
    setTabValue(newValue);
  };

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    if (canEdit) {
      post(route('member.update', user.id), {
        onSuccess: () => {
          showSnackbar("User updated successfully", "success");
          fetchData();
        },
        onError: () => {
          showSnackbar('Error updating user', "error");
        },
      });
    }
  };

  const handleGenderChange = (value: object) => {
    setData('gender', value.target.value);
  }
  return (
    <Card variant="outlined" sx={{ marginTop: '0.02rem', borderRadius: 0, height: "99.9%", width: "100%", background: "#383838", border: "2px solid grey", borderLeft: { xs: '2px solid grey', md: 'none' }, marginBottom: "1rem" }}>
      <Tabs
        value={tabValue}
        onChange={handleTabChange}
        textColor="secondary"
        indicatorColor="secondary"
      >
        <Tab value="one" label="Account" />
        <Tab value="two" label="Manage Role" />
        {canEdit && (<Tab value="three" label="Styling" />)}
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
                    value={data?.firstname || ""}
                    title="First Name"
                    onChange={handleUserChange}
                    dis={!canEdit}
                    error={errors.firstname}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="lastname"
                    value={data?.lastname || ""}
                    onChange={handleUserChange}
                    title="Last Name"
                    dis={!canEdit}
                    error={errors.lastname}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomInput
                    name="middlename"
                    value={data?.middlename || ""}
                    onChange={handleUserChange}
                    title="Middle Name"
                    dis={!canEdit}
                    error={errors.middlename}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <Box>
                    <PhoneNumber value={data?.phone_number || ""}
                      sx={{ width: '100%', maxHeight: '20px' }}
                      onChange={(value) => handleChangePhone(value)}
                      forceDialCode={true}
                      error={errors.phone_number}
                      helperText={errors.phone_number}
                    />
                  </Box>
                </Grid>

                {/* Third row */}
                <Grid item xs={12} md={4} sx={{ mt: { xs: 4, md: 0 } }}>
                  <CustomInput
                    name="email"
                    value={data?.email || ""}
                    title="Email Address"
                    dis={true}
                    error={errors.email}
                  />
                </Grid>

                <Grid item xs={12} md={4}>
                  <CustomSelect
                    name="gender"
                    value={data?.gender || ""}
                    title="Gender"
                    onChange={handleGenderChange}
                    options={[
                      { value: '', label: '-' },
                      { value: 'M', label: 'Male' },
                      { value: 'F', label: 'Female' },

                    ]}
                    dis={!canEdit}
                    error={errors.gender}
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

      {tabValue === "three" && canEdit && (
        <CardContent sx={{ p: 3, maxHeight: { md: "40vh" }, textAlign: { xs: "center", md: "start" }, width: "100%" }}>
          <Box sx={{ display: "flex", flexDirection: "column", gap: 2, mt: 2 }}>
            <Typography variant="h6">Badge Color</Typography>

            <Box sx={{ display: "flex", gap: 3 }}>
              <Box>
                <Typography variant="body2">Background color</Typography>
                <input
                  type="color"
                  value={colorData.badge.background}
                  onChange={(e) =>
                    setColorData("badge", {
                      ...colorData.badge,
                      background: e.target.value,
                    })
                  }
                />
              </Box>

              <Box>
                <Typography variant="body2">Text color</Typography>
                <input
                  type="color"
                  value={colorData.badge.text}
                  onChange={(e) =>
                    setColorData("badge", {
                      ...colorData.badge,
                      text: e.target.value,
                    })
                  }
                />
              </Box>
            </Box>


            <Box sx={{ display: "inline-flex", gap: 0.5 }}>
              <Chip
                label={user.user_name || "Agent Name"}
                avatar={user?.user_name ? <Avatar>{user.user_name[0]}</Avatar> : <PersonAddIcon />}
                size="small"
                sx={{
                  fontSize: "0.75rem",
                  fontWeight: 500,
                  color: colorData.badge.text,
                  backgroundColor: colorData.badge.background,
                  "& .MuiChip-label": { px: 1.5 },
                }}
              />


            </Box>

            <Button
              variant="contained"
              color="primary"
              sx={{ mt: 3, alignSelf: "flex-start" }}
              onClick={handleSave}
            >
              Save Colors
            </Button>
            <p style={{ visibility: "hidden" }}>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Veniam dicta fugit adipisci quisquam sed soluta quo, dolor dolores quasi eveniet animi beatae ducimus, itaque est doloribus pariatur cupiditate atque? Sit?</p>
          </Box>
        </CardContent>
      )}




    </Card>
  );
};

export default SettingsCard;
