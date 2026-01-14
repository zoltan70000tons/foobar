import React, { useEffect, useMemo, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router, useForm } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Grid, Toolbar, Box, Button, Chip, Rating } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import "dayjs/locale/en";
import { Permissions } from "@/enums/PermissionEnum";
import MuiTable from "@/Components/tables/MuiTable";
import { Visibility } from "@mui/icons-material";

const Index = ({ auth, tags }: PageProps) => {
  const { hasPermission } = usePermissions();
  const { get } = useForm();

  const columns = useMemo(
    () => [
      {
        header: "Name",
        accessor: "name",
        filterable: true,
        sortable: true,
        //width: '26%',
      },
      {
        accessor: "description",
        header: "Description",
        filterable: true,
        sortable: true,
        //width: '17%',
      },
      {
        accessor: "color",
        header: "Preview",
        filterable: false,
        sortable: false,
        //width: '17%',
        draw: (row) => (
          <Chip
            label={row.name}
            size="small"
            sx={{
              fontSize: "0.7rem",
              fontWeight: 500,
              backgroundColor: row.color,
              color: "#fff",
            }}
          />
        ),
      },
      {
        header: "Type",
        accessor: "type",
        filterable: true,
        sortable: true,
        //width: '17%',
      },
      {
        header: "Priority",
        accessor: "priority",
        sortable: true,
        draw: (row) => <Rating value={row.priority} max={10} readOnly size="small" />,
      },
      {
        header: "Actions",
        accessor: "id",
        disableFilter: true,
        //width: '13%',
        draw: (row) => (
          <div style={{ display: "flex", gap: "10px" }}>
            {hasPermission(Permissions.ViewTags) && (
              <Visibility
                onClick={() => {
                  router.get(route("tags.show", { tag: row.id }));
                }}
                style={{ cursor: "pointer" }}
              />
            )}
          </div>
        ),
      },
    ],
    [],
  );

  const handleCreate = () => {
    get(route("tags.create", {}));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={"Tags"}>
      <Head title="Tags" />
      <Toolbar sx={{ mt: 8 }}>
        {hasPermission(Permissions.CreateTags) && (
          <Button variant="outlined" color="secondary" onClick={handleCreate}>
            New Tag
          </Button>
        )}
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              <Box>{tags ? <MuiTable columns={columns} data={tags} showCheckBox={false} /> : <></>}</Box>
            </Box>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
