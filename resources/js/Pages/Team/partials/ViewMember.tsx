import React from "react";
import {
  Grid,
} from "@mui/material";
import ProfileCard from "./ProfileCard";
import SettingsCard from "./SettingsCard";
import { User } from "@/interfaces/User";


interface ViewMemberProps {
  selectedUser: User;
}

export default function ViewMember({ selectedUser }: ViewMemberProps) {
  return (
    <Grid
      container
      direction={{ xs: "column", md: "row" }}
      spacing={2}
    >
      <Grid item md={3}>
        <ProfileCard user={selectedUser}></ProfileCard>
      </Grid>
      <Grid item md={9} sx={{ width: '100%' }}>
        <SettingsCard user={selectedUser}  ></SettingsCard>
      </Grid>
    </Grid>
  );
}
