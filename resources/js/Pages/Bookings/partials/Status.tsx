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
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import "dayjs/locale/en";
import { BookingTagEnum } from "@/enums/TagEnum";
import { router } from "@inertiajs/react";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import Tags from "./Tags";
import { StatusEnum } from "@/enums/StatusEnum";

const Status = ({ event, booking, editMode }) => {
  const [selectedStatus, setSelectedStatus] = useState<StatusEnum[]>(
    booking.status ? booking.status : []
  );
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [updatedBookingCode, setUpdatedBookingCode] = useState(booking.booking_code);

  const { hasPermission } = usePermissions();

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedStatus(event.target.value as StatusEnum[]);
  };

  const handleUpdate = () => {

    router.post(route("bookings.updateCode", { id: event.id }), { booking_code: updatedBookingCode, booking_id : booking.id });
    setIsDialogOpen(false);
  };

  const handleDialogOpen = () => {
    setIsDialogOpen(true);
  };

  const handleDialogClose = () => {
    setIsDialogOpen(false);
  };

  const canEdit = hasPermission(Permissions.EditBookings);

  return (
    <>
      <Box>
        <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
          <Grid container spacing={2}>
            <Grid item xs={12} md={2}>
              <Typography variant="h5">Booking ID:</Typography>
            </Grid>
            <Grid item xs={12} md={4} display="flex" alignItems="center">
              <Typography variant="h6">{booking.booking_code}</Typography>
              {canEdit && (
                <IconButton onClick={handleDialogOpen} sx={{ ml: 1 }}>
                  <EditIcon />
                </IconButton>
              )}
            </Grid>
            <Grid item xs={12} md={6}>
              <Grid container spacing={2}>
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
                    onClick={handleUpdate}
                    disabled={!canEdit || !editMode}
                    sx={{ height: "-webkit-fill-available", color: "#fff" }}
                  >
                    Update
                  </Button>
                </Grid>
              </Grid>
              <Grid container mt={2}>
                <Tags editable={!editMode} />
              </Grid>
            </Grid>
            <Grid item xs={12}></Grid>
          </Grid>
        </Paper>
      </Box>

      <Dialog open={isDialogOpen} onClose={handleDialogClose}>
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
    </>
  );
};

export default Status;
