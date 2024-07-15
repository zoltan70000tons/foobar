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
    <>
      <DialogTitle id="view-dialog-title">User Details</DialogTitle>
      <DialogContent>
          <Grid container direction="column" sx={{ overflowX: "hidden" }}>
            <Grid
              container
              direction={{ xs: "column", md: "row" }}
              spacing={3}
              sx={{
                position: "absolute",
                top: "20vh",
                px: { xs: 0, md: 7 },
              }}
            >
              <Grid item md={3}>
                <ProfileCard user={selectedUser}></ProfileCard>
              </Grid>
              <Grid item md={9}>
                <SettingsCard user={selectedUser}></SettingsCard>
              </Grid>
            </Grid>
          </Grid>
      </DialogContent>
    </>
  );
}
