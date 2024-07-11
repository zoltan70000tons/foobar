import React,{useEffect} from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/types";
import { Container, Toolbar, Paper, Grid } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import axios from "axios";

const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 70 },
    { field: 'userName', headerName: 'Name', 
     width: 200 
    },
    {
      field: 'email',
      headerName: 'Email',
      type: 'string',
      width: 200,
    },
    {
      field: 'status',
      headerName: 'Status',
      description: '',
      sortable: false,
      width: 100,
      //valueGetter: (value, row) => `${row.firstName || ''} ${row.lastName || ''}`,
    },
    {
      field: 'role',
      headerName: 'Role',
      type: 'string',
      width: 200,
    }
  ];
  
  const rows = [
    { id: 1, userName: 'Andy Piller', email: 'andy@7000tons.com', status: 'Active', role: 'Admin'},
    { id: 2, userName: 'Tobias Smith', email: 'tobias@7000tons.com', status: 'Inactive', role: 'Customer Service'},
    { id: 3, userName: 'Carlos Castañeda', email: 'carlos@7000tons.com', status: 'Active', role: 'TI'},
    { id: 4, userName: 'Andrew Shepherd', email: 'andrew@7000tons.com', status: 'Active', role: 'TI'},
    { id: 5, userName: 'Leonardo Bonilla', email: 'leonardo@7000tons.com', status: 'Invited', role: 'TI' },
    { id: 6, userName: 'Jacek Gajewsk', email: 'jacek@7000tons.com', status: 'Active', role: 'TI' },
    { id: 7, userName: 'Nicanor Errazuriz', email: 'nicanor@7000tons.com', status: 'Inactive', role: 'TI'},
    
    
    
  ];

export default function List() {

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
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
}
