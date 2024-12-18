import React, { useState, useEffect } from "react";
import {
  Grid,
  Typography,
  Table,
  TableBody,
  TableRow,
  TableCell,
  Paper,
  Box,
  IconButton,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Button,
  TextField,
  Autocomplete,
  ToggleButton,
  Switch,
  FormControlLabel,
  MenuItem,
  Select,
  InputLabel,
  FormControl,
  Alert,
  AlertTitle,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import FilterListIcon from "@mui/icons-material/FilterList";
import axios from "axios";
import { router } from "@inertiajs/react";
import { LocationEnum } from "@/enums/LocationEnum";
import { DeckEnum } from "@/enums/DeckEnum";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

const Detail = ({ event, booking, editMode, cabinTypes, cabinCategories }) => {
  const [open, setOpen] = useState(false);
  const [cabinType, setCabinType] = useState(null);
  const [cabinCategory, setCabinCategory] = useState(null);
  const [availableCabins, setAvailableCabins] = useState([]);
  const [cabinNumber, setCabinNumber] = useState(booking?.cabin?.cabin_number || null);
  const [advancedFilters, setAdvancedFilters] = useState(false);
  const [selectedDeck, setSelectedDeck] = useState(null);
  const [onlyBalcony, setOnlyBalcony] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState("");
  const [onlyAccessible, setOnlyAccessible] = useState(false);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const { showSnackbar } = useSnackbar();


  useEffect(() => {
    if (booking?.cabin) {
      setCabinType(cabinTypes.find((type) => type.id === booking.cabin.cabin_type_id) || null);
      setCabinCategory(
        cabinCategories.find((category) => category.id === booking.cabin.cabin_category_id) || null
      );
      setCabinNumber(booking.cabin.cabin_number || null);
    }
  }, [booking, cabinTypes, cabinCategories]);

  useEffect(() => {
    if (open) fetchAvailableCabins();
  }, [cabinCategory, selectedDeck, onlyBalcony, selectedLocation, onlyAccessible]);

  const fetchAvailableCabins = async () => {
    try {
      const response = await axios.get(route("cabins.available"), {
        params: {
          type_id: cabinType?.id,
          category_id: cabinCategory?.id,
          deck: selectedDeck,
          balcony: onlyBalcony,
          location: selectedLocation,
          accessible: onlyAccessible,
        },
      });
      setAvailableCabins(response.data.cabins || []);
      setCabinNumber(null);
    } catch (error) {
      showSnackbar("Error fetching available cabins!", "error");
      console.error("Error fetching available cabins:", error);
      setAvailableCabins([]);
    }
  };

  const handleEditClick = () => {
    setOpen(true);
    if (cabinType && cabinCategory) {
      fetchAvailableCabins();
    }
  };

  const handleClose = () => {
    setOpen(false);
    // Reset states
    setAvailableCabins([]);
    setSelectedDeck(null);
    setOnlyBalcony(false);
    setSelectedLocation("");
    setOnlyAccessible(false);
    setCabinNumber(null);
  };

  const handleSave = () => {
    setConfirmOpen(false);
    router.post(
      route("bookings.updateCabin", { id: event.id }),
      {
        cabin_type_id: cabinType?.id,
        cabin_category_id: cabinCategory?.id,
        cabin_number: cabinNumber,
        deck: selectedDeck,
        balcony: onlyBalcony,
        location: selectedLocation,
        accessible: onlyAccessible,
        booking_id: booking.id,
      },
      {
        onSuccess: () => {
          setOpen(false);
          showSnackbar("Cabin updated successfully!", "success");
        },
        onError: (errors) => {
          showSnackbar("Error updating cabin!", "error");
          console.error(errors);
        },
      }
    );
  };

  return (
    <>
      <Box>
        <Typography variant="h5" mb={2}>
          Cabin Details
        </Typography>
        <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <Table size="small">
                <TableBody>
                  <TableRow>
                    <TableCell>Cabin Type</TableCell>
                    <TableCell>{booking?.cabin?.cabin_type?.cabin_type}</TableCell>
                    <TableCell align="right">
                      <IconButton color="secondary" disabled={!editMode} onClick={handleEditClick}>
                        <EditIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Category</TableCell>
                    <TableCell>{booking?.cabin?.cabin_category?.title}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Number</TableCell>
                    <TableCell>{booking?.cabin?.cabin_number}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Deck</TableCell>
                    <TableCell>{booking?.cabin?.deck}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Location</TableCell>
                    <TableCell>{booking?.cabin?.location}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Capacity</TableCell>
                    <TableCell>{booking?.cabin?.cabin_category?.capacity}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </Grid>
          </Grid>
        </Paper>
      </Box>

      {/* Dialog for Editing Cabin Details */}
      <Dialog open={open} onClose={handleClose} maxWidth="md" fullWidth>
        <DialogTitle>Edit Cabin Details</DialogTitle>
        <DialogContent sx={{paddingTop: '1rem !important'}}>
          <Autocomplete
            fullWidth
            options={cabinTypes}
            getOptionLabel={(option) => option.cabin_type}
            value={cabinType}
            disabled
            onChange={(event, newValue) => setCabinType(newValue)}
            renderInput={(params) => <TextField {...params} label="Cabin Type" />}
            sx={{ mb: 2 }}
          />
          <Autocomplete
            fullWidth
            options={cabinCategories}
            getOptionLabel={(option) => option.title}
            value={cabinCategory}
            disabled
            onChange={(event, newValue) => setCabinCategory(newValue)}
            renderInput={(params) => <TextField {...params} label="Cabin Category" />}
            sx={{ mb: 2 }}
          />
          <Box sx={{ mb: 2 }}>
            <ToggleButton
              value="advancedFilters"
              selected={advancedFilters}
              onChange={() => setAdvancedFilters(!advancedFilters)}
            >
              <FilterListIcon />
              Advanced Filters
            </ToggleButton>
          </Box>
          {advancedFilters && (
            <>
              <Autocomplete
                fullWidth
                options={Object.values(DeckEnum).filter((value) => typeof value === "number")}
                getOptionLabel={(option) => `Deck ${option}`}
                value={selectedDeck}
                onChange={(event, newValue) => setSelectedDeck(newValue)}
                renderInput={(params) => <TextField {...params} label="Cabin Deck" />}
                sx={{ mb: 2 }}
              />
              <FormControlLabel
                control={
                  <Switch
                    checked={onlyBalcony}
                    onChange={(e) => setOnlyBalcony(e.target.checked)}
                  />
                }
                label="Only Balcony"
              />
              <FormControl fullWidth sx={{ mt: 2 }}>
                <InputLabel id="location-label">Location</InputLabel>
                <Select
                  labelId="location-label"
                  value={selectedLocation}
                  onChange={(e) => setSelectedLocation(e.target.value)}
                >
                  {Object.values(LocationEnum).map((location) => (
                    <MenuItem key={location} value={location}>
                      {location}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
              <FormControlLabel
                control={
                  <Switch
                    checked={onlyAccessible}
                    onChange={(e) => setOnlyAccessible(e.target.checked)}
                  />
                }
                label="Only Accessible"
              />
            </>
          )}
          <Box>
            <Typography variant="body1" sx={{ mb: 1 }}>
              Select a new cabin for this booking. The current cabin is {`${booking.cabin.cabin_number}`}.
            </Typography>
            <Autocomplete
              fullWidth
              options={availableCabins}
              getOptionLabel={(option) => option.cabin_number}
              value={availableCabins.find((cabin) => cabin.cabin_number === cabinNumber) || null}
              onChange={(event, newValue) => setCabinNumber(newValue?.cabin_number || null)}
              renderInput={(params) => <TextField {...params} label="Available Cabins" />}
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} color="secondary">
            Cancel
          </Button>
          <Button onClick={() => setConfirmOpen(true)} color="primary" disabled={!cabinNumber}>
            Save
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={confirmOpen} onClose={() => setConfirmOpen(false)}>
        <DialogTitle>Confirm Save</DialogTitle>
        <DialogContent>
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            Are you sure you want to save? This action will permanently release the current cabin,
            update the booking code, and associate the selected cabin with this booking.
            The change will be recorded in the system.
          </Alert>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setConfirmOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleSave} color="primary">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default Detail;
