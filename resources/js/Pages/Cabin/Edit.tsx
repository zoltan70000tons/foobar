import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TagEnum } from "@/enums/TagEnum";
import {
  CabinStatus,
  CabinStatusReduced,
  CabinStatusColor,
} from "@/enums/CabinStatus";
import { CabinType } from "@/enums/CabinType";
import { PageProps } from "@/types";
import { Head, router } from "@inertiajs/react";
import { LocationEnum } from "@/enums/LocationEnum";
import {
  Box,
  Container,
  Grid,
  IconButton,
  TextField,
  Toolbar,
  Typography,
  Autocomplete,
  Chip,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Checkbox,
  FormControlLabel,
  Button,
  ListItemIcon,
  Tooltip,
  Alert,
} from "@mui/material";
import {
  CheckCircle,
  Block,
  HourglassEmpty,
  Close,
  ArrowBack,
  Rule,
} from "@mui/icons-material";

import NoAccessAlert from "@/Components/NoAccessAlert";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import SnackbarAlert from "@/Components/SnackbarAlert";

const Edit = ({
  auth,
  cabin,
  event,
  categories,
  errors,
}: PageProps & { tab: string; data: any }) => {
  const [selectedTags, setSelectedTags] = useState<string[]>(cabin.tags || []);
  const [cabinStatus, setCabinStatus] = useState<string>(cabin.status);
  const [cabinNumber, setCabinNumber] = useState<string>(cabin.cabin_number);
  const [cabinCategory, setCabinCategory] = useState<string>(
    cabin.cabin_category_id
  );
  const [cabinType, setCabinType] = useState<number | string>(
    cabin.cabin_type_id
  );
  const [deck, setDeck] = useState<string>(cabin.deck);
  const [location, setLocation] = useState<string>(cabin.location);
  const [connectWith, setConnectWith] = useState<string>(cabin.connects_with);
  const [ticketInventory, setTicketInventory] = useState<string>(
    cabin.inventory
  );
  const [totalBerths, setTotalBerths] = useState<string>(cabin.total_berths);
  const [lowerBedType1, setLowerBedType1] = useState<string>(
    cabin.lower_bed_type_1
  );
  const [lowerBedType2, setLowerBedType2] = useState<string>(
    cabin.lower_bed_type_2
  );
  const [upperBerths, setUpperBerths] = useState<string>(cabin.upper_berths);
  const [notes, setNotes] = useState<string>(cabin.notes);
  const [features, setFeatures] = useState({
    accessible: cabin.accessible,
    balcony: cabin.balcony,
    obstructedView: cabin.obstructed_view,
  });

  const { hasPermission } = usePermissions();

  const [snackbar, setSnackbar] = useState({
    open: false,
    severity: "success",
    message: "",
  });

  const handleBack = () => {
    window.history.back();
  };

  const cabinTypeOptions = [
    { value: 1, label: CabinType.PRIVATE_CABIN },
    { value: 2, label: CabinType.SINGLE_TICKET_MALE },
    { value: 3, label: CabinType.SINGLE_TICKET_FEMALE },
  ];
  
  const disableFields = cabinStatus === CabinStatus.BOOKED || cabinStatus === CabinStatus.PARTIALLY_BOOKED;
  const statusSource = disableFields ? CabinStatus : CabinStatusReduced;

  const canUpdateInventory = hasPermission(Permissions.EditCabinInventory);

  const statusIcons = {
    [CabinStatus.AVAILABLE]: (
      <CheckCircle
        fontSize="small"
        color={CabinStatusColor[CabinStatus.AVAILABLE]}
      />
    ),
    [CabinStatus.RESERVED]: (
      <HourglassEmpty
        fontSize="small"
        color={CabinStatusColor[CabinStatus.RESERVED]}
      />
    ),
    [CabinStatus.BOOKED]: (
      <Block fontSize="small" color={CabinStatusColor[CabinStatus.BOOKED]} />
    ),
    [CabinStatusReduced.CLOSED]: (
      <Close fontSize="small" color={CabinStatusColor[CabinStatus.CLOSED]} />
    ),
    [CabinStatus.PARTIALLY_BOOKED]: (
      <Rule
        fontSize="small"
        color={CabinStatusColor[CabinStatus.PARTIALLY_BOOKED]}
      />
    ),
  };

  const handleTagsChange = (event: any, newValue: string[]) => {
    setSelectedTags(newValue);
  };

  const handleFeatureChange = (event: any) => {
    setFeatures({ ...features, [event.target.name]: event.target.checked });
  };

  const handleCabinStatusChange = (event: any) => {
    setCabinStatus(event.target.value as CabinStatus);
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const formData = {
      cabin_status: cabinStatus,
      cabin_number: cabinNumber,
      cabin_category: cabinCategory,
      cabin_type: cabinType,
      deck: deck,
      location: location,
      connects_with: connectWith,
      total_berths: totalBerths,
      lower_bed_type_1: lowerBedType1,
      lower_bed_type_2: lowerBedType2,
      upper_berths: upperBerths,
      notes: notes,
      tags: selectedTags,
      features: {
        accessible: features.accessible,
        balcony: features.balcony,
        obstructed_view: features.obstructedView,
      },
      ticket_inventory: ticketInventory,
    };

    router.post(
      route("cabins.update", { id: event.id, cabin_id: cabin.id }),
      formData,
      {
        forceFormData: true,
        onSuccess: (response) => {
          setSnackbar({
            open: true,
            severity: "success",
            message: "Cabin edited successfully",
          });
          router.visit(route('cabins.edit', { id: event.id, cabin_id: cabin.id}), { only: ['cabins'] });
        },
        onError: (errors) => {
          setSnackbar({
            open: true,
            severity: "error",
            message: "Error editing cabin",
          });
        },
      }
    );
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };
  const hasAnyPermission =
    hasPermission(Permissions.ViewCabins) ||
    hasPermission(Permissions.EditCabins);

  const canEdit = !hasPermission(Permissions.EditCabins);

  return (
    <AuthenticatedLayout user={auth.user} header={"Cabins"}>
      <Head title="Cabins" />
      <Toolbar sx={{ mt: 8 }}>
        <IconButton edge="start" color="inherit" aria-label="menu">
          <img src={event.image} alt="Logo" style={{ height: 40 }} />
        </IconButton>
        <Typography variant="h6" style={{ flexGrow: 1 }}>
          {event.name}
        </Typography>
      </Toolbar>
      <Container maxWidth="lg" sx={{ mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Box>
              <div
                style={{
                  display: "flex",
                  justifyContent: "flex-start",
                  gap: "8px",
                }}
              >
                <Tooltip title="Back">
                  <IconButton color="primary" onClick={handleBack}>
                    <ArrowBack />
                  </IconButton>
                </Tooltip>
              </div>
            </Box>

            {hasAnyPermission ? (
              <>
                <Typography variant="h5" sx={{ mb: 3 }}>
                  Cabins / {cabin.cabin_number}
                </Typography>

                <form onSubmit={handleSubmit}>
                  <Grid container spacing={2}>
                    {(cabinStatus === CabinStatus.BOOKED ||
                      cabinStatus === CabinStatus.PARTIALLY_BOOKED) && (
                      <Grid item xs={12}>
                        <Alert severity="warning">
                          The current status of this cabin only allows editing
                          certain fields.
                        </Alert>
                      </Grid>
                    )}
                    {/* Status Select */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ mb: 2 }}>
                        <FormControl
                          fullWidth
                          variant="outlined"
                          disabled={canEdit || disableFields}
                        >
                          <InputLabel>Status</InputLabel>
                          <Select
                            value={cabinStatus}
                            onChange={handleCabinStatusChange}
                            label="Status"
                            renderValue={(selected) => (
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 1,
                                }}
                              >
                                <ListItemIcon
                                  sx={{
                                    minWidth: "auto",
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  {
                                    statusIcons[
                                      selected as keyof typeof statusSource
                                    ]
                                  }{" "}
                                </ListItemIcon>
                                {
                                  statusSource[
                                    selected as keyof typeof statusSource
                                  ]
                                }{" "}
                              </Box>
                            )}
                          >
                            {Object.keys(statusSource).map((status) => (
                              <MenuItem
                                key={status}
                                value={status}
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 1,
                                }}
                              >
                                <ListItemIcon
                                  sx={{
                                    minWidth: "auto",
                                    marginRight: 1,
                                    display: "flex",
                                    alignItems: "center",
                                  }}
                                >
                                  {
                                    statusIcons[
                                      status as keyof typeof CabinStatus
                                    ]
                                  }{" "}
                                </ListItemIcon>
                                {
                                  CabinStatus[
                                    status as keyof typeof CabinStatus
                                  ]
                                }{" "}
                              </MenuItem>
                            ))}
                          </Select>
                        </FormControl>
                      </Box>
                    </Grid>

                    {/* Tags Select with Chips */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ mb: 2 }}>
                        <Autocomplete
                          disabled={canEdit}
                          multiple
                          options={Object.values(TagEnum)}
                          value={selectedTags}
                          onChange={handleTagsChange}
                          renderTags={(value: string[], getTagProps) =>
                            value.map((option: string, index: number) => (
                              <Chip
                                variant="outlined"
                                label={option}
                                {...getTagProps({ index })}
                              />
                            ))
                          }
                          renderInput={(params) => (
                            <TextField
                              {...params}
                              variant="outlined"
                              label="Tags"
                              placeholder="Add tags"
                            />
                          )}
                          fullWidth
                        />
                      </Box>
                    </Grid>

                    {/* Cabin Category */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ mb: 2 }}>
                        <FormControl
                          fullWidth
                          variant="outlined"
                          disabled={canEdit || disableFields}
                        >
                          <InputLabel>Cabin Category</InputLabel>
                          <Select
                            value={cabinCategory}
                            onChange={(e) => setCabinCategory(e.target.value)}
                            label="Cabin Category"
                          >
                            {categories.map((category) => (
                              <MenuItem key={category.id} value={category.id}>
                                {category.title}
                              </MenuItem>
                            ))}
                          </Select>
                        </FormControl>
                      </Box>
                    </Grid>

                    {/* Cabin Number */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="cabin_number"
                          label="Cabin Number"
                          variant="outlined"
                          fullWidth
                          value={cabinNumber}
                          onChange={(e) => setCabinNumber(e.target.value)}
                          error={Boolean(errors.cabin_number)}
                          helperText={errors.cabin_number}
                        />
                      </Box>
                    </Grid>

                    {/* Cabin Type */}
                    <Grid item xs={12} md={4}>
                      <Box sx={{ mb: 2 }}>
                        <FormControl
                          fullWidth
                          variant="outlined"
                          disabled={canEdit || disableFields}
                        >
                          <InputLabel>Cabin Type</InputLabel>
                          <Select
                            value={cabinType}
                            onChange={(e) => setCabinType(e.target.value)}
                            label="Cabin Type"
                            disabled={canEdit || disableFields}
                          >
                            {cabinTypeOptions.map((option) => (
                              <MenuItem key={option.value} value={option.value}>
                                {option.label}
                              </MenuItem>
                            ))}
                          </Select>
                        </FormControl>
                      </Box>
                    </Grid>

                    {/* Ticket Inventory */}
                    <Grid item xs={12} md={4}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          name="ticket_inventory"
                          label="Ticket Inventory"
                          variant="outlined"
                          fullWidth
                          disabled
                          value={ticketInventory}
                          onChange={(e) => setTicketInventory(e.target.value)}
                        />
                      </Box>
                    </Grid>

                    {/* Deck */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="deck"
                          label="Deck"
                          variant="outlined"
                          fullWidth
                          value={deck}
                          onChange={(e) => setDeck(e.target.value)}
                          error={Boolean(errors.deck)}
                          helperText={errors.deck}
                        />
                      </Box>
                    </Grid>

                    {/* Location */}
                    <Grid item xs={12} md={4}>
                      <Box sx={{ mb: 2 }}>
                        <FormControl
                          fullWidth
                          variant="outlined"
                          disabled={canEdit || disableFields}
                        >
                          <InputLabel>Location</InputLabel>
                          <Select
                            value={location}
                            onChange={(e) => setLocation(e.target.value)}
                            label="Location"
                          >
                            <MenuItem value="FW">Forward</MenuItem>
                            <MenuItem value="MS">Midship</MenuItem>
                            <MenuItem value="AF">Aft</MenuItem>
                          </Select>
                        </FormControl>
                      </Box>
                    </Grid>

                    {/* Connect With */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="connects_with"
                          label="Connects With"
                          variant="outlined"
                          fullWidth
                          value={connectWith}
                          onChange={(e) => setConnectWith(e.target.value)}
                          error={Boolean(errors.connects_with)}
                          helperText={errors.connects_with}
                        />
                      </Box>
                    </Grid>

                    {/* Features */}
                    <Grid item xs={12}>
                      <Box sx={{ mb: 2 }}>
                        <Typography variant="h6">Features</Typography>
                        <FormControlLabel
                          control={
                            <Checkbox
                              checked={features.accessible}
                              onChange={handleFeatureChange}
                              name="accessible"
                              disabled={canEdit || disableFields}
                            />
                          }
                          label="Accessible"
                        />
                        <FormControlLabel
                          control={
                            <Checkbox
                              checked={features.balcony}
                              onChange={handleFeatureChange}
                              name="balcony"
                              disabled={canEdit || disableFields}
                            />
                          }
                          label="Balcony"
                        />
                        <FormControlLabel
                          control={
                            <Checkbox
                              checked={features.obstructedView}
                              onChange={handleFeatureChange}
                              name="obstructedView"
                              disabled={canEdit || disableFields}
                            />
                          }
                          label="Obstructed View"
                        />
                      </Box>
                    </Grid>

                    {/* Total Berths */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="total_berths"
                          label="Total Berths"
                          variant="outlined"
                          fullWidth
                          value={totalBerths}
                          onChange={(e) => setTotalBerths(e.target.value)}
                          error={Boolean(errors.total_berths)}
                          helperText={errors.total_berths}
                        />
                      </Box>
                    </Grid>

                    {/* Lower Bed Type 1 */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="lower_bed_type_1"
                          label="Lower Bed Type 1"
                          variant="outlined"
                          fullWidth
                          value={lowerBedType1}
                          onChange={(e) => setLowerBedType1(e.target.value)}
                          error={Boolean(errors.lower_bed_type_1)}
                          helperText={errors.lower_bed_type_1}
                        />
                      </Box>
                    </Grid>

                    {/* Lower Bed Type 2 */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="lower_bed_type_2"
                          label="Lower Bed Type 2"
                          variant="outlined"
                          fullWidth
                          value={lowerBedType2}
                          onChange={(e) => setLowerBedType2(e.target.value)}
                          error={Boolean(errors.lower_bed_type_2)}
                          helperText={errors.lower_bed_type_2}
                        />
                      </Box>
                    </Grid>

                    {/* Upper Berths */}
                    <Grid item xs={12} md={2}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit || disableFields}
                          name="upper_berths"
                          label="Upper Berths"
                          variant="outlined"
                          fullWidth
                          value={upperBerths}
                          onChange={(e) => setUpperBerths(e.target.value)}
                          error={Boolean(errors.upper_berths)}
                          helperText={errors.upper_berths}
                        />
                      </Box>
                    </Grid>

                    {/* Notes */}
                    <Grid item xs={12}>
                      <Box sx={{ mb: 2 }}>
                        <TextField
                          disabled={canEdit}
                          name="notes"
                          label="Notes"
                          variant="outlined"
                          fullWidth
                          multiline
                          rows={4}
                          value={notes}
                          onChange={(e) => setNotes(e.target.value)}
                          error={Boolean(errors.notes)}
                          helperText={errors.notes}
                        />
                      </Box>
                    </Grid>
                  </Grid>

                  {hasPermission(Permissions.EditCabins) && (
                    <Grid container spacing={2}>
                      <Grid item xs={12}>
                        <Box sx={{ mb: 2 }}>
                          <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                          >
                            Update
                          </Button>
                        </Box>
                      </Grid>
                    </Grid>
                  )}
                </form>
              </>
            ) : (
              <NoAccessAlert message="You do not have permission to access this section." /> // Mostrar NotAllowed si no tiene permisos
            )}
          </Grid>
          <SnackbarAlert
            open={snackbar.open}
            severity={snackbar.severity}
            message={snackbar.message}
            onClose={handleCloseSnackbar}
          />
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Edit;
