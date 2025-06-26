import React, { useState, useEffect } from "react";
import {
  FormControl,
  InputLabel,
  Select,
  OutlinedInput,
  MenuItem,
  Box,
  Chip,
  CircularProgress,
  SelectChangeEvent,
  Button,
  Grid,
  Theme,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import LoadingButton from "@mui/lab/LoadingButton";
import { useTheme } from "@mui/material/styles";
import SaveIcon from '@mui/icons-material/Save';
import apiRoutes from "@/Helpers/ApiRoutes";
import axios from "axios";
import { Permissions } from "@/enums/PermissionEnum";
import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";


interface Permission {
  name: string;
  granted: boolean;
}

interface AssignRolesProps {
  userId: number;
  orgId: number;
}

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

function getStyles(name: string, permissionName: readonly string[], theme: Theme) {
  return {
    fontWeight:
      permissionName.indexOf(name) === -1
        ? theme.typography.fontWeightRegular
        : theme.typography.fontWeightMedium,
  };
}

const AssignRoles: React.FC<AssignRolesProps> = ({ userId, orgId }) => {
  const { hasPermission,  error } = usePermissions();
  const canEdit = hasPermission(Permissions.EditUsers);
  
  const theme = useTheme();
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [permissionName, setPermissionName] = useState<string[]>([]);
  const [loading, setLoading] = useState(true); 
  const [saveLoading, setSaveLoading] = useState(false);
  const {showSnackbar} = useSnackbar();

  useEffect(() => {
    const fetchPermissions = async () => {
      try {
        const response = await axios.get(apiRoutes.orgRolesUrl, { params: { org_id: orgId, user_id: userId } });
        const permissionsData = response.data.data;
        const grantedPermissions = permissionsData.filter(permission => permission.granted).map(permission => permission.name);
        setPermissions(permissionsData);
        setPermissionName(grantedPermissions); 
      } catch (error) {
        console.error("Error fetching permissions", error);
      } finally {
        setLoading(false); 
      }
    };

    fetchPermissions();
  }, [userId, orgId]);

  const handleChangeChips = (event: SelectChangeEvent<typeof permissionName>) => {
    const {
      target: { value },
    } = event;
    setPermissionName(
      // On autofill we get a stringified value.
      typeof value === 'string' ? value.split(',') : value,
    );
  };

  const handleSubmit = async () => {
    try {
      setSaveLoading(true);
      router.put(apiRoutes.updateRole, { 
        roles: permissionName, 
        user_id: userId, 
        org_id: 1 
    }, {
        preserveState:false, 
        onSuccess: () => {
          console.log('Role updated successfully!');
          showSnackbar('Role updated successfully', 'success');
        },
        onError: (errors) => {
          console.error('Failed to update role:', errors)
          showSnackbar('Failed to update role', 'error');
        },
    });
      setSaveLoading(false);
    } catch (error) {
      console.error("Error saving roles", error);
      setSaveLoading(false);
    }
  };

  return (
    <Grid container xs={12} sm={12} md={12} lg={12}>
    <Box sx={{ width: '100%', display:'block'}} component="form" onSubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
      <FormControl fullWidth>
        <InputLabel id="multiple-chip-label">Role</InputLabel>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
            <CircularProgress />
          </Box>
        ) : (
          <Select
            labelId="multiple-chip-label"
            id="multiple-chip"
            value={permissionName}
            onChange={handleChangeChips}
            disabled ={!hasPermission(Permissions.EditUsers)} 
            input={<OutlinedInput id="select-multiple-chip" label="Roles" />}
            renderValue={(selected) => (
              <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
                {selected.map((value) => (
                  <Chip key={value} label={value} />
                ))}
              </Box>
            )}
            MenuProps={MenuProps}
            fullWidth
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
        )}
      </FormControl>
      {hasPermission(Permissions.EditUsers) && ( <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2 }}>
      <LoadingButton
              loading={saveLoading}
              loadingPosition="start"
              startIcon={<SaveIcon />}
              variant="contained"
              color="primary"
              onClick={handleSubmit}
            >
              Save
            </LoadingButton>
      </Box>)}
      <p style={{visibility:"hidden"}}>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Veniam dicta fugit adipisci quisquam sed soluta quo, dolor dolores quasi eveniet animi beatae ducimus, itaque est doloribus pariatur cupiditate atque? Sit?</p> 
    </Box>
    </Grid> 
  );
};

export default AssignRoles;
