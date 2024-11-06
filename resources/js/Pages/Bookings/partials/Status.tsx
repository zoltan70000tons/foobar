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

const Status = ({ booking }) => {
  const [selectedTag, setSelectedTag] = useState<BookingTagEnum>(
    BookingTagEnum.NOT_ASSIGNE
  );

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    setSelectedTag(event.target.value as BookingTagEnum);
  };

  const handleUpdate = () => {
    console.log("Tag actualizado a:", selectedTag);
  };

  return (
    <>
    <Box >
      <Paper
        variant="outlined"
        sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}
      >
        <Grid container spacing={2}>
          <Grid item xs={12} md={6}>
            <Typography variant="h5">
              Booking ID: {booking.booking_code}
            </Typography>
          </Grid>
          <Grid item xs={12} md={6}>
            <Grid container spacing={2}>
              <Grid item xs={8}>
                <Select
                  value={selectedTag}
                  onChange={handleSelectChange}
                  fullWidth
                  displayEmpty
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
                  color="primary"
                  onClick={handleUpdate}
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
