import React, { useState, useEffect } from "react";
import {
  Container,
  IconButton,
  Button,
  Modal,
  TextField,
  Box,
  Typography,
  CircularProgress,
  Chip,
  InputLabel,
  FormControl,
  Select,
  MenuItem,
  OutlinedInput,
  ButtonGroup,
} from "@mui/material";
import { DataGrid, GridColDef } from "@mui/x-data-grid";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import AddIcon from "@mui/icons-material/Add";
import axios from "axios";
import { useTheme } from "@emotion/react";
import LoadingButton from "@mui/lab/LoadingButton";
import SaveIcon from '@mui/icons-material/Save';
import apiRoutes from "@/Helpers/ApiRoutes";
import useAxiosWithToken from '@/Hooks/useAxiosWithToken';
import SettingsIcon from '@mui/icons-material/Settings';
import { Link } from "@inertiajs/react";
import LoadingOverlay from '../../../Components/LoadingOverlay'; 
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

function getStyles(name, personName, theme) {
  return {
    fontWeight:
      personName.indexOf(name) === -1
        ? theme.typography.fontWeightRegular
        : theme.typography.fontWeightMedium,
  };
}

const List = () => {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [newRole, setNewRole] = useState("");
  const [editOpen, setEditOpen] = useState(false);
  const [editRole, setEditRole] = useState(null);
  const [editLoading, setEditLoading] = useState(false);
  const [saveLoading, setSaveLoading] = useState(false);
  const [modalLoading, setModalLoading] = useState(false);
  const [permissions, setPermissions] = useState([]);
  const theme = useTheme();
  const [permissionName, setPermissionName] = useState([]);

  //useAxiosWithToken();

  useEffect(() => {
    axios.get(apiRoutes.orgRolesUrl, { params: {  org_id:1} }).then((response) => {
      const rolesData = response.data.data.map((roles) => ({
        id: roles.id,
        role: roles.name,
        system: roles.system,
      }));
      setRows(rolesData);
      setLoading(false);
    });
  }, []);

  const handleEdit = async (role) => {
    setLoading(true);
    setEditRole(role);
    try {
      const response = await axios.get(apiRoutes.permissionUrl, {
        params: { id: 1, role_id: role.id },
      });
      const allPermissions = response.data.data;

      // Filtrar permisos con granted: true
      const grantedPermissions = allPermissions
        .filter((p) => p.granted)
        .map((p) => p.name);

      setPermissions(allPermissions);
      setPermissionName(grantedPermissions);
    } catch (error) {
      console.error("Error loading permissions:", error);
    }
    setEditOpen(true);
    setLoading(false);
  };

  const handleDelete = (id) => {
    setRows(rows.filter((row) => row.id !== id));
    console.log(`Delete role with ID: ${id}`);
  };

  const handleOpen = () => {
    setOpen(true);
  };

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

  const handleChange = (event) => {
    setNewRole(event.target.value);
  };

  const handleEditChange = (event) => {
    setEditRole({ ...editRole, role: event.target.value });
  };

  const handleManagePermissions = (event) => {
    //setNewRole(event.target.value);
  };

  const handleSave = async () => {
    try {
      setSaveLoading(true);
      const response = await axios.post(apiRoutes.rolesUrl, { name: newRole });
      const newRoleData = response.data.data;
      console.log(newRoleData);
      setRows([
        ...rows,
        { id: newRoleData.id, role: newRoleData.name, system: newRole.system },
      ]);
      setSaveLoading(false);
      handleClose();
    } catch (error) {
      console.error("Error creating new role:", error);
    }
  };

  const handleEditSave = async () => {
    try {
      setEditLoading(true);
      const response = await axios.post(apiRoutes.addPermissionToRoleUrl, {
        name: editRole.role,
        permissions: permissions.map((permission) => ({
          name: permission.name,
          granted: permissionName.includes(permission.name),
        })),
        role_id: editRole.id,
        org_id: 1,
      });
      const updatedRoleData = response.data.data;
      console.log(updatedRoleData);
      setRows(rows.map(row => row.id === updatedRoleData.id ? updatedRoleData : row));
      setEditLoading(false);
      handleEditClose();
    } catch (error) {
      console.error("Error updating role:", error);
    }
  };

  const handleChangeChips = (event) => {
    const {
      target: { value },
    } = event;
    setPermissionName(typeof value === "string" ? value.split(",") : value);
  };

  const columns = [
    { field: "id", headerName: "ID", width: 10 },
    {
      field: "role",
      headerName: "Role",
      type: "string",
      width: 800,
    },
    {
      field: "actions",
      headerName: "Actions",
      width: 150,
      headerAlign: "right",
      align: "right",
      renderCell: (params) => (
        <div
          style={{ display: "flex", justifyContent: "flex-end", width: "100%" }}
        >
          <IconButton
            color="primary"
            onClick={() => handleEdit(params.row)}
            disabled={params.row.system} // Disable button if system is true
          >
            <SettingsIcon />
          </IconButton>
          <IconButton
            color="secondary"
            onClick={() => handleDelete(params.row.id)}
            disabled={params.row.system} // Disable button if system is true
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
      <Button variant="contained">Roles</Button>
    </Link>,
    <Link href="/70k/team/permissions" key="Permissions">
      <Button >Permissions</Button>
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
          Add Role
        </Button>
      </Box>
      <div style={{ height: 400, width: "100%", position: "relative" }}>
        {loading ? (
          <></>
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
        <Box
          sx={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            width: 400,
            bgcolor: "background.paper",
            border: "2px solid #000",
            boxShadow: 24,
            p: 4,
          }}
        >
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
          <Box sx={{ mt: 2, display: "flex", justifyContent: "flex-end" }}>
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

      <Modal
        open={editOpen}
        onClose={handleEditClose}
        aria-labelledby="edit-modal-title"
        aria-describedby="edit-modal-description"
      >
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
          }}
        >
          <Typography variant="h6" component="h2" id="edit-modal-title">
            Edit Role
          </Typography>
          <TextField
            autoFocus
            margin="dense"
            label="Role"
            type="text"
            fullWidth
            value={editRole?.role || ""}
            onChange={handleEditChange}
          />
          <FormControl sx={{ m: 1 }}>
            <InputLabel id="demo-multiple-chip-label">Permissions</InputLabel>
            <Select
              labelId="demo-multiple-chip-label"
              id="demo-multiple-chip"
              multiple
              value={permissionName}
              onChange={handleChangeChips}
              input={
                <OutlinedInput id="select-multiple-chip" label="Permissions" />
              }
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
                <MenuItem
                  key={permission.name}
                  value={permission.name}
                  style={getStyles(permission.name, permissionName, theme)}
                >
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
      <LoadingOverlay open={loading}/>
    </Container>
  );
};

export default List;
