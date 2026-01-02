import React from "react";
import { Grid } from "@mui/material";
import ProfileCard from "./ProfileCard";
import SettingsCard from "./SettingsCard";
import { User } from "@/interfaces/User";

interface ViewMemberProps {
  selectedUser: User;
  onUpdate: () => void;
}

export default function ViewMember({ selectedUser, onUpdate }: ViewMemberProps) {
  return (
    <Grid container direction={{ xs: "column", md: "row" }} sx={{ width: "100%", margin: "0", mt: 0 }}>
      <Grid item md={3} sx={{ p: 0 }}>
        <ProfileCard user={selectedUser}></ProfileCard>
      </Grid>
      <Grid item md={9} sx={{ width: "100%", paddingTop: "0px" }}>
        <SettingsCard user={selectedUser} onUpdate={onUpdate}></SettingsCard>
      </Grid>
    </Grid>
  );
}
