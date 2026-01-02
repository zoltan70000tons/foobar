import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Toolbar, Paper, Grid } from "@mui/material";
import List from "@/Pages/Role/partials/List";
import NavigationTeam from "@/Components/NavigationTeam";
import buttonsConfig from "./Team/buttonsConfig";

export default function ManageRoles({ auth, loadingState }: PageProps) {
  return (
    <AuthenticatedLayout user={auth.user} header={"Team"}>
      <Head title="Team" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Paper sx={{ p: 2, display: "flex", flexDirection: "column" }}>
              <NavigationTeam buttonsConfig={buttonsConfig} />
              <List lodingState={loadingState} />
            </Paper>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
}
