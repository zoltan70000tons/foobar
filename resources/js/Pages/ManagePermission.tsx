import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Toolbar, Paper, Grid } from "@mui/material";
import List from "@/Pages/Permission/partials/List";
import NavigationTeam from "@/Components/NavigationTeam";
import buttonsConfig from "./Team/buttonsConfig";

export default function ManagePermission({ auth }: PageProps) {
  return (
    <AuthenticatedLayout user={auth.user} header={"Team"}>
      <Head title="Team" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Paper sx={{ p: 2, display: "flex", flexDirection: "column" }}>
              <NavigationTeam buttonsConfig={buttonsConfig} />
              <List />
            </Paper>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
}
