import React, { useEffect, useState } from 'react';
import { Container, CircularProgress, Button, ButtonGroup, Box } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import axios from "axios";
import ActionMenu from './ActionMenu';
import { Link } from '@inertiajs/react';
import Chip from '@mui/material/Chip';
import Stack from '@mui/material/Stack';
import LoadingOverlay from '@/Components/LoadingOverlay';
import { useTeamData } from '@/Hooks/useTeamData';

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
}

const columns: GridColDef[] = [
  { field: 'id', headerName: 'ID', width: 70 },
  {
    field: 'name',
    headerName: 'Name',
    width: 200,
    renderCell: (params) => {
      const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
      const firstName = params.row.detail?.first_name ? capitalize(params.row.detail.first_name) : '';
      const lastName = params.row.detail?.last_name ? capitalize(params.row.detail.last_name) : '';
      return <>{`${firstName} ${lastName}`}</>;
    },
  },
  
  { field: 'email', headerName: 'Email', type: 'string', width: 200 },
  {
    field: 'status', headerName: 'Status', sortable: false, width: 100,
    renderCell: (params) => (
      <Stack direction="row"
        alignItems="center"
        height="100%"
      >
        <Chip label={params.row.status} color={params.row.status == "Active" ? "success" : "error"} />
      </Stack>
    ),

  },
  {
    field: 'roles',
    headerName: 'Role',
    width: 300,
    renderCell: (params) => (
      <Stack direction="row"
        alignItems="center"
        height="100%"
      >
        {params.row.roles.map((role: string, index: number) => (
          <Chip key={index} label={role} />
        ))}
      </Stack>
    ),
  },
  {
    field: 'actions',
    headerName: 'Actions',
    width: 150,
    renderCell: (params) => <ActionMenu params={params} />,
  },
];

const List: React.FC = () => {
  const { rows, fetchData, loading } = useTeamData();

  useEffect(() => {
    fetchData();
  }, []);

  

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <div style={{ height: 400, width: "100%" }}>
        {loading ? (
          <></>
        ) : (
          <DataGrid
            disableRowSelectionOnClick
            rows={rows}
            columns={columns}
            getRowId={(row) => row.id}
            initialState={{
              pagination: {
                paginationModel: { page: 0, pageSize: 5 },
              },
            }}
            pageSizeOptions={[5, 10]}
            checkboxSelection
          />
        )}
      </div>
      <LoadingOverlay open={loading} />
    </Container>
  );
};

export default List;
