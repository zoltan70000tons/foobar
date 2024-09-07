import React, { useState, useEffect } from "react";
import {
  Container,
  IconButton,
  Button,
  Modal,
  TextField,
  Box,
  Typography,
  Chip,
  InputLabel,
  FormControl,
  Select,
  MenuItem,
  OutlinedInput,
  ButtonGroup,
} from "@mui/material";
import { DataGrid } from "@mui/x-data-grid";
import DeleteIcon from "@mui/icons-material/Delete";
import AddIcon from "@mui/icons-material/Add";
import SaveIcon from '@mui/icons-material/Save';
import SettingsIcon from '@mui/icons-material/Settings';
import { Link, useForm } from "@inertiajs/react";
import axios from "axios";
import apiRoutes from "@/Helpers/ApiRoutes";
import LoadingButton from "@mui/lab/LoadingButton";
import LoadingOverlay from '../../../Components/LoadingOverlay'; 
import {useTheme} from '@emotion/react';
import SnackbarAlert from "@/Components/SnackbarAlert";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";


const ITEM_HEIGHT = 48;
const ITEM_PADDING_TOP = 8;
const MenuProps = {
  PaperProps: {
    style: {
      maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
      width: 250,
    },
  },
};

const List = () => {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [newRole, setNewRole] = useState("");
  const [editOpen, setEditOpen] = useState(false);
  const [editRole, setEditRole] = useState(null);
  const [editLoading, setEditLoading] = useState(false);
  const [saveLoading, setSaveLoading] = useState(false);
  const [permissions, setPermissions] = useState([]);
  const [permissionName, setPermissionName] = useState([]);
  const [snackbar, setSnackbar] = useState({ open: false, severity: 'success', message: '' });

  const { hasPermission } = usePermissions();

  const createRolePermission = Permissions.CreateRoles;
  const editRolePermission = Permissions.EditRoles;
  const deleteRolePermission = Permissions.DeleteRoles;

  const theme = useTheme();
  const { delete: destroy } = useForm({
    id: '',
  });

  useEffect(() => {
    axios.get(apiRoutes.orgRolesUrl, { params: { org_id: 1 } })
      .then((response) => {
        const rolesData = response.data.data.map((role) => ({
          id: role.id,
          role: role.name,
          system: role.system,
        }));
        setRows(rolesData);
        setLoading(false);
      });
  }, []);

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  const handleEdit = async (role) => {
    setEditRole(role);
    try {
      const { data } = await axios.get(apiRoutes.permissionUrl, { params: { id: 1, role_id: role.id } });
      const allPermissions = data.data;
      const grantedPermissions = allPermissions.filter(p => p.granted).map(p => p.name);
      setPermissions(allPermissions);
      setPermissionName(grantedPermissions);
    } catch (error) {
      console.error("Error loading permissions:", error);
    }
    setEditOpen(true);
  };

  const handleDelete = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
      destroy(route('roles.destroy', id), {
        method: 'delete',
        onSuccess: () => {
          setSnackbar({ open: true, severity: 'success', message: 'Role deleted successfully' });
        },
        onError:() =>{
          setSnackbar({ open: true, severity: 'error', message: 'Error deleting permission' });
        }
      });
      setRows(rows.filter((row) => row.id !== id));

    }

  };

  const handleOpen = () => setOpen(true);
  const handleClose = () => {
    setOpen(false);
    setNewRole("");
  };

  const handleEditClose = () => {
    setEditOpen(false);
    setEditRole(null);
    setPermissions([]);
    setPermissionName([]);
  };

  const handleChange = (event) => setNewRole(event.target.value);
  const handleEditChange = (event) => setEditRole({ ...editRole, role: event.target.value });
  const handleChangeChips = (event) => setPermissionName(event.target.value);

  const handleSave = async () => {
    setSaveLoading(true);
    try {
      const { data } = await axios.post(apiRoutes.rolesUrl, { name: newRole });
      setRows([...rows, { id: data.data.id, role: data.data.name, system: data.data.system }]);
      handleClose();
      setSnackbar({ open: true, severity: 'success', message: 'Role added successfully' });
    } catch (error) {
      setSnackbar({ open: true, severity: 'error', message: 'Error adding role' });
    } finally {
      setSaveLoading(false);
    }
  };

  const handleEditSave = async () => {
    setEditLoading(true);
    try {
      const { data } = await axios.post(apiRoutes.addPermissionToRoleUrl, {
        name: editRole.role,
        permissions: permissions.map(p => ({
          name: p.name,
          granted: permissionName.includes(p.name),
        })),
        role_id: editRole.id,
        org_id: 1,
      });

      setRows(rows.map(row => row.id === data.data.id ? data.data : row));
      setSnackbar({ open: true, severity: 'success', message: 'Role updated successfully' });
      handleEditClose();
    } catch (error) {
      setSnackbar({ open: true, severity: 'error', message: 'Error updating role' });
    } finally {
      setEditLoading(false);
    }
  };

  const columns = [
    { field: "id", headerName: "ID", width: 70 },
    { field: "role", headerName: "Role", width: 800 },
    {
      field: "actions",
      headerName: "Actions",
      width: 150,
      renderCell: (params) => (
        <Box display="flex" justifyContent="flex-end">
           {hasPermission(editRolePermission) && (<IconButton color="primary" onClick={() => handleEdit(params.row)}>
            <SettingsIcon />
          </IconButton>)}
          {hasPermission(deleteRolePermission) &&(<IconButton
            color="secondary"
            onClick={() => handleDelete(params.row.id)}
            disabled={params.row.system}
          >
            <DeleteIcon />
          </IconButton>)}
          
        </Box>
      ),
    },
  ];

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      
          {hasPermission(createRolePermission) && (<Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 2 }}><Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={handleOpen}
        >
          Add Role
        </Button></Box>)}
      
      <div style={{ height: 400, width: "100%", position: "relative" }}>
        <DataGrid
            rows={rows}
            columns={columns}
            getRowId={(row) => row.id}
            initialState={{ pagination: { paginationModel: { page: 0, pageSize: 5 } } }}
            pageSizeOptions={[5, 10]}
            checkboxSelection
          />
      </div>
      <Modal open={open} onClose={handleClose}>
        <Box sx={{ position: "absolute", top: "50%", left: "50%", transform: "translate(-50%, -50%)", width: 400, bgcolor: "background.paper", border: "2px solid #000", boxShadow: 24, p: 4 }}>
          <Typography variant="h6">Add New Role</Typography>
          <TextField autoFocus margin="dense" label="Role" type="text" fullWidth value={newRole} onChange={handleChange} />
          <Box sx={{ mt: 2, display: "flex", justifyContent: "flex-end" }}>
            <Button onClick={handleClose} sx={{ mr: 1 }}>Cancel</Button>
            <LoadingButton loading={saveLoading} loadingPosition="start" startIcon={<SaveIcon />} variant="contained" color="primary" onClick={handleSave}>
              Save
            </LoadingButton>
          </Box>
        </Box>
      </Modal>

      <Modal open={editOpen} onClose={handleEditClose}>
  <Box
    sx={{
      position: "absolute",
      top: "50%",
      left: "50%",
      transform: "translate(-50%, -50%)",
      bgcolor: "background.paper",
      border: "2px solid #000",
      boxShadow: 24,
      p: 4,
      width: { xs: '90%', sm: '75%', md: '60%', lg: '50%', xl: '40%' }
    }}
  >
    <Typography variant="h6" gutterBottom>
      Edit Role
    </Typography>
    <TextField
      autoFocus
      fullWidth
      margin="dense"
      label="Role"
      type="text"
      value={editRole?.role || ""}
      onChange={handleEditChange}
    />
    <FormControl fullWidth sx={{ mt: 2 }}>
      <InputLabel>Permissions</InputLabel>
      <Select
        multiple
        value={permissionName}
        onChange={handleChangeChips}
        input={<OutlinedInput label="Permissions" />}
        renderValue={(selected) => (
          <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
            {selected.map((value) => (
              <Chip key={value} label={value} />
            ))}
          </Box>
        )}
        MenuProps={MenuProps}
      >
        {permissions.map((permission) => (
          <MenuItem key={permission.name} value={permission.name}>
            {permission.name}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
    <Box sx={{ mt: 2, display: "flex", justifyContent: "flex-end" }}>
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

      <SnackbarAlert
        open={snackbar.open}
        severity={snackbar.severity}
        message={snackbar.message}
        onClose={handleCloseSnackbar}
      />
      <LoadingOverlay open={loading} />
    </Container>
  );
};

export default List;