import React, { useState } from "react";
import {
  Grid,
  Typography,
  Select,
  MenuItem,
  Button,
  Paper,
  Box,
} from "@mui/material";
import "dayjs/locale/en";
import { BookingTagEnum } from "@/enums/TagEnum";
import { router } from "@inertiajs/react";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import Tags from "./Tags";
import { StatusEnum } from "@/enums/StatusEnum";

const Status = ({ event, booking, editMode }) => {
  const [selectedStatus, setSelectedStatus] = useState<StatusEnum[]>(booking.status ? booking.status : []);
  const {hasPermission} = usePermissions();

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedStatus(event.target.value as StatusEnum[]);
  };

  const handleUpdate = () => {
    const data = {
      selectedTags,
    };
    router.post(route('bookings.update', { id: event.id, booking_code: booking.booking_code }), data);
  };

  const canEdit =  hasPermission(Permissions.EditBookings);

  return (
    <>
      <Box>
        <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
          <Grid container spacing={2}>
            <Grid item xs={12} md={2}>
              <Typography variant="h5">Booking ID:</Typography>
            </Grid>
            <Grid item xs={12} md={4}>
              <Typography variant="h6">{booking.booking_code}</Typography>
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
                    //renderValue={(selected) => (selected as StatusEnumEnum[]).join(', ')}
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
                    sx={{ height: '-webkit-fill-available', color: "#fff" }}
                  >
                    Update
                  </Button>
                </Grid>
              </Grid>
              <Grid container mt={2}>
              <Tags editable={!editMode}/>
              </Grid>
            </Grid>
            <Grid item xs={12}></Grid>
          </Grid>
        </Paper>
      </Box>
    </>
  );
};

export default Status;
