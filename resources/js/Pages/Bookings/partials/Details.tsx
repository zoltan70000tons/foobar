import React, { useState, useEffect } from "react";
import {
  Grid,
  Typography,
  Paper,
  Box,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Button,
  TextField,
  Autocomplete,
  MenuItem,
  ToggleButton,
  Switch,
  FormControlLabel,
  Alert,
  AlertTitle,
  Chip,
  CircularProgress,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import UpgradeIcon from '@mui/icons-material/Upgrade';
import FilterListIcon from "@mui/icons-material/FilterList";
import axios from "axios";
import { router } from "@inertiajs/react";
import { LocationEnum } from "@/enums/LocationEnum";
import { DeckEnum } from "@/enums/DeckEnum";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import PinIcon from '@mui/icons-material/Pin';
import VisibilityIcon from '@mui/icons-material/Visibility';
import ClearIcon from '@mui/icons-material/Clear'
import SwapHorizIcon from '@mui/icons-material/SwapHoriz';

import {
  DateRange as DateRangeIcon,
  LocationOn as LocationOnIcon,
  ConfirmationNumber as ConfirmationNumberIcon,
  DirectionsBoat as DirectionsBoatIcon,
  Group as GroupIcon,
  Payment as PaymentIcon,
  Bed as BedIcon,
  Fingerprint as FingerprintIcon,
  EventNote as EventNoteIcon,
  Notes as NotesIcon,
} from "@mui/icons-material";

import { formatDate } from "@/Helpers/stringUtils";
import { isArray } from "lodash";

const Detail = ({ event, booking, editMode, cabinTypes, cabinCategories, maxInstallmentsAllowed }) => {
  const [open, setOpen] = useState(false);
  const [upgradeCabinModalOpen, setUpgradeCabinModalOpen] = useState(false);
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
  const [confirmUpgradeOpen, setConfirmUpgradeOpen] = useState(false);
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);
  const [availableDecks, setAvailableDecks] = useState([]);

  const [cabinsToUpgradeTo, setCabinsToUpgradeTo] = useState([]);
  const [upgradeCabin, setUpgradeCabin] = useState(null);
  const [changeCabin, setChangeCabin] = useState(null);

  const [switchPlanOpen, setSwitchPlanOpen] = useState(false);
  const [switchingPlan, setSwitchingPlan] = useState(false);
  const [selectedInstallments, setSelectedInstallments] = useState<number | null>(null);


  useEffect(() => {
    if (booking?.cabin) {
      setCabinType(cabinTypes.find((type) => type.id === booking.cabin.cabin_type_id) || null);
      setCabinCategory(
        cabinCategories.find((category) => category.id === booking.cabin.cabin_category_id) || null
      );
      setCabinNumber(booking.cabin.cabin_number);
    }
  }, [booking, cabinTypes, cabinCategories]);

  useEffect(() => {
    if (open) fetchAvailableCabins();
  }, [cabinCategory, selectedDeck, onlyBalcony, selectedLocation, onlyAccessible, advancedFilters]);

  useEffect(() => {
    console.log('Available cabins updated:', availableCabins);
  }, [availableCabins]);

  const statusPriority = {
    PARTIALLY_BOOKED: 1,
    AVAILABLE: 2,
  };

  const isInstallments = booking?.payment_plan === 'INSTALLMENTS';
  const currentPlanLabel = isInstallments ? 'Installments' : 'Paid in Full';
  const nextPlanLabel = isInstallments ? 'Paid in Full' : 'Installments';
  const maxAllowed = isInstallments ? 0 : maxInstallmentsAllowed ?? 0;
  const bookingCreatedAt = booking?.created_at ? new Date(booking.created_at) : null;
  const bookingAgeDays = bookingCreatedAt ? Math.floor((Date.now() - bookingCreatedAt.getTime()) / (1000 * 60 * 60 * 24)) : 0;
  const bookingOlderThanWeek = bookingAgeDays > 7;
  console.log(bookingCreatedAt, bookingAgeDays, bookingOlderThanWeek);

  const fetchAvailableCabins = async () => {
    try {
      setLoading(true);
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
      if (response?.data?.error) {
        showSnackbar(response.data.error, 'error');
      }
      const decks = Array.isArray(response?.data?.cabins)
        ? [...new Set(response.data.cabins.map(cabin => cabin.deck))]
        : [];
      setAvailableCabins(response?.data?.cabins);
      setAvailableDecks(decks);

    } catch (error) {
      if (error.response?.data?.error) {
        showSnackbar(error.response.data.error, 'error');
      }
      //showSnackbar("Error fetching available cabins!", "error");
      console.error("Error fetching available cabins:", error);
      setAvailableCabins([]);
      setAvailableDecks([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchCabinsToUpgradeTo = async () => {
    try {
      setLoading(true);
      setCabinsToUpgradeTo([]);
      const response = await axios.get(route("cabins.upgrade-list"), {
        params: {
          type_id: cabinType?.id,
          category_id: cabinCategory?.id,
          cabin_number: cabinNumber,
          deck: selectedDeck,
          balcony: onlyBalcony,
          location: selectedLocation,
          accessible: onlyAccessible,
        },
      });

      if (response?.data?.error) {
        showSnackbar(response.data.error, 'error');
      }
      /*const decks = Array.isArray(response?.data?.cabins)
        ? [...new Set(response.data.cabins.map(cabin => cabin.deck))]
        : [];
      setAvailableCabins(response?.data?.cabins);
      setAvailableDecks(decks);*/

      setCabinsToUpgradeTo(response?.data?.cabins);
    } catch (error) {
      if (error.response?.data?.error) {
        showSnackbar(error.response.data.error, 'error');
      }
      //showSnackbar("Error fetching available cabins!", "error");
      console.error("Error fetching available cabins:", error);
      //setAvailableCabins([]);
      //setAvailableDecks([]);
    } finally {
      setLoading(false);
    }
  };

  const handleEditClick = () => {
    setOpen(true);
    if (cabinType && cabinCategory) {
      fetchAvailableCabins();
    }
  };

  const handleUpgradeCabin = () => {
    setUpgradeCabinModalOpen(true);

    if (cabinType && cabinCategory) {
      fetchCabinsToUpgradeTo();
    }
  }

  const handleClose = () => {
    setOpen(false);
    // Reset states
    setAvailableCabins([]);
    setSelectedDeck(null);
    setOnlyBalcony(false);
    setSelectedLocation("");
    setOnlyAccessible(false);
  };

  const handleSave = () => {
    setConfirmOpen(false);
    setLoading(true);
    router.post(
      route("bookings.updateCabin", { id: event.id }),
      {
        cabin_type_id: changeCabin?.cabin_type_id,
        cabin_category_id: changeCabin?.cabin_category_id,
        cabin_number: changeCabin?.cabin_number,
        deck: selectedDeck,
        balcony: onlyBalcony,
        location: selectedLocation,
        accessible: onlyAccessible,
        booking_id: booking.id,
        capacity: changeCabin?.capacity,
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
        onFinish: () => {
          setLoading(false);
        }
      }
    );
  };

  const handleUpgrade = () => {
    setConfirmUpgradeOpen(false);
    setLoading(true);
    router.post(
      route("bookings.upgradeCabin", { id: event.id }),
      {
        cabin_type_id: upgradeCabin?.cabin_type.id,
        cabin_category_id: upgradeCabin?.category.id,
        cabin_number: upgradeCabin?.cabin_number,
        deck: selectedDeck,
        balcony: onlyBalcony,
        location: selectedLocation,
        accessible: onlyAccessible,
        booking_id: booking.id,
        capacity: upgradeCabin?.category.capacity,
      },
      {
        onSuccess: () => {
          setUpgradeCabinModalOpen(false);
          showSnackbar("Cabin upgraded successfully!", "success");
        },
        onError: (errors) => {

          showSnackbar("Error upgrading cabin!", "error");
          console.error(errors);
        },
        onFinish: () => {
          setLoading(false);
        }
      }
    );
  };

  const handleSwitchClick = () => {
    setSwitchPlanOpen(true);
    // reset selection when opening
    setSelectedInstallments(null);
  }

  const handleConfirmSwitchPlan = () => {
    setSwitchingPlan(true);
    const payload = {
      payment_plan: isInstallments ? 'PAY_IN_FULL' : 'INSTALLMENTS',
      booking_id: booking.id,
      number_of_installments: isInstallments ? null : selectedInstallments,
    };

    router.post(
      route("bookings.switchPaymentPlan", { booking_id: booking.id, event_id: event.id }),
      payload,
      {
        onSuccess: () => {
          setSwitchPlanOpen(false);
          showSnackbar(`Payment plan changed to ${nextPlanLabel}`, "success");
        },
        onError: (errors) => {
          console.error(errors);
          showSnackbar("Error switching payment plan", "error");
        },
        onFinish: () => {
          setSwitchingPlan(false);
        }
      }
    );
  };

  return (
    <>
      <Box>
        <Typography variant="h5" mb={2}>
          Booking Code - {booking?.booking_code}
        </Typography>
        <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c" }}>
          <Grid container spacing={3}>
            <Grid item xs={12} md={4}>
              <Box
                sx={{
                  position: "relative",
                  width: "100%",
                  paddingTop: "100%",
                  borderRadius: 2,
                  overflow: "hidden",
                }}
              >
                <img
                  src={event?.image}
                  alt="Event"
                  style={{
                    position: "absolute",
                    top: 0,
                    left: 0,
                    width: "100%",
                    height: "100%",
                    objectFit: "cover",
                  }}
                />
              </Box>
            </Grid>

            <Grid item xs={12} md={8}>
              <Grid container spacing={2}>
                {[
                  {
                    icon: <DateRangeIcon fontSize="small" />,
                    label: "Event Dates",
                    value:
                      event?.start_date && event?.end_date
                        ? `${formatDate(event.start_date)} - ${formatDate(event.end_date)}`
                        : "-",
                  },
                  {
                    icon: <LocationOnIcon fontSize="small" />,
                    label: "Destination",
                    value: event?.address || "-",
                  },
                  {
                    icon: <FingerprintIcon fontSize="small" />,
                    label: "Booking Request ID",
                    value: booking?.booking_request_id || "-",
                  },
                  {
                    icon: <ConfirmationNumberIcon fontSize="small" />,
                    label: "Booking Type",
                    value: booking?.cabin?.cabin_type?.cabin_type || "-",
                  },
                  {
                    icon: <DirectionsBoatIcon fontSize="small" />,
                    label: "Cabin Category",
                    value: booking?.cabin?.category?.title || "-",
                  },
                  {
                    icon: <GroupIcon fontSize="small" />,
                    label: "Cabin Capacity",
                    value: booking?.cabin?.category?.capacity || "-",
                  },
                  {
                    icon: <PaymentIcon fontSize="small" />,
                    label: "Payment Plan",
                    value: booking?.payment_plan === "INSTALLMENTS" ? "Installments" : "Pay In Full At Booking",
                  },
                  {
                    icon: <BedIcon fontSize="small" />,
                    label: "Bed Configuration",
                    value: booking?.bed_config || "-",
                  },
                  {
                    icon: <PinIcon fontSize="small" />,
                    label: "Cabin Number",
                    value: booking?.cabin?.cabin_number || "-",
                  },
                  {
                    icon: <VisibilityIcon fontSize="small" />,
                    label: "Status",
                    value: booking?.status || "-",
                  },
                  {
                    icon: <EventNoteIcon fontSize="small" />,
                    label: "Notes",
                    value: booking?.cabin?.notes || "-",
                  },
                  {
                    icon: <NotesIcon fontSize="small" />,
                    label: "Internal Notes",
                    value: booking?.cabin?.internal_notes || "-",
                  },
                  
                ].map((field, index) => (
                  <Grid item xs={12} sm={6} key={index}>
                    <Box display="flex" alignItems="center" mb={0.5}>
                      {field.icon}
                      <Typography variant="body2" sx={{ ml: 1, fontWeight: "bold" }}>
                        {field.label}:
                      </Typography>
                    </Box>
                    <Typography variant="body2" color="text.secondary">
                      {field.value}
                    </Typography>
                  </Grid>
                ))}
              </Grid>

              {editMode && (
                <Box sx={{display: "flex", gap: "16px", justifyContent: "flex-end"}}>
                  <Box mt={3} textAlign="right">
                    <Button variant="contained" color="primary" startIcon={<EditIcon />} onClick={handleEditClick}>
                      Swap Cabins
                    </Button>
                  </Box>

                  <Box mt={3} textAlign="right">
                    <Button variant="contained" color="primary" startIcon={<UpgradeIcon />} onClick={handleUpgradeCabin}>
                      Upgrade Cabin
                    </Button>
                  </Box>
                  <Box mt={3} textAlign="right">
                    <Button variant="contained" color="primary" startIcon={<SwapHorizIcon />} onClick={handleSwitchClick}>
                      Switch Payment Plan
                    </Button>
                  </Box>
                </Box>
              )}
            </Grid>
          </Grid>
        </Paper>
      </Box>

      {/* Dialog for Editing Cabin Details */}
      <Dialog open={open} onClose={handleClose} maxWidth="md" fullWidth>
        <DialogTitle>Edit Cabin Details</DialogTitle>
        <DialogContent sx={{ paddingTop: '1rem !important' }}>
          {loading && (
            <Box
              sx={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                // bgcolor: "rgba(255,255,255,0.7)",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
              }}
            >
              <CircularProgress />
            </Box>
          )}

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
              onChange={() => {
                const next = !advancedFilters;
                setAdvancedFilters(next);
                if (next) {
                } else {
                  setSelectedDeck(null);
                  setAvailableCabins([]);
                  setOnlyAccessible(false);
                  setOnlyBalcony(false);
                }
              }}
              disabled={loading}
              sx={{
                color: advancedFilters ? 'error.main' : 'inherit',
                borderColor: advancedFilters ? 'error.main' : 'default',
              }}
            >
              {advancedFilters ? <ClearIcon /> : <FilterListIcon />}
              {advancedFilters ? 'Clear Filters' : 'Advanced Filters'}
            </ToggleButton>
          </Box>
          {advancedFilters && (
            <>
              <Autocomplete
                fullWidth
                options={availableDecks}
                getOptionLabel={(option) => option ? `Deck ${option}` : ''}
                value={selectedDeck}
                onChange={(event, newValue) => setSelectedDeck(newValue)}
                renderInput={(params) => <TextField {...params} label="Cabin Deck" />}
                sx={{ mb: 2 }}
                disabled={loading}
              />
              <FormControlLabel
                control={
                  <Switch
                    checked={onlyBalcony}
                    onChange={(e) => setOnlyBalcony(e.target.checked)}
                    disabled={loading}
                  />
                }
                label="Only Balcony"
              />
              <Autocomplete
                fullWidth
                options={Object.values(LocationEnum)}
                getOptionLabel={(option) => option || ''}
                value={selectedLocation}
                onChange={(event, newValue) => setSelectedLocation(newValue)}
                renderInput={(params) => (
                  <TextField {...params} label="Location" />
                )}
                sx={{ mt: 2 }}
                disabled={loading}
              />
              <FormControlLabel
                control={
                  <Switch
                    checked={onlyAccessible}
                    onChange={(e) => setOnlyAccessible(e.target.checked)}
                    disabled={loading}
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
              options={[...availableCabins].sort(
                (a, b) => statusPriority[a.status] - statusPriority[b.status]
              )}
              getOptionLabel={(option) => option.cabin_number + ' ' + option.status}
              value={availableCabins?.find((cabin) => cabin.cabin_number === cabinNumber) || null}
              onChange={(event, newValue) => {
                setChangeCabin(newValue || null);
                setCabinNumber(newValue?.cabin_number);
              }}
              renderOption={(props, option) => (
                <li {...props} key={option.cabin_number}>
                  {option.cabin_number} 
                  <Chip
                    label={option.status === "RESERVED" ? "INTERNALLY AVAILABLE" : option.status === "AVAILABLE" ? "PUBLICALLY AVAILABLE" : option.status}
                    size="small"
                    sx={{ ml: 1 ,color:'white'}}
                    color={
                      option.status === 'AVAILABLE'
                        ? 'success'
                        : option.status === 'PARTIALLY_BOOKED'
                          ? 'warning'
                          : 'default'
                    }
                  />
                </li>
              )}
              renderInput={(params) => <TextField {...params} label="Available Cabins" />}
              disabled={loading}
            />


          </Box>

        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} color="secondary" variant="outlined">
            Cancel
          </Button>
          <Button onClick={() => setConfirmOpen(true)} color="primary" variant="outlined" disabled={!changeCabin || loading}>
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

      <Dialog open={upgradeCabinModalOpen} onClose={()=>setUpgradeCabinModalOpen(false)} maxWidth="md" fullWidth>
        <DialogTitle>Upgrade Cabin</DialogTitle>
        <DialogContent sx={{ paddingTop: '1rem !important' }}>
          {loading && (
            <Box
              sx={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                // bgcolor: "rgba(255,255,255,0.7)",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
              }}
            >
              <CircularProgress />
            </Box>
          )}

          <Typography>Current cabin type: {cabinType?.cabin_type}</Typography>
          <Typography>Current cabin category: {cabinCategory?.title}</Typography>
          <Box>
            <Typography variant="body1" sx={{ mb: 1 }}>
              Select a new cabin for this booking. The current cabin is {`${booking.cabin.cabin_number}`}.
            </Typography>

            {isArray(cabinsToUpgradeTo) && cabinsToUpgradeTo.length !== 0 && (
              <Autocomplete
                fullWidth
                options={[...cabinsToUpgradeTo].sort(
                  (a, b) => statusPriority[a.status] - statusPriority[b.status]
                )}
                getOptionLabel={(option) => `${option.cabin_number} ${option.status}`}
                value={cabinsToUpgradeTo?.find((cabin) => cabin.cabin_number === cabinNumber) || null}
                onChange={(event, newValue) => {
                  setUpgradeCabin(newValue || null);
                }}
                renderOption={(props, option) => (
                  <li {...props} key={option.cabin_number}>
                    {option.cabin_number}
                    <Chip
                      label={option.status === "RESERVED" ? "INTERNALLY AVAILABLE" : option.status === "AVAILABLE" ? "PUBLICALLY AVAILABLE" : option.status}
                      size="small"
                      sx={{ ml: 1 ,color:'white'}}
                      color={
                        option.status === 'AVAILABLE'
                          ? 'success'
                          : option.status === 'PARTIALLY_BOOKED'
                            ? 'warning'
                            : 'default'
                      }
                    />
                    - Price: {option.category.price}
                    - Name: {option.category.category_name}
                    - Capacity: {option.category.spec.capacity}
                  </li>
                )}
                renderInput={(params) => <TextField {...params} label="Available Cabins" />}
                disabled={loading || cabinsToUpgradeTo.length === 0}
              />
            )}
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={()=>setUpgradeCabinModalOpen(false)} color="secondary" variant="outlined">
            Cancel
          </Button>
          <Button onClick={() => setConfirmUpgradeOpen(true)} color="primary" variant="outlined" disabled={!cabinNumber || loading}>
            Save
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={confirmUpgradeOpen} onClose={() => setConfirmUpgradeOpen(false)}>
        <DialogTitle>Confirm Upgrade</DialogTitle>
        <DialogContent>
          <Alert severity="warning" sx={{ mb: 2 }}>
            <AlertTitle>Warning</AlertTitle>
            Are you sure you want to save? This action will permanently release the current cabin,
            update the booking code, and associate the selected cabin with this booking.
            The change will be recorded in the system.
          </Alert>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setConfirmUpgradeOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleUpgrade} color="primary">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={switchPlanOpen} onClose={() => !switchingPlan && setSwitchPlanOpen(false)}>
        <DialogTitle>Switch Payment Plan</DialogTitle>
        <DialogContent>
          {isInstallments ? (
            <>
             {bookingOlderThanWeek && (
              <>
              <Alert severity="info" sx={{ mt: 2 }}>
                Please note this booking is a week old
              </Alert>
              <br />
              </>

            )}
            <Alert severity="warning" sx={{ mb: 2 }}>
              <AlertTitle>Switch to Paid in Full</AlertTitle>
              This will <b>convert the booking to a Paid in Full plan</b>.
              The following changes will be applied:
              <ul>
                <li>Add the <b>5% Pay in Full adjustment</b> to this booking.</li>
                <li>Mark all remaining installments as <b>fully due immediately</b>.</li>
                <li>Update outstanding balances per passenger.</li>
              </ul>
              <b>Note:</b> Existing payment entries will not be modified.
            </Alert>
            </>
          ) : (
            <Alert severity="warning" sx={{ mb: 2 }}>
              <AlertTitle>Switch to Installments</AlertTitle>
              This will <b>switch the booking to an Installment Plan</b>.
              The following changes will be applied:
              <ul>
                <li>Remove the <b>5% Pay in Full adjustment</b> from this booking.</li>
                <li>Generate <b>2–5 monthly installments</b>.</li>
                <li>Recalculate <b>outstanding amounts per passenger</b>.</li>
              </ul>
              <b>Note:</b> Existing payment entries will not be altered.
            </Alert>
          )}

          <Typography variant="body2" color="text.secondary">
            Booking: <b>{booking?.booking_code}</b>
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Current Plan: <b>{currentPlanLabel}</b> → New Plan: <b>{nextPlanLabel}</b>
          </Typography>
          {!isInstallments && (
            <Box mt={2}>
              <TextField
                select
                fullWidth
                label="Number of installments"
                value={selectedInstallments ?? ''}
                onChange={(e) => setSelectedInstallments(Number(e.target.value) || null)}
                helperText={
                  maxAllowed < 2
                    ? 'Not enough time to create at least 2 installments before event cutoff'
                    : `Select number of installments (2-${maxAllowed})`
                }
                disabled={maxAllowed < 2}
              >
                {Array.from({ length: Math.max(0, maxAllowed - 1) }, (_, i) => i + 2).map((n) => (
                  <MenuItem key={n} value={n}>
                    {n}
                  </MenuItem>
                ))}
              </TextField>
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button
            onClick={() => setSwitchPlanOpen(false)}
            color="secondary"
      variant="outlined"
      disabled={switchingPlan}
    >
      Cancel
    </Button>
    <Button
      onClick={handleConfirmSwitchPlan}
      color="primary"
      variant="contained"
      startIcon={<SwapHorizIcon />}
      disabled={
        switchingPlan || (!isInstallments && (maxAllowed < 2 || !selectedInstallments || selectedInstallments < 2))
      }
    >
      {switchingPlan ? "Processing..." : "Confirm Switch"}
    </Button>
  </DialogActions>
</Dialog>


    </>
  );
};

export default Detail;
