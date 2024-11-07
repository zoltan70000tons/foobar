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

const Status = ({ event, booking }) => {
  const [selectedTags, setSelectedTags] = useState<BookingTagEnum[]>(booking.tags ? booking.tags : []);
  const {hasPermission} = usePermissions();

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedTags(event.target.value as BookingTagEnum[]);
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
                    multiple
                    value={selectedTags}
                    onChange={handleSelectChange}
                    disabled={!canEdit}
                    fullWidth
                    displayEmpty
                    renderValue={(selected) => (selected as BookingTagEnum[]).join(', ')}
                  >
                    {Object.values(BookingTagEnum).map((tag) => (
                      <MenuItem key={tag} value={tag}>
                        {tag}
                      </MenuItem>
                    ))}
                  </Select>
                </Grid>
                <Grid item xs={4}>
                  <Button
                    variant="contained"
                    color="warning"
                    onClick={handleUpdate}
                    sx={{ height: '-webkit-fill-available', color: "#fff" }}
                    disabled={!canEdit}
                  >
                    Update
                  </Button>
                </Grid>
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
