import React, { useEffect, useState } from 'react';
import { Container, CircularProgress, Button, ButtonGroup, Box } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import axios from "axios";
import ActionMenu from './ActionMenu';
import apiRoutes from '@/Helpers/ApiRoutes';
import useAxiosWithToken from '@/Hooks/useAxiosWithToken';
import { Link } from '@inertiajs/react';
import Chip from '@mui/material/Chip';
import Stack from '@mui/material/Stack';

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
}

const columns: GridColDef[] = [
  { field: 'id', headerName: 'ID', width: 70 },
  { field: 'name', headerName: 'Name', width: 200 },
  { field: 'email', headerName: 'Email', type: 'string', width: 200 },
  { field: 'status', headerName: 'Status', sortable: false, width: 100 },
  {
    field: 'roles',
    headerName: 'Roles',
    width: 300,
    renderCell: (params) => (
      <Stack direction="row" spacing={1}>
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
  const [rows, setRows] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  useAxiosWithToken();

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios.get<User[]>(apiRoutes.getTeamUrl, { params: { org_id: 1 } });
        const processedData = response.data.map(user => ({
          ...user,
        }));
        setRows(processedData);
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return (
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4, display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <CircularProgress />
      </Container>
    );
  }

  const buttons = [
    <Link href="/70k/team/" key="Team" >
      <Button variant="contained">Team</Button>
    </Link>,
    <Link href="/70k/team/roles" key="Roles">
      <Button>Roles</Button>
    </Link>,
    <Link href="/70k/team/permissions" key="Permissions">
      <Button>Permissions</Button>
    </Link>
  ];

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Box>
        <ButtonGroup style={{ marginBottom: '1rem' }} disableElevation size="small" aria-label="Small button group" variant="outlined">
          {buttons}
        </ButtonGroup>
      </Box>
      <div style={{ height: 400, width: "100%" }}>
        <DataGrid
          rows={rows}
          columns={columns}
          initialState={{
            pagination: {
              paginationModel: { page: 0, pageSize: 5 },
            },
          }}
          pageSizeOptions={[5, 10]}
          checkboxSelection
        />
      </div>
    </Container>
  );
};

export default List;
