import React from "react";
import { Head, router, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
  Container,
  Paper,
  Grid,
  Toolbar,
  TextField,
  Box,
  Tooltip,
  IconButton,
  Button,
  Chip,
  Rating,
  Typography,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { ArrowBack, Delete, Edit } from "@mui/icons-material";
import { Permissions } from "@/enums/PermissionEnum";

const View = ({ auth, tag }: PageProps) => {
  const { get, delete: destroy } = useForm();
  const { hasPermission } = usePermissions();

  const handleEdit = () => {
    get(route("tags.edit", { tag: tag.id }));
  };

  const handleBack = () => {
    router.visit(route("tags.index"), {
      only: ["tag"],
    });
  };

  const handleDelete = () => {
    const confirmed = window.confirm("Are you sure you want to delete this tag?");
    if (confirmed) {
      destroy(route("tags.destroy", { tag: tag.id }));
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Tags"}>
      <Head title="View Tag" />
      <Toolbar sx={{ mt: 8 }}>
        <Button variant="outlined" color="secondary" onClick={handleBack}>
          Back
        </Button>
      </Toolbar>

      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          {hasPermission(Permissions.ViewTags) && (
            <>
              <Paper
                sx={{
                  p: 2,
                  display: "flex",
                  flexDirection: "column",
                  minHeight: 240,
                  width: "100%",
                }}
              >
                <Box sx={{ width: "100%" }}>
                  <Grid container spacing={2}>
                    <Grid item xs={12}>
                      <TextField
                        fullWidth
                        label="Name"
                        variant="outlined"
                        value={tag.name}
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={12}>
                      <TextField
                        fullWidth
                        label="Description"
                        variant="outlined"
                        value={tag.description}
                        InputProps={{ readOnly: true }}
                      />
                    </Grid>
                    <Grid item xs={12}>
                      <Typography component="legend">Priority</Typography>
                      <Rating name="priority" value={Number(tag.priority)} max={10} disabled />
                    </Grid>
                    <Grid item xs={8} sx={{ display: "flex", gap: "16px", height: "72px", alignItems: "center" }}>
                      <div style={{ display: "flex", alignItems: "center", gap: "16px" }}>
                        <span>Preview:</span>
                        <Chip
                          label={tag.name}
                          size="small"
                          sx={{
                            fontSize: "0.7rem",
                            fontWeight: 500,
                            backgroundColor: tag.color,
                            color: "#fff",
                          }}
                        />
                      </div>
                    </Grid>
                  </Grid>
                </Box>

                <Box sx={{ mt: 4 }}>
                  <div
                    style={{
                      display: "flex",
                      justifyContent: "flex-end",
                      gap: "8px",
                    }}
                  >
                    <Tooltip title="Back">
                      <IconButton color="primary" onClick={handleBack}>
                        <ArrowBack />
                      </IconButton>
                    </Tooltip>
                    {hasPermission(Permissions.EditTags) && (
                      <Tooltip title="Edit">
                        <IconButton color="primary" onClick={handleEdit}>
                          <Edit />
                        </IconButton>
                      </Tooltip>
                    )}
                    {hasPermission(Permissions.DeleteTags) && (
                      <Tooltip title="Delete">
                        <IconButton color="error" onClick={handleDelete}>
                          <Delete />
                        </IconButton>
                      </Tooltip>
                    )}
                  </div>
                </Box>
              </Paper>
            </>
          )}
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default View;
