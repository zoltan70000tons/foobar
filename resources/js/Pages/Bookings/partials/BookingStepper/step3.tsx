import React from "react";
import { Box, Checkbox, FormControlLabel, Grid, Typography } from "@mui/material";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { useAppSelector } from "@/store/hooks";
import { selectBooking } from "@/store/slices/selectors";

export const BookingStepperStepThree = () => {
  const {
    toggleAddon,
  } = useBookingActions();

  const {
    addons,
  } = useAppSelector(selectBooking);
  return (
    <>
      <Box>
        <Typography variant="body1" sx={{ mt: 2 }}>
          Carbon Offset
        </Typography>
        <Grid item xs={12} md={3}>
          <FormControlLabel
            control={
              <Checkbox
                size="small"
                checked={addons.carbonOffset}
                onChange={() => toggleAddon("carbonOffset")}
              />
            }
            label="Carbon Offset"
          />
        </Grid>
      </Box>
      <Box>
        <Typography variant="body1" sx={{ mt: 2 }}>
          You Choose Your Cabin
        </Typography>
        <Grid item xs={12} md={3}>
          <FormControlLabel
            control={
              <Checkbox
                size="small"
                checked={addons.youChooseYourCabin}
                onChange={() => toggleAddon("youChooseYourCabin")}
              />
            }
            label="You Choose Your Cabin"
          />
        </Grid>
      </Box>
    </>
  );
}
