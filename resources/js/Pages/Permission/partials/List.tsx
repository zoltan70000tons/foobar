import React, { useState, useEffect } from "react";
import { Container, IconButton, Button, Modal, TextField, Box, Typography, CircularProgress, ButtonGroup } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import apiRoutes from "@/Helpers/ApiRoutes";
import axios from "axios";
import useAxiosWithToken from "@/Hooks/useAxiosWithToken";
import { Link } from "@inertiajs/react";

const List = () => {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [newPermission, setNewPermission] = useState("");

  useAxiosWithToken();

  useEffect(() => {
    axios.get(apiRoutes.permissionUrl, { params: { id: '1' } }).then((response) => {
      const permissionsData = response.data.data.map((permission: any) => ({
        id: permission.id,
        permission: permission.name,
      }));
      setRows(permissionsData);
      setLoading(false);
    });
  }, []);

  const handleEdit = (id: number) => {
    console.log(`Edit permission with ID: ${id}`);
  };

  const handleDelete = (id: number) => {
    setRows(rows.filter(row => row.id !== id));
    console.log(`Delete permission with ID: ${id}`);
  };

  const handleOpen = () => {
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
    setNewPermission("");
  };

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setNewPermission(event.target.value);
  };

  const handleSave = async () => {
    try {
      const response = await axios.post(apiRoutes.orgPermissionUrl, { name: newPermission });
      const newPermissionData = response.data.data;
      console.log(newPermissionData);
      setRows([...rows, { id: newPermissionData.id, permission: newPermissionData.name }]);
      handleClose();
    } catch (error) {
      console.error("Error creating new permission:", error);
    }
  };

  const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 70 },
    {
      field: 'permission',
      headerName: 'Permission',
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

  const buttons = [
    <Link href="/70k/team/" key="Team" >
      <Button >Team</Button>
    </Link>,
    <Link href="/70k/team/roles" key="Roles">
      <Button>Roles</Button>
    </Link>,
    <Link href="/70k/team/permissions" key="Permissions">
      <Button variant="contained">Permissions</Button>
    </Link>
  ];


  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
       <Box>
       <ButtonGroup style={{marginBottom: '1rem'}} disableElevation size="small" aria-label="Small button group" variant="outlined">
        {buttons}
      </ButtonGroup>
       </Box>
       <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 2 }}>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={handleOpen}
        >
          Add Permission
        </Button>
      </Box>
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
        aria-labelledby="edit-permissions"
        aria-describedby="edit-modal-permissions"
      >
        <Box sx={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: 400, bgcolor: 'background.paper', border: '2px solid #000', boxShadow: 24, p: 4 }}>
          <Typography variant="h6" component="h2" id="edit-modal-permission">
            Add New Permission
          </Typography>
          <TextField
            autoFocus
            margin="dense"
            label="Permission"
            type="text"
            fullWidth
            value={newPermission}
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
