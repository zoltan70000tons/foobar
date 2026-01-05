import { Autocomplete, Box, Chip, TextField } from "@mui/material";
import { useAvailableCabins } from "@/Hooks/booking/useAvailableCabins";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { useAppSelector } from "@/store/hooks";

export default function CabinSelector() {
  const { cabins, loading } = useAvailableCabins();
  const { setCabin } = useBookingActions();
  const { cabinNumber, cabinType, cabinCategory } = useAppSelector(
    (s) => s.booking
  );

  return (
    <Autocomplete
      fullWidth
      options={cabins}
      loading={loading}
      getOptionLabel={(option) => option.cabin_number}
      value={cabins.find((c) => c.cabin_number === cabinNumber) ?? null}
      onChange={(_, cabin) =>
        setCabin({
          cabinNumber: cabin?.cabin_number ?? null,
          isSingleRoom: cabin?.cabin_type_id !== 1,
        })
      }
      renderOption={(props, option) => (
        <Box component="li" {...props} key={option.cabin_number}>
          {option.cabin_number}
          {option.status === "RESERVED" && (
            <Chip sx={{ ml: 1 }} label="INTERNALLY AVAILABLE" color="warning" size="small" />
          )}
          {option.status === "AVAILABLE" && (
            <Chip sx={{ ml: 1 }} label="PUBLICLY AVAILABLE" color="success" size="small" />
          )}
          {option.status === "PARTIALLY_BOOKED" && (
            <Chip sx={{ ml: 1 }} label="PARTIALLY BOOKED" color="info" size="small" />
          )}
        </Box>
      )}
      renderInput={(params) => (
        <TextField
          {...params}
          label="Available Cabins"
          disabled={!cabinType || !cabinCategory || !cabins.length}
        />
      )}
      loadingText="Loading cabins..."
    />
  );
}
