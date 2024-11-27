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
  Tabs,
  Tab
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import WarningIcon from "@mui/icons-material/Warning";
import { router } from "@inertiajs/react";
import { usePermissions } from "@/Providers/PermissionContext";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import UserSelectorModal from "@/Components/UserSelectorModal";
import { Permissions } from "@/enums/PermissionEnum";
import { StatusEnum } from "@/enums/StatusEnum";
import Tags from "./Tags";




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
  const { showSnackbar } = useSnackbar();
  const [isCancelDialogOpen, setIsCancelDialogOpen] = useState(false);
  const handleCancelDialogOpen = () => setIsCancelDialogOpen(true);
  const handleCancelDialogClose = () => setIsCancelDialogOpen(false);
  const handleDialogOpen = () => setIsDialogOpen(true);
  const handleDialogClose = () => setIsDialogOpen(false);
  const handleCloseUserModal = () => setUserOpenModal(false);
  const [activeTab, setActiveTab] = useState(0);

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedStatus(event.target.value as StatusEnum[]);
  };

  const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
    setActiveTab(newValue);
  };

  const handleUpdate = () => {
    router.post(
      route("bookings.updateCode", { id: event.id }),
      {
        booking_code: updatedBookingCode,
        booking_id: booking.id,
      },
      {
        onSuccess: () => {
          showSnackbar("Booking code updated successfully!", "success");
          setIsDialogOpen(false);
        },
        onError: (errors) => {
          showSnackbar("Error updating booking code!", "error");
        },
      }
    );
  };

  const handleUpdateStatus = () => {
    router.post(route("bookings.updateStatus", { id: event.id }), {
      status: selectedStatus,
      booking_id: booking.id,
    }, {
      onSuccess: () => {
        showSnackbar("Booking status updated successfully!", "success");
        setIsDialogOpen(false);
      },
      onError: (errors) => {
        showSnackbar("Error updating booking status!", "error");
      },
    });
  };


  const handleCancelBooking = () => {
    router.post(
      route("bookings.cancel", { id: event.id }),
      { booking_id: booking.id },
      {
        onSuccess: () => {
          showSnackbar("Booking canceled successfully!", "success");
          setIsCancelDialogOpen(false);
        },
        onError: (errors) => {
          showSnackbar("Error canceling booking!", "error");
          setIsCancelDialogOpen(false);
        },
      }
    );
  };



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
      <Box sx={{minHeight:'200px', marginBottom:'2rem'}}>
      <Tabs
          value={activeTab}
          onChange={handleTabChange}
          indicatorColor="primary"
          textColor="primary"
          //centered
        >
          <Tab label="Status" />
          <Tab label="Danger Zone" />
        </Tabs>

        {activeTab === 0 && (
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
                  alignSelf: "flex-start", 
                  width: "auto", 
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
                <Tags editable={true} event={event} booking={booking} />
              </Grid>
            </Grid>
          </Grid>
        </Paper>)}
        {activeTab === 1 && (
          <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mt: 2, minHeight: '150px' }}>
            <Typography variant="h6" sx={{ mb: 2 }}>
              Cancel this booking:
            </Typography>
            <Button
              variant="contained"
              color="warning"
              startIcon={<WarningIcon />}
              onClick={handleCancelDialogOpen}
              disabled={booking.is_cancelled || !editMode}
            >
              Cancel Booking
            </Button>
          </Paper>
        )}
      </Box>
      <Dialog open={isDialogOpen} onClose={handleDialogClose} maxWidth="md" fullWidth>
        <DialogTitle>Edit Booking Code</DialogTitle>
        <DialogContent >
          <TextField
            sx={{marginTop:'1rem !important'}}
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
      {/* Confirm Cancel Booking Dialog */}
      <Dialog open={isCancelDialogOpen} onClose={handleCancelDialogClose}>
        <DialogTitle>Cancel Booking</DialogTitle>
        <DialogContent>
          Are you sure you want to cancel this booking? This action cannot be undone.
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancelDialogClose} color="secondary">
            No, Keep Booking
          </Button>
          <Button onClick={handleCancelBooking} color="warning">
            Yes, Cancel Booking
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default Status;
