import React, { useState } from "react";
import {
  Grid,
  Typography,
  Select,
  MenuItem,
  Button,
  Paper,
  Box,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
  IconButton,
  Chip,
  Avatar,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import { router } from "@inertiajs/react";
import { usePermissions } from "@/Providers/PermissionContext";
import Tags from "./Tags";
import UserSelectorModal from "@/Components/UserSelectorModal";
import { Permissions } from "@/enums/PermissionEnum";
import { StatusEnum } from "@/enums/StatusEnum";

const Status = ({ event, booking, editMode, users }) => {
  const [selectedStatus, setSelectedStatus] = useState<StatusEnum[]>(
    booking.status ? booking.status : []
  );
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [updatedBookingCode, setUpdatedBookingCode] = useState(booking.booking_code);
  const [openUserModal, setUserOpenModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);

  const { hasPermission } = usePermissions();
  const canEdit = hasPermission(Permissions.EditBookings);
  const agent = booking?.agent;
  const label = agent?.username ? agent.username : <em>Not Assigned</em>;
  const avatar = agent?.username ? <Avatar>{agent.username[0]}</Avatar> : <Avatar>N</Avatar>;

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedStatus(event.target.value as StatusEnum[]);
  };

  const handleUpdate = () => {
    router.post(route("bookings.updateCode", { id: event.id }), {
      booking_code: updatedBookingCode,
      booking_id: booking.id,
    });
    setIsDialogOpen(false);
  };

  const handleUpdateStatus = () => {
    router.post(route("bookings.updateStatus", { id: event.id }), {
      status: selectedStatus,
      booking_id: booking.id,
    });
  };

  const handleDialogOpen = () => setIsDialogOpen(true);
  const handleDialogClose = () => setIsDialogOpen(false);
  const handleCloseUserModal = () => setUserOpenModal(false);

  const handleChipClick = (agent_id, booking_id) => {
    setUserOpenModal(true);
  };

  const handleAgentSelection = (userId: string | null) => {
    if (!userId) return;
    router.put(
      route("bookings.assignAgent", { id: event.id }),
      { agent_id: userId, booking_code: booking.booking_code },
      {
        onSuccess: () => setUserOpenModal(false),
        onError: () => setUserOpenModal(false),
        preserveScroll: true,
      }
    );
  };

  return (
    <>
      <Box>
        <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} md={2}>
              <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                Booking ID:
              </Typography>
            </Grid>
            <Grid item xs={12} md={4} display="flex" flexDirection="column" gap={1}>
              <Box display="flex" alignItems="center" gap={1}>
                <Typography variant="h6">{booking.booking_code}</Typography>
                  <IconButton onClick={handleDialogOpen} size="small" disabled={!canEdit || !editMode}>
                    <EditIcon />
                  </IconButton>
              </Box>
              <Chip
                key={booking.id}
                label={label}
                avatar={avatar}
                onClick={() => handleChipClick(agent?.id, booking.booking_code)}
                size="small"
                color={agent?.username ? "primary" : "default"}
                sx={{
                  fontSize: "0.7rem",
                  fontWeight: "400",
                  alignSelf: "flex-start", // Alinea el Chip al inicio
                  width: "auto", // Asegura que el Chip no se estire
                }}
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <Grid container spacing={2} alignItems="center">
                <Grid item xs={8}>
                  <Select
                    value={selectedStatus}
                    onChange={handleSelectChange}
                    disabled={!canEdit || !editMode}
                    fullWidth
                    displayEmpty
                  >
                    {Object.values(StatusEnum).map((status) => (
                      <MenuItem key={status} value={status}>
                        {status}
                      </MenuItem>
                    ))}
                  </Select>
                </Grid>
                <Grid item xs={4}>
                  <Button
                    variant="contained"
                    color="warning"
                    onClick={handleUpdateStatus}
                    disabled={!canEdit || !editMode}
                    sx={{
                      height: "100%",
                      color: "#fff",
                      textTransform: "none",
                    }}
                  >
                    Update
                  </Button>
                </Grid>
              </Grid>
              <Grid container mt={2}>
                <Tags editable={!editMode} event={event} booking={booking} />
              </Grid>
            </Grid>
          </Grid>
        </Paper>
      </Box>
      <Dialog open={isDialogOpen} onClose={handleDialogClose} maxWidth="md" fullWidth>
        <DialogTitle>Edit Booking Code</DialogTitle>
        <DialogContent>
          <TextField
            fullWidth
            value={updatedBookingCode}
            onChange={(e) => setUpdatedBookingCode(e.target.value)}
            label="Booking Code"
            variant="outlined"
            autoFocus
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDialogClose} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleUpdate} color="primary">
            Save
          </Button>
        </DialogActions>
      </Dialog>
      <UserSelectorModal
        open={openUserModal}
        onClose={handleCloseUserModal}
        onSave={handleAgentSelection}
        initialUserId={selectedUserId}
        users={users}
      />
    </>
  );
};

export default Status;
