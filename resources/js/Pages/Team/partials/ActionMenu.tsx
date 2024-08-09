import React, { useState } from 'react';
import { IconButton, Menu, MenuItem, Dialog, DialogActions, Button, DialogTitle, DialogContent } from "@mui/material";
import MoreVertIcon from '@mui/icons-material/MoreVert';
import ViewMember from './ViewMember';

interface ActionMenuProps {
  params: any;
}

const ActionMenu: React.FC<ActionMenuProps> = ({ params }) => {
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const [openViewModal, setOpenViewModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

  const handleClick = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleView = () => {
    setSelectedUser(params.row);
    setOpenViewModal(true);
    handleClose();
  };

  const handleEdit = () => {
    console.log('Edit', params.row);
    //Todo edit
    handleClose();
  };

  const handleCloseViewModal = () => {
    setOpenViewModal(false);
  };

  return (
    <>
      <IconButton
        aria-label="more"
        aria-controls="long-menu"
        aria-haspopup="true"
        onClick={handleClick}
      >
        <MoreVertIcon />
      </IconButton>
      <Menu
        anchorEl={anchorEl}
        keepMounted
        open={Boolean(anchorEl)}
        onClose={handleClose}
      >
        <MenuItem onClick={handleView}>View</MenuItem>
      </Menu>

      <Dialog
        maxWidth="lg"
        open={openViewModal}
        onClose={handleCloseViewModal}
        aria-labelledby="view-dialog-title"
        aria-describedby="view-dialog-description"
      >
        <DialogTitle>Edit User Details</DialogTitle>
        <DialogContent>
        <ViewMember selectedUser={selectedUser} />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseViewModal} color="primary" variant="contained">Close</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default ActionMenu;