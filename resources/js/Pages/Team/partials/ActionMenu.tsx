import React, { useState } from "react";
import {
  IconButton,
  Menu,
  MenuItem,
  Dialog,
  DialogActions,
  Button,
  DialogTitle,
  DialogContent,
} from "@mui/material";
import MoreVertIcon from "@mui/icons-material/MoreVert";
import ViewMember from "./ViewMember";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { User } from "@/interfaces/User";

interface ActionMenuProps {
  params:{
    row: User;
  };
  onUpdate: () => void;
}

const ActionMenu: React.FC<ActionMenuProps> = ({ params, onUpdate }) => {
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const [openViewModal, setOpenViewModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User>(params.row);
  const { hasPermission } = usePermissions();

  const handleMenuOpen = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
  };

  const handleView = () => {
    setSelectedUser(params.row);
    setOpenViewModal(true);
    handleMenuClose();
  };

  const handleCloseViewModal = () => {
    setOpenViewModal(false);
  };

  return (
    <>
      {hasPermission(Permissions.ViewUsers) && (
        <>
          <IconButton
            aria-label="more"
            aria-controls="long-menu"
            aria-haspopup="true"
            onClick={handleMenuOpen}
          >
            <MoreVertIcon />
          </IconButton>
          <Menu
            anchorEl={anchorEl}
            keepMounted
            open={Boolean(anchorEl)}
            onClose={handleMenuClose} 
          >
            <MenuItem onClick={handleView}>View</MenuItem>
          </Menu>
        </>
      )}

      <Dialog
        maxWidth="xl"
        open={openViewModal}
        onClose={handleCloseViewModal} 
        aria-labelledby="view-dialog-title"
        aria-describedby="view-dialog-description"
      >
        <DialogTitle>Edit User Details</DialogTitle>
        <DialogContent>
          <ViewMember selectedUser={selectedUser} onUpdate={onUpdate}/>
        </DialogContent>
        <DialogActions>
          <Button
            onClick={handleCloseViewModal}
            color="primary"
            variant="contained"
          >
            Close
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default ActionMenu;
