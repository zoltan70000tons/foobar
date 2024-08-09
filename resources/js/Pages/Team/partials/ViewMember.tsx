import React from "react";
import {
  DialogContent,
  DialogTitle,
  Grid,
} from "@mui/material";
import ProfileCard from "./ProfileCard";
import SettingsCard from "./SettingsCard";
import CssBaseline from "@mui/material/CssBaseline";
interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
  organization_id: number;
  organization_name: string;
}

interface ViewMemberProps {
  selectedUser: User | null;
}

export default function ViewMember({ selectedUser }: ViewMemberProps) {
  return (
          <Grid container >
            <Grid
              container
              direction={{ xs: "column", md: "row" }}
              spacing={2}
            >
              <Grid item md={3}>
                <ProfileCard user={selectedUser}></ProfileCard>
              </Grid>
              <Grid item md={9}>
                <SettingsCard user={selectedUser}></SettingsCard>
              </Grid>
            </Grid>
          </Grid>
  );
}
