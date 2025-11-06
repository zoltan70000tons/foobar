import React, { useState, useEffect } from 'react';
import { Container, Button, Modal, TextField, Box, Typography, IconButton } from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import SaveIcon from '@mui/icons-material/Save';
import axios from 'axios';
import LoadingButton from '@mui/lab/LoadingButton';
import apiRoutes from '@/Helpers/ApiRoutes';
import { useForm } from '@inertiajs/react';
import { usePermissions } from '@/Providers/PermissionContext';
import { Permissions } from '@/enums/PermissionEnum';
import { useSnackbar } from '@/Providers/SnackBarAlertProvider';

const List = () => {
  const [rows, setRows] = useState([]);
  const [open, setOpen] = useState(false);
  const [newPermission, setNewPermission] = useState('');
  const [editOpen, setEditOpen] = useState(false);
  const [editPermission, setEditPermission] = useState(null);
  const [saveLoading, setSaveLoading] = useState(false);
  const [editLoading, setEditLoading] = useState(false);
  const { hasPermission } = usePermissions();
  const createPermission = Permissions.CreatePermissions;
  const updatePermission = Permissions.EditPermissions;
  const deletePermission = Permissions.DeletePermissions;
  const { delete: destroy } = useForm({
    id: '',
  });

  const { showSnackbar } = useSnackbar();

  useEffect(() => {
    axios
      .get(apiRoutes.orgPermissionUrl, { params: { org_id: 1 } })
      .then((response) => {
        const rolesData = response.data.data.map((roles) => ({
          id: roles.id,
          role: roles.name,
          system: roles.system,
        }));
        setRows(rolesData);
      })
      .catch((error) => {
        console.error('Error fetching permissions:', error);
      });
  }, []);

  const sanitizeInput = (input) => {
    const dangerousPattern = /['";<>\\\/`&{}[\]()=|%+*^$#@!]/g;
    return input.replace(dangerousPattern, '').trim();
  };

  const handleEdit = async (permission) => {
    setEditPermission(permission);
    setEditOpen(true);
  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => {
    setOpen(false);
    setNewPermission('');
  };
  const handleEditClose = () => {
    setEditOpen(false);
    setEditPermission(null);
  };

  const handleChange = (event) => {
    const sanitizedValue = sanitizeInput(event.target.value);
    setNewPermission(sanitizedValue);
  };

  const handleEditChange = (event) => {
    if (editPermission) {
      const sanitizedValue = sanitizeInput(event.target.value);
      setEditPermission({ ...editPermission, role: sanitizedValue });
    }
  };

  const handleSave = async () => {
    try {
      setSaveLoading(true);
      const response = await axios.post(apiRoutes.orgPermissionUrl, { name: newPermission });
      const newPermissionData = response.data.data;
      setRows([...rows, { id: newPermissionData.id, role: newPermissionData.name, system: newPermissionData.system }]);
      setSaveLoading(false);
      showSnackbar('Permission created successfully', 'success');
      handleClose();
    } catch (error) {
      showSnackbar('Error creating permission', 'error');
      setSaveLoading(false);
    }
  };

  const handleEditSave = async () => {
    if (!editPermission) return;
    try {
      setEditLoading(true);
      const response = await axios.put(`${apiRoutes.orgPermissionUrl}/${editPermission.id}`, {
        name: editPermission.role,
      });
      const updatedPermission = response.data.data;

      setRows(rows.map((row) => (row.id === updatedPermission.id ? { ...row, role: updatedPermission.name } : row)));

      setEditLoading(false);
      showSnackbar('Permission updated successfully', 'success');
      handleEditClose();
    } catch (error) {
      showSnackbar('Error updating permission', 'error');
      setEditLoading(false);
    }
  };

  const handleDelete = (id) => {
    if (confirm('Are you sure you want to delete this item?')) {
      axios
        .delete(route('permissions.destroy', id))
        .then(() => {
          setRows(rows.filter((row) => row.id !== id));
          showSnackbar('Permission deleted successfully', 'success');
        })
        .catch((error) => {
          showSnackbar('Error deleting permission', 'error');
        });
    }
  };

  const columns = [
    { field: 'id', headerName: 'ID', width: 70 },
    { field: 'role', headerName: 'Permission', width: 800, flex: 1  },
    {
      field: 'actions',
      headerName: 'Actions',
      width: 150,
      headerAlign: 'right',
      align: 'right',
      renderCell: (params) => (
        <Box display="flex" justifyContent="flex-end" width="100%">
        <div style={{ display: 'flex', justifyContent: 'flex-end', width: '100%' }}>
          {hasPermission(updatePermission) && (
            <IconButton color="primary" onClick={() => handleEdit(params.row)} disabled={params.row.system}>
              <EditIcon />
            </IconButton>
          )}
          {hasPermission(deletePermission) && (
            <IconButton color="secondary" onClick={() => handleDelete(params.row.id)} disabled={params.row.system}>
              <DeleteIcon />
            </IconButton>
          )}
        </div>
        </Box>
      ),
      sortable: false,
    },
  ];

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      {hasPermission(createPermission) && (
        <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 2 }}>
          <Button variant="contained" color="primary" startIcon={<AddIcon />} onClick={handleOpen}>
            Add Permission
          </Button>
        </Box>
      )}

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
        //checkboxSelection
      />

      <Modal open={open} onClose={handleClose} aria-labelledby="add-permission-modal-title">
        <Box
          sx={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            width: 400,
            bgcolor: 'background.paper',
            border: '2px solid #000',
            boxShadow: 24,
            p: 4,
          }}
        >
          <Typography variant="h6" component="h2" id="add-permission-modal-title">
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
            <Button onClick={handleClose} sx={{ mr: 1 }}>
              Cancel
            </Button>
            <LoadingButton
              loading={saveLoading}
              loadingPosition="start"
              startIcon={<SaveIcon />}
              variant="contained"
              color="primary"
              onClick={handleSave}
            >
              Save
            </LoadingButton>
          </Box>
        </Box>
      </Modal>

      <Modal open={editOpen} onClose={handleEditClose} aria-labelledby="edit-permission-modal-title">
        <Box
          sx={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            bgcolor: 'background.paper',
            border: '2px solid #000',
            boxShadow: 24,
            p: 4,
            width: { xs: '90%', sm: '75%', md: '60%', lg: '50%', xl: '40%' },
          }}
        >
          <Typography variant="h6" component="h2" id="edit-permission-modal-title">
            Edit Permission
          </Typography>
          <TextField
            autoFocus
            margin="dense"
            label="Permission"
            type="text"
            fullWidth
            value={editPermission?.role || ''}
            onChange={handleEditChange}
          />
          <Box sx={{ mt: 2, display: 'flex', justifyContent: 'flex-end' }}>
            <Button onClick={handleEditClose} sx={{ mr: 1 }}>
              Cancel
            </Button>
            <LoadingButton
              loading={editLoading}
              loadingPosition="start"
              startIcon={<SaveIcon />}
              variant="contained"
              color="primary"
              onClick={handleEditSave}
            >
              Save
            </LoadingButton>
          </Box>
        </Box>
      </Modal>
      {/* <LoadingOverlay open={loading} /> */}
    </Container>
  );
};

export default List;
