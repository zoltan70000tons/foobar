import { formatCurrency } from "@/Helpers/stringUtils";
import { LocationEnum } from "@/enums/LocationEnum";
import CabinSelector from "@/Pages/Bookings/partials/CabinSelector";
import { bedConfigOptions } from "@/types/bed-config";
import FilterListIcon from "@mui/icons-material/FilterList";
import ClearIcon from "@mui/icons-material/Clear";
import React, { useEffect, useRef, useState } from "react";
import {
  Autocomplete,
  Box,
  FormControl,
  FormControlLabel,
  Grid,
  InputLabel, MenuItem, Select,
  Switch,
  TextField,
  ToggleButton
} from "@mui/material";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { fetchAvailableCabins } from "@/store/thunks/fetchAvailableCabins";

const paymentPlanOptions = [
  {
    id: "INSTALLMENTS",
    value: "INSTALLMENTS",
  },
  {
    id: "PAY_IN_FULL",
    value: "PAY_IN_FULL",
  },
];

export const BookingStepperStepZero = () => {
  const dispatch = useAppDispatch();
  const {
    setCabinType,
    setCabinCategory,
    setAvailableDecks,
    setAvailableCabins,
    setPaymentPlan,
    setBedConfig,
    setOnlyAccessible,
    setSelectedLocation,
    setSelectedDeck,
    setNumberOfInstallments,
    setAdvancedFilters,
  } = useBookingActions();

  const cabinType = useAppSelector((s) => s.booking.cabinType);
  const cabinCategory = useAppSelector((s) => s.booking.cabinCategory);
  const selectedDeck = useAppSelector((s) => s.booking.deck);
  const selectedLocation = useAppSelector((s) => s.booking.location);
  const onlyAccessible = useAppSelector((s) => s.booking.onlyAccessible);
  const advancedFilters = useAppSelector((s) => s.booking.advancedFilters);
  const cabinCategories = useAppSelector((s) => s.booking.cabinCategories);
  const cabinTypes = useAppSelector((s) => s.booking.cabinTypes);
  const loading = useAppSelector((s) => s.booking.loading);
  const paymentPlan = useAppSelector((s) => s.booking.paymentPlan);
  const bedConfig = useAppSelector((s) => s.booking.bedConfig);
  const availableDecks = useAppSelector((s) => s.booking.availableDecks);
  const numberOfInstallments = useAppSelector((s) => s.booking.installments);

  const [filteredCategories, setFilteredCategories] = useState([]);
  const cabinTypeRef = useRef(null);

  useEffect(() => {
    if (cabinTypeRef.current) {
      cabinTypeRef.current.focus();
    }
  }, []);

  useEffect(() => {
    if (!cabinType || !cabinCategory) {
      return;
    }
    dispatch(fetchAvailableCabins({
      type_id: cabinType?.id ?? null,
      category_id: cabinCategory?.id ?? null,
      deck: selectedDeck ?? null,
      location: selectedLocation ?? null,
      accessible: onlyAccessible,
    }));
  }, [cabinType, cabinCategory, selectedDeck, selectedLocation, onlyAccessible, advancedFilters]);

  useEffect(() => {
    if (!cabinType) return;
    const filteredCategories = cabinCategories.filter((category) =>
      category.cabins.some((cabin) => {
        const matchesType = cabin.cabin_type?.id === cabinType.id;
        const status = cabin.status;

        const isValidStatus =
          status === "AVAILABLE" || status === "RESERVED" || (cabinType.id !== 1 && status === "PARTIALLY_BOOKED");

        return matchesType && isValidStatus;
      }),
    );

    setFilteredCategories(filteredCategories);
  }, [cabinType, cabinCategories]);

  return (
    <Box>
      <Grid container spacing={2}>
        <Grid item xs={12} md={3}>
          <FormControl fullWidth sx={{ mt: 2 }}>
            <Autocomplete
              fullWidth
              options={cabinTypes}
              getOptionLabel={(option) => option.cabin_type}
              value={cabinType}
              onChange={(event, newValue) => setCabinType(newValue)}
              renderInput={(params) => <TextField {...params} label="Cabin Type" inputRef={cabinTypeRef} />}
              sx={{ mb: 2 }}
            />
          </FormControl>
        </Grid>

        <Grid item xs={12} md={6}>
          <FormControl fullWidth sx={{ mt: 2 }}>
            <Autocomplete
              fullWidth
              options={filteredCategories}
              getOptionLabel={(option) =>
                `${option.title} - ${option.capacity_description} - ${formatCurrency(option.price)}`
              }
              value={cabinCategory}
              onChange={(event, newValue) => {
                setCabinCategory(newValue);
                setAvailableCabins([]);
              }}
              renderInput={(params) => <TextField {...params} label="Cabin Category" disabled={!cabinType} />}
              sx={{ mb: 2 }}
              loading={loading}
              loadingText="Loading categories..."
              disabled={!cabinType}
            />
          </FormControl>
        </Grid>

        <Grid item xs={12} md={3} sx={{ mt: 2 }}>
          <FormControl fullWidth>
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
                  setSelectedLocation(null);
                  setAvailableDecks([]);
                  setOnlyAccessible(false);
                }
              }}
              disabled={loading}
              sx={{
                color: advancedFilters ? "error.main" : "inherit",
                borderColor: advancedFilters ? "error.main" : "default",
              }}
            >
              {advancedFilters ? <ClearIcon /> : <FilterListIcon />}
              {advancedFilters ? "Clear Filters" : "Advanced Filters"}
            </ToggleButton>
          </FormControl>
        </Grid>
        {advancedFilters && (
          <>
            <Grid item xs={12} md={3}>
              <FormControl fullWidth>
                <Autocomplete
                  fullWidth
                  options={availableDecks}
                  getOptionLabel={(option) => (option ? `Deck ${option}` : "")}
                  value={selectedDeck}
                  onChange={(event, newValue) => setSelectedDeck(newValue)}
                  renderInput={(params) => (
                    <TextField {...params} label="Cabin Deck" disabled={!cabinType || !cabinCategory} />
                  )}
                  sx={{ mb: 2 }}
                  disabled={!cabinType || !cabinCategory}
                />
              </FormControl>
            </Grid>
            <Grid item xs={12} md={3}>
              <FormControl fullWidth>
                <InputLabel id="location-label">Location</InputLabel>
                <Select
                  labelId="location-label"
                  value={selectedLocation}
                  onChange={(e) => setSelectedLocation(e.target.value)}
                  label="Location"
                  disabled={!cabinType || !cabinCategory}
                >
                  <MenuItem value="">
                    <em>None</em>
                  </MenuItem>
                  {Object.values(LocationEnum).map((location) => (
                    <MenuItem key={location} value={location}>
                      {location}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} md={3}>
              <FormControlLabel
                control={
                  <Switch
                    checked={onlyAccessible}
                    onChange={(e) => setOnlyAccessible(e.target.checked)}
                    disabled={!cabinType || !cabinCategory}
                  />
                }
                label="Only Accessible"
              />
            </Grid>
            <Grid item xs={12} md={3}></Grid>
          </>
        )}

        <Grid item xs={12} md={3}>
          <FormControl fullWidth>
            <CabinSelector />
          </FormControl>
        </Grid>

        {/*Payment Plan */}
        <Grid item xs={12} md={3}>
          <FormControl fullWidth>
            <Autocomplete
              fullWidth
              options={paymentPlanOptions}
              getOptionLabel={(option) => option.value}
              value={paymentPlan}
              onChange={(event, newValue) => setPaymentPlan(newValue)}
              renderInput={(params) => <TextField {...params} label="Payment Plan" />}
              sx={{ mb: 2 }}
            />
          </FormControl>
        </Grid>

        {/* Number of Installments */}
        {paymentPlan?.value === "INSTALLMENTS" && (
          <Grid item xs={12} md={3}>
            <FormControl fullWidth>
              <Autocomplete
                fullWidth
                options={[
                  { id: 2, value: 2 },
                  { id: 3, value: 3 },
                  { id: 4, value: 4 },
                  { id: 5, value: 5 },
                ]}
                getOptionLabel={(option) => `${option.value}`}
                isOptionEqualToValue={(option, value) => option.id === value.id}
                value={numberOfInstallments}
                onChange={(event, newValue) => setNumberOfInstallments(newValue)}
                renderInput={(params) => <TextField {...params} label="Number of Installments" />}
                sx={{ mb: 2 }}
              />
            </FormControl>
          </Grid>
        )}
        <Grid item xs={12} md={3}>
          <FormControl fullWidth>
            <Autocomplete
              fullWidth
              options={bedConfigOptions}
              getOptionLabel={(option) => `${option.value}`}
              value={bedConfig}
              onChange={(event, newValue) => setBedConfig(newValue)}
              renderInput={(params) => <TextField {...params} label="Bed Configuration" />}
              sx={{ mb: 2 }}
            />

          </FormControl>
        </Grid>
      </Grid>
    </Box>
  );
}
