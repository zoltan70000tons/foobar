import React, { useState, useEffect } from "react";
import { Container, IconButton, Button, Modal, TextField, Box, Typography, CircularProgress } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import axios from "axios";

const List = () => {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [newRole, setNewRole] = useState("");

//TODO configure this in .env
  const baseURL = "http://localhost:8000/api/organization/roles";
  const createURL = "http://localhost:8000/api/roles";
  
  useEffect(() => {
    axios.get(baseURL, { params: { id: '1' } }).then((response) => {
      const rolesData = response.data.data.map((roles: any) => ({
        id: roles.id,
        role: roles.name,
      }));
      setRows(rolesData);
      setLoading(false);
    });
  }, []);

  const handleEdit = (id: number) => {
    console.log(`Edit role with ID: ${id}`);
  };

  const handleDelete = (id: number) => {
    setRows(rows.filter(row => row.id !== id));
    console.log(`Delete role with ID: ${id}`);
  };

  const handleOpen = () => {
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
    setNewRole("");
  };

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setNewRole(event.target.value);
  };

  const handleSave = async () => {
    try {
      const response = await axios.post(createURL, { name: newRole });
      const newRoleData = response.data.data; 
      console.log(newRoleData);
      setRows([...rows, { id: newRoleData.id, role: newRoleData.name }]);
      handleClose();
    } catch (error) {
      console.error("Error creating new role:", error);
    }
  };

  const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 70 },
    {
      field: 'role',
      headerName: 'Role',
      type: 'string',
      width: 200,
    },
    {
      field: 'actions',
      headerName: 'Actions',
      width: 150,
      renderCell: (params) => (
        <div style={{ display: 'flex', justifyContent: 'flex-end', width: '100%' }}>
          <IconButton
            color="primary"
            onClick={() => handleEdit(params.row.id)}
          >
            <EditIcon />
          </IconButton>
          <IconButton
            color="secondary"
            onClick={() => handleDelete(params.row.id)}
          >
            <DeleteIcon />
          </IconButton>
        </div>
      ),
      sortable: false,
    },
  ];

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Button
        variant="contained"
        color="primary"
        startIcon={<AddIcon />}
        onClick={handleOpen}
        sx={{ mb: 2 }}
      >
        Add Role
      </Button>
      <div style={{ height: 400, width: "100%", position: "relative" }}>
        {loading ? (
          <Box
            sx={{
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              height: '100%',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              backgroundColor: 'rgba(255, 255, 255, 0.5)',
              zIndex: 1,
            }}
          >
            <CircularProgress />
          </Box>
        ) : (
          <DataGrid
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

      <Modal
        open={open}
        onClose={handleClose}
        aria-labelledby="simple-modal-title"
        aria-describedby="simple-modal-description"
      >
        <Box sx={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: 400, bgcolor: 'background.paper', border: '2px solid #000', boxShadow: 24, p: 4 }}>
          <Typography variant="h6" component="h2" id="simple-modal-title">
            Add New Role
          </Typography>
          <TextField
            autoFocus
            margin="dense"
            label="Role"
            type="text"
            fullWidth
            value={newRole}
            onChange={handleChange}
          />
          <Box sx={{ mt: 2, display: 'flex', justifyContent: 'flex-end' }}>
            <Button onClick={handleClose} sx={{ mr: 1 }}>Cancel</Button>
            <Button variant="contained" color="primary" onClick={handleSave}>Save</Button>
          </Box>
        </Box>
      </Modal>
    </Container>
  );
};

export default List;
