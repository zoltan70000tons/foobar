import React, { useEffect, useState } from "react";
import {
  Box,
  Button,
  Grid,
  Paper,
  Typography,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  IconButton,
  Autocomplete,
  TextField,
  CircularProgress,
  FormControl,
  InputLabel,
  Select,
  Checkbox,
  FormControlLabel,
  Tooltip,
  FormHelperText,
  MenuItem,
  Alert,
} from "@mui/material";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import EditPassengerModal from "./EditPassengerModal";
import { router } from "@inertiajs/react";
import { PersonAdd } from "@mui/icons-material";
import PersonIcon from "@mui/icons-material/Person";
import Person2Icon from "@mui/icons-material/Person2";
import SwapHoriz from "@mui/icons-material/SwapHoriz";
import { Passenger } from "./Payment";
import debounce from "lodash/debounce";
import Country from "@/Components/Country";
import PhoneNumber from "@/Components/PhoneNumber";
import CreditCardIcon from "@mui/icons-material/CreditCard";
import DirectionsBoatIcon from "@mui/icons-material/DirectionsBoat";
import ReportProblemIcon from "@mui/icons-material/ReportProblem";
import HomeIcon from "@mui/icons-material/Home";
import ContactMailIcon from "@mui/icons-material/ContactMail";
import CloseIcon from "@mui/icons-material/Close";

type PassengersProps = {
  booking: Booking;
  editMode: boolean;
  setLoading: (loading: boolean) => void;
};

type Booking = {
  id: number;
  event_id: number;
};

const CabinType = Object.freeze({
  SINGLE_MALE: 2,
  SINGLE_FEMALE: 3,
  PRIVATE_CABIN: 1,
});

const Passengers: React.FC<PassengersProps> = ({ booking, editMode, setLoading }) => {
  const [editPassengerOpen, setEditPassengerOpen] = useState(false);
  const [openConfirmCancelInvitation, setOpenConfirmCancelInvitation] = useState(false);
  const [openConfirmSeatEmpty, setOpenConfirmSeatEmpty] = useState(false);
  const [selectedUser, setSelectedUser] = useState(null);
  const [openConfirm, setOpenConfirm] = useState(false);
  const [passengers, setPassengers] = useState(
    [...booking.passengers].sort((a, b) => a.passenger_order - b.passenger_order),
  );
  const [searchLoading, setSearchLoading] = useState(false);
  const [editingPassenger, setEditingPassenger] = useState(null);
  const [editedPassengerData, setEditedPassengerData] = useState({});
  const [errors, setErrors] = useState({});
  const { showSnackbar } = useSnackbar();
  const [savinLoading, setSavingLoading] = useState(false);
  const [releaseLoading, setReleaseLoading] = useState(false);
  const [passengerToCancelInvitationFor, setPassengerToCancelInvitationFor] = useState(null);
  const isSingleRoom = [CabinType.SINGLE_MALE, CabinType.SINGLE_FEMALE].includes(booking?.cabin?.cabin_type_id);
  const [searchQuery, setSearchQuery] = useState("");
  const [suggestions, setSuggestions] = useState([]);

  const [openSwithPassengerModal, setOpenSwitchPassengerModal] = useState(false);
  const [switchStep, setSwitchStep] = useState<"search" | "edit" | "review" | "confirm">("search");
  const [switchPassengerData, setSwitchPassengerData] = useState({});
  const [selectedCountry, setSelectedCountry] = useState(null);
  const isDisabled = false;
  const isLeadPassenger = switchPassengerData?.lead_passenger;
  const validation = errors?.response?.data?.errors;
  const [selectedOptions, setSelectedOptions] = useState([]);
  const [leadPassengerCurrent, setLeadPassengerCurrent] = useState<Passenger | null>(
    passengers.find((p) => p.lead_passenger) || null,
  );

  useEffect(() => {
    const fetchSuggestions = debounce(async (query: string) => {
      if (query.length < 3) {
        setSuggestions([]);
        return;
      }
      try {
        setSearchLoading(true);
        const response = await axios.get(route("switch.lead.search"), {
          params: { query: query, bookingId: booking.id, eventId: booking.event_id },
        });
        setSuggestions(response.data);
        console.log("Suggestions fetched:", response.data);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        setSuggestions([]);
      } finally {
        setSearchLoading(false);
      }
    }, 300);

    fetchSuggestions(searchQuery);

    return () => {
      fetchSuggestions.cancel();
    };
  }, [searchQuery]);

  const handleSwitchLeadPassenger = (passenger: Passenger) => {
    setOpenSwitchPassengerModal(true);
  };

  // Open edit modal and set passenger data
  const handleEditPassenger = (passenger) => {
    setEditingPassenger(passenger);
    setEditedPassengerData(passenger);
    setEditPassengerOpen(true);
  };

  const handleCancelPassengerInvitation = (passenger) => {
    setPassengerToCancelInvitationFor(passenger);
    setOpenConfirmCancelInvitation(true);
  };

  const handleCloseCancelInvitationModal = () => {
    setOpenConfirmCancelInvitation(false);
  };

  const handleConfirmCancelInvitation = async () => {
    setOpenConfirmCancelInvitation(false);
    setReleaseLoading(true);

    try {
      if (!passengerToCancelInvitationFor) {
        throw new Error("Passenger not found");
      }
      const response = await cancelInvitation(passengerToCancelInvitationFor.id, booking.id, booking.event_id);

      setPassengers(response.data.passengers);

      showSnackbar("Invitation successfully cancelled!", "success");

      setErrors({});
      router.reload({ only: ["booking"] });
    } catch (error) {
      console.error("Error cancelling invitation:", error.response?.data || error);
      showSnackbar("Failed to cancel invitation", "error");
    } finally {
      setPassengerToCancelInvitationFor(null);
      setReleaseLoading(false);
    }
  };

  // Update passenger details
  const handleSavePassenger = async () => {
    try {
      setSavingLoading(true);
      const response = await axios.post(route("seat.update", { id: booking.event_id, booking_id: booking.id }), {
        ...editedPassengerData,
      });
      setPassengers((prev) => prev.map((p) => (p.id === editingPassenger.id ? { ...p, ...response.data } : p)));
      setEditPassengerOpen(false);
      setEditingPassenger(null);
      setErrors({});
      router.reload({ only: ["booking"] });
      showSnackbar("Passenger data updated succesfully!", "success");
    } catch (error) {
      setErrors(error);
      console.error(error);
      showSnackbar("Error updating passenger data!" + (error.response?.data?.error || error), "error");
    } finally {
      setSavingLoading(false);
    }
  };

  const searchCustomers = async (query) => {
    if (!query || query.trim() === "") return [];

    try {
      const res = await axios.get(route("customers.search"), {
        params: { search: query },
      });
      console.log("Search results:", res.data);
      const customers = res.data;
      return Array.isArray(customers) ? customers : [];
    } catch (err) {
      console.error("Search error:", err);
      return [];
    }
  };

  const onDelete = () => {
    setOpenConfirm(true);
  };

  const handleConfirm = async () => {
    setOpenConfirm(false);
    setReleaseLoading(true);

    try {
      const response = await releaseSeat(editedPassengerData.id, booking.id, booking.event_id);

      let updatedPassengers = passengers.filter((passenger) => passenger.id !== editedPassengerData.id);

      if (response.data && Object.keys(response.data).length > 0) {
        updatedPassengers.push(response.data);
      }

      updatedPassengers = updatedPassengers.sort((a, b) => a.passenger_order - b.passenger_order);

      setPassengers(updatedPassengers);

      showSnackbar("Seat released successfully!", "success");
      setEditPassengerOpen(false);
      setEditingPassenger(null);
      setErrors({});
      router.reload({ only: ["booking"] });
    } catch (error) {
      console.error("Error releasing seat:", error.response?.data || error);
      showSnackbar("Failed to release seat", "error");
    } finally {
      setReleaseLoading(false);
    }
  };

  const releaseSeat = async (slotId: number, bookingId: number, eventId: number) => {
    return axios.post(route("seat.release", { id: eventId, booking_id: bookingId }), {
      slotId,
      bookingId,
    });
  };

  const cancelInvitation = async (passengerId: number, bookingId: number, eventId: number) => {
    return axios.post(route("passenger_invitation.cancel", { id: eventId, booking_id: bookingId }), {
      passengerId,
      bookingId,
    });
  };

  const handleCancel = () => {
    setOpenConfirm(false);
    setOpenConfirmSeatEmpty(false);
  };

  const getAvatar = (passenger) => {
    const iconProps = {
      sx: {
        width: 50,
        height: 50,
        mr: 2,
        cursor: "pointer",
        color: passenger.lead_passenger ? "#ffa726" : getAvatarColor(passenger),
      },
    };

    const IconComponent = getAvatarIcon(passenger);

    return <IconComponent {...iconProps} />;
  };

  const getAvatarColor = (passenger) => {
    switch (passenger.gender?.toLowerCase()) {
      case "m":
        return "#2196F3";
      case "f":
        return "#E91E63";
      default:
        return "gray";
    }
  };

  const getAvatarIcon = (passenger) => {
    if (passenger.empty) return PersonAdd;
    switch (passenger.gender?.toLowerCase()) {
      case "m":
        return PersonIcon;
      case "f":
        return Person2Icon;
      default:
        return PersonIcon;
    }
  };

  const getPassengerBgColor = (passenger) => {
    if (passenger.lead_passenger) return "#B0BEC5";
    if (passenger.empty) return "#90CAF9";
    return "#FFF59D";
  };

  // Handle set seat as empty
  const showModalSeatEmpty = () => {
    setOpenConfirmSeatEmpty(true);
  };

  // handle confirm / unset empty seat
  const handleConfirmEmptySeat = async () => {
    setOpenConfirmSeatEmpty(false);

    setReleaseLoading(true);
    try {
      const response = await axios.post(
        route("seat.empty", { id: booking.event_id, booking_id: booking.id, slot_id: editedPassengerData?.id }),
        {
          ...editedPassengerData,
          empty_seat: !editedPassengerData?.empty_seat,
        },
      );

      setPassengers((prev) => prev.map((p) => (p.id === editedPassengerData.id ? { ...p, ...response.data } : p)));
      setEditPassengerOpen(false);
      setEditingPassenger(null);
      setErrors({});
      router.reload({ only: ["booking"] });
      showSnackbar("Seat updated successfully!", "success");
    } catch (error) {
      setErrors(error);
      console.error(error);
      showSnackbar("Error updating seat!" + (error.response?.data?.error || error), "error");
    } finally {
      setReleaseLoading(false);
    }
  };

  const handleSwitchLeadPassengerConfirm = async () => {
    if (!switchPassengerData?.id) return;

    try {
      setLoading(true);

      const response = await axios.post(
        route("lead.passenger.switch", {
          event_id: booking.event_id,
          booking_id: booking.id,
        }),
        {
          new_lead_passenger_id: switchPassengerData.id,
          ...switchPassengerData,
        },
      );

      setPassengers(response.data.passengers);
      showSnackbar("Lead passenger updated successfully!", "success");
      setOpenSwitchPassengerModal(false);
      setSelectedUser(null);
      setSwitchStep("search");
      router.reload({ only: ["booking"] });
    } catch (error) {
      console.error("Error switching lead passenger:", error);
      showSnackbar("Error switching lead passenger", "error");
    } finally {
      setLoading(false);
    }
  };

  const SectionTitle = ({ icon: Icon, title, color = "primary.main" }) => (
    <Box sx={{ borderBottom: "1px solid", borderColor: "divider", mb: 2 }}>
      <Box sx={{ display: "flex", alignItems: "center", pb: 1 }}>
        <Icon sx={{ mr: 1, color }} />
        <Typography variant="h6">{title}</Typography>
      </Box>
    </Box>
  );

  const renderChip = (passenger) => {
    if (passenger.empty_seat) {
      return <Chip label="Empty Bed" size="small" color="info" sx={{ color: "white" }} />;
    }

    if (
      editMode &&
      !passenger?.dob &&
      !passenger?.empty_seat &&
      !(passenger?.passenger_invitation && passenger.passenger_invitation.length > 0)
    ) {
      return <Chip label="Add Passenger" size="small" color="primary" sx={{ color: "white" }} />;
    }

    if (passenger?.passenger_invitation && passenger?.passenger_invitation.length > 0) {

      return (
        <Chip
          label="Invited"
          size="small"
          color="success"
          sx={{ color: "white" }}
        />
      );
    }

    if (isSingleRoom) {
      return <Chip label={"Passenger"} size="small" color="default" sx={{ color: "white" }} />;
    }

    if (passenger.lead_passenger) {
      return (
        <Box display="flex" alignItems="center" justifyContent="space-between" width="100%">
          <Chip label="Lead Passenger" size="small" color="warning" />
        </Box>
      );
    }

    return (
      <Chip label={`Passenger #${passenger.passenger_order}`} size="small" color="default" sx={{ color: "white" }} />
    );
  };

  // const onChangeLeadPassenger = (field, value) => {
  //   console.log("onChangeLeadPassenger", field, value);
  //   if (field === "state") {
  //     if (value?.value) {
  //       value = value.value;
  //     }
  //   }
  //   // if (field === "cabin_conf_accp" || field === "terms_n_cons") {
  //   //   value = true; // Always set to true for these fields
  //   // }
  //   setSwitchPassengerData((prev) => ({ ...prev, [field]: value }));
  // }

  const onChangeLeadPassenger = (field, value) => {
    if (field === "state" && value?.value) {
      value = value.value;
    }

    if (field === "cabin_conf_accp" || field === "terms_n_cons") {
      value = true;
    }

    setSwitchPassengerData((prev) => ({ ...prev, [field]: value }));
  };

  const validateSwitchPassengerData = () => {
    const requiredFields = [
      "first_name",
      "last_name",
      "dob",
      "gender",
      "citizenship",
      "email",
      "phone",
      "address_first",
      "city",
      "country",
      "postal_code",
      "emergency_c_name",
      "emergency_c_phone",
      "payment_method",
    ];
    const newErrors = {};

    requiredFields.forEach((field) => {
      if (!switchPassengerData[field]) {
        newErrors[field] = "This field is required";
      }
    });

    setErrors({ response: { data: { errors: newErrors } } });

    return Object.keys(newErrors).length === 0;
  };
  const handleCloseSwitchModal = () => {
    setOpenSwitchPassengerModal(false);
    setSwitchStep("search");
    setSelectedUser(null);
    setSearchQuery("");
    setSwitchPassengerData({});
    setSuggestions([]);
    setErrors({});
  };

  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Seats
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          {passengers.map((passenger, index) => (
            <Grid item xs={12} sm={6} md={3} key={passenger.id + passenger.email}>
              <Box
                display="flex"
                alignItems="center"
                sx={{
                  position: "relative",
                  padding: "10px",
                  borderRadius: "5px",
                  border: "1px solid grey",
                  cursor: "pointer",
                  minHeight: "140px",
                }}
                onClick={() =>
                    passenger?.passenger_invitation?.length
                      ? editMode && handleCancelPassengerInvitation(passenger)
                      : handleEditPassenger(passenger)
                }

              >
                {passenger.lead_passenger && !isSingleRoom && (
                  <Box sx={{ position: "absolute", top: 8, right: 8 }}>
                    <IconButton
                      size="small"
                      disabled={!editMode}
                      onClick={(e) => {
                        e.stopPropagation();
                        handleSwitchLeadPassenger(passenger);
                      }}
                      sx={{
                        borderRadius: 1,
                        padding: "6px",
                        fontSize: "0.75rem",
                        "&:hover": {
                          backgroundColor: "#424242",
                          cursor: "pointer",
                        },
                      }}
                    >
                      Switch Lead Passenger&nbsp;
                      <SwapHoriz fontSize="small" />
                    </IconButton>
                  </Box>
                )}
                {getAvatar(passenger)}
                <Box>
                  <Typography>
                    {passenger?.passenger_invitation?.length ? `Passenger ${index + 1}` : passenger.full_name}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    position: "absolute",
                    bottom: 8,
                    right: 8,
                    display: "flex",
                    flexDirection: "column",
                    gap: "4px",
                    alignItems: "flex-end",
                  }}
                >
                  {renderChip(passenger)}
                </Box>
              </Box>
            </Grid>
          ))}
        </Grid>
      </Paper>

      <EditPassengerModal
        open={editPassengerOpen}
        onClose={() => setEditPassengerOpen(false)}
        passenger={editedPassengerData}
        editMode={editMode}
        onSave={handleSavePassenger}
        onDelete={onDelete}
        showModalSeatEmpty={showModalSeatEmpty}
        savingLoading={savinLoading}
        releaseLoading={releaseLoading}
        emptySeatLoading={releaseLoading}
        isSingleRoom={isSingleRoom}
        booking={booking}
        onChange={(field, value) => {
          if (field === "state") {
            if (value?.value) {
              value = value.value;
            }
          }
          setEditedPassengerData((prev) => ({ ...prev, [field]: value }));
        }}
        errors={errors}
      />

      <Dialog open={openConfirm} onClose={handleCancel}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Are you sure you want to release this seat? This action cannot be undone.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancel} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirm} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={openConfirmCancelInvitation} onClose={handleCancel}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Are you sure you want to cancel the invitation? This action cannot be undone.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseCancelInvitationModal} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirmCancelInvitation} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={openConfirmSeatEmpty} onClose={handleCancel}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>
            {editedPassengerData?.empty_seat
              ? "Are you sure you want delete empty seat? This seat will be available again"
              : "Are you sure you want to SET this seat as empty?"}
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancel} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirmEmptySeat} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={openSwithPassengerModal}
        fullWidth
        maxWidth={false}
        onClose={handleCloseSwitchModal}
        PaperProps={{
          sx: {
            width: {
              xs: "95vw",
              sm: "90vw",
              md: "80vw",
              lg: "70vw",
              xl: "60vw",
            },
            maxWidth: "none",
          },
        }}
      >
        <DialogTitle>Switch Lead Passenger</DialogTitle>
        <DialogContent>
          {switchStep === "search" && (
            <>
              <DialogContentText sx={{ mb: 2 }}>
                Select a customer to switch lead passenger.
                <br />
              </DialogContentText>
              <Alert severity="info" sx={{ mb: 2 }}>
                Note: You cannot switch to a customer who is already registered in another booking or has a lower-tier
                membership.
              </Alert>
              <Autocomplete
                options={suggestions}
                getOptionLabel={(option) =>
                  typeof option === "string" ? option : `${option.full_name} (${option.email})`
                }
                filterOptions={(x) => x}
                getOptionDisabled={(option) => option.has_booking || option.lower_tier}
                inputValue={searchQuery}
                onInputChange={(e, value) => setSearchQuery(value)}
                value={selectedUser}
                loading={searchLoading}
                //onChange={(e, value) => {
                //   if (!value) return;
                //   setSelectedUser(value);
                //   setSwitchPassengerData({ ...value });
                //   setLeadPassengerCurrent(passengers.find(p => p.lead_passenger) || null);
                //   setSwitchStep("edit");

                // }}
                onChange={(e, value) => {
                  if (!value) return;
                  setSelectedUser(value);
                  setSwitchPassengerData({
                    ...value,
                    terms_n_cons: true,
                    cabin_conf_accp: true,
                  });
                  setLeadPassengerCurrent(passengers.find((p) => p.lead_passenger) || null);
                  setSwitchStep("edit");
                }}
                isOptionEqualToValue={(option, value) => option.id === value.id}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label="Search by Email or Name"
                    variant="outlined"
                    InputProps={{
                      ...params.InputProps,
                      endAdornment: (
                        <>
                          {searchLoading ? <CircularProgress color="inherit" size={20} /> : null}
                          {params.InputProps.endAdornment}
                        </>
                      ),
                    }}
                  />
                )}
                renderOption={(props, option) => (
                  <li {...props}>
                    <div style={{ display: "flex", alignItems: "center" }}>
                      <span>{`${option.full_name} (${option.email})`}</span>
                      {option.has_booking && (
                        <Chip label="ALREADY BOOKED" color="error" style={{ marginLeft: "20px" }} />
                      )}
                      {option.lower_tier && <Chip label="LOWER TIER" color="error" style={{ marginLeft: "20px" }} />}
                      {option.is_same_booking && (
                        <Chip label="SAME BOOKING" color="info" style={{ marginLeft: "20px" }} />
                      )}
                    </div>
                  </li>
                )}
              />
            </>
          )}{" "}
          {switchStep == "edit" && (
            <>
              <DialogContentText>
                <Grid>
                  <SectionTitle icon={PersonIcon} title={"PERSONAL INFO"} />
                  {/* First group */}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    {/* First Column */}
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="First Name"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.first_name || ""}
                        onChange={(e) => onChangeLeadPassenger("first_name", e.target.value)}
                        error={!!validation?.first_name}
                        helperText={validation?.first_name}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Middle Name"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.middle_name || ""}
                        onChange={(e) => onChangeLeadPassenger("middle_name", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.middle_name}
                        helperText={validation?.middle_name}
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Last Name"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.last_name || ""}
                        onChange={(e) => onChangeLeadPassenger("last_name", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.last_name}
                        helperText={validation?.last_name}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Date of Birth"
                        variant="outlined"
                        fullWidth
                        type="date"
                        value={switchPassengerData?.dob || ""}
                        onChange={(e) => onChangeLeadPassenger("dob", e.target.value)}
                        InputLabelProps={{ shrink: true }}
                        disabled={isDisabled}
                        error={!!validation?.dob}
                        helperText={validation?.dob}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <FormControl fullWidth>
                        <InputLabel>Gender</InputLabel>
                        <Select
                          value={switchPassengerData?.gender || ""}
                          onChange={(e) => onChangeLeadPassenger("gender", e.target.value)}
                          disabled={isDisabled}
                          label={"Gender"}
                          error={!!validation?.gender}
                          helperText={validation?.gender}
                          required
                        >
                          <MenuItem value="M">Male</MenuItem>
                          <MenuItem value="F">Female</MenuItem>
                          <MenuItem value="O">Other</MenuItem>
                        </Select>
                      </FormControl>
                    </Grid>

                    <Grid item xs={12} md={3}>
                      <Country
                        fullWidth
                        label="Citizenship"
                        variant="outlined"
                        value={switchPassengerData?.citizenship || ""}
                        name={"citizenship"}
                        onChange={(e) => onChangeLeadPassenger("citizenship", e)}
                        disabled={isDisabled}
                        error={!!validation?.citizenship}
                        helperText={validation?.citizenship}
                        required
                      />
                    </Grid>

                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Survivor Number"
                        variant="outlined"
                        fullWidth
                        size="small"
                        value={switchPassengerData?.survivor_number || ""}
                        onChange={(e) => onChangeLeadPassenger("survivor_number", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.survivor_number}
                        helperText={validation?.survivor_number}
                      />
                    </Grid>
                  </Grid>

                  <SectionTitle icon={ContactMailIcon} title={"CONTACT INFO"} />
                  {/* Second group*/}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Email"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.email || ""}
                        onChange={(e) => onChangeLeadPassenger("email", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.email}
                        helperText={validation?.email}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <PhoneNumber
                        label="Phone"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.phone || ""}
                        forceDialCode={true}
                        name={"phone"}
                        onChange={(e) => onChangeLeadPassenger("phone", e)}
                        disabled={isDisabled}
                        error={!!validation?.phone}
                        helperText={validation?.phone}
                        required
                      />
                    </Grid>
                  </Grid>

                  <SectionTitle icon={HomeIcon} title={"ADDRESS INFO"} />
                  {/* First group */}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    <Grid item xs={12} md={5}>
                      <TextField
                        label="Address Line 1"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.address_first || ""}
                        onChange={(e) => onChangeLeadPassenger("address_first", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.address_first}
                        helperText={validation?.address_first}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={5}>
                      <TextField
                        label="Address Line 2"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.address_second || ""}
                        onChange={(e) => onChangeLeadPassenger("address_second", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.address_second}
                        helperText={validation?.citizenship}
                      />
                    </Grid>
                    <Grid item xs={12} md={2}>
                      <TextField
                        label="City"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.city || ""}
                        onChange={(e) => onChangeLeadPassenger("city", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.city}
                        helperText={validation?.city}
                        required
                      />
                    </Grid>

                    <Grid item xs={12} md={6}>
                      <Country
                        fullWidth
                        label="Country"
                        variant="outlined"
                        value={switchPassengerData?.country || ""}
                        name={"country"}
                        //onChange={onChangeCountry}
                        disabled={isDisabled}
                        error={!!validation?.country}
                        helperText={validation?.country}
                        required
                      />
                    </Grid>
                    {selectedCountry === "USA" || selectedCountry === "CAN" ? (
                      <Grid item xs={12} md={3}>
                        <Autocomplete
                          options={selectedOptions}
                          getOptionLabel={(option) => (option.label ? option.label : "")}
                          value={selectedOptions.find((option) => option.value === switchPassengerData?.state) || null}
                          onChange={(e, value) => onChangeLeadPassenger("state", value)}
                          disabled={isDisabled}
                          isOptionEqualToValue={(option, value) => option.value === value.value}
                          renderInput={(params) => (
                            <TextField
                              {...params}
                              required
                              label="State"
                              variant="outlined"
                              error={!!validation?.state}
                              helperText={validation?.state}
                            />
                          )}
                        />
                      </Grid>
                    ) : (
                      <Grid item xs={12} md={3}>
                        <TextField
                          fullWidth
                          label="State"
                          variant="outlined"
                          value={switchPassengerData?.state || ""}
                          name={"state"}
                          onChange={(e) => onChangeLeadPassenger("state", e.target.value)}
                          disabled={isDisabled}
                          error={!!validation?.state}
                          helperText={validation?.state}
                        />
                      </Grid>
                    )}
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Postal Code"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.postal_code || ""}
                        onChange={(e) => onChangeLeadPassenger("postal_code", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.postal_code}
                        helperText={validation?.postal_code}
                        required
                      />
                    </Grid>
                  </Grid>

                  <SectionTitle icon={ReportProblemIcon} title={"EMERGENCY CONTACT"} />
                  {/* First group */}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    <Grid item xs={12} md={3}>
                      <TextField
                        label="Emergency Contact Name"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.emergency_c_name || ""}
                        onChange={(e) => onChangeLeadPassenger("emergency_c_name", e.target.value)}
                        disabled={isDisabled}
                        error={!!validation?.emergency_c_name}
                        helperText={validation?.emergency_c_name}
                        required
                      />
                    </Grid>
                    <Grid item xs={12} md={3}>
                      <PhoneNumber
                        label="Emergency Contact Phone"
                        variant="outlined"
                        fullWidth
                        value={switchPassengerData?.emergency_c_phone || ""}
                        forceDialCode={true}
                        name={"emergency_c_phone"}
                        onChange={(e) => onChangeLeadPassenger("emergency_c_phone", e)}
                        disabled={isDisabled}
                        error={!!validation?.emergency_c_phone}
                        helperText={validation?.emergency_c_phone}
                        required
                      />
                    </Grid>
                  </Grid>

                  <SectionTitle icon={CreditCardIcon} title="PAYMENT INFO" />
                  {/* First group */}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    <Grid item xs={12} md={3}>
                      <FormControl fullWidth error={!!validation?.payment_method}>
                        <InputLabel>Payment Method</InputLabel>
                        <Select
                          value={switchPassengerData?.payment_method || ""}
                          onChange={(e) => onChangeLeadPassenger("payment_method", e.target.value)}
                          disabled={isDisabled}
                          label={"Payment Method"}
                          required
                          error={!!validation?.payment_method}
                          helperText={validation?.payment_method}
                        >
                          {booking.payment_plan === "PAY_IN_FULL" && (
                            <MenuItem value="BANK_TRANSFER">Bank Transfer</MenuItem>
                          )}
                          <MenuItem value="CREDIT_CARD">Credit Card</MenuItem>
                        </Select>
                        {validation?.payment_method && <FormHelperText>{validation.payment_method}</FormHelperText>}
                      </FormControl>
                    </Grid>
                  </Grid>

                  <SectionTitle icon={DirectionsBoatIcon} title="TRAVEL INFO" />
                  {/* Second group*/}
                  <Grid container spacing={2} alignItems="center" sx={{ mb: "1rem" }}>
                    <Grid item xs={12} md={3}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.confirmed_booking_email || false}
                            onChange={(e) => onChangeLeadPassenger("confirmed_booking_email", e.target.checked)}
                            disabled={isDisabled}
                          />
                        }
                        label="Confirmed Booking Email"
                      />
                    </Grid>
                    <Grid item xs={12} md={2}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.terms_n_cons ?? isLeadPassenger === false}
                            onChange={(e, checked) => onChangeLeadPassenger("terms_n_cons", checked)}
                            disabled={true}
                          />
                        }
                        label="Terms & Conditions"
                      />
                    </Grid>
                    <Grid item xs={12}>
                      <TextField
                        label="Special Request"
                        variant="outlined"
                        fullWidth
                        multiline
                        placeholder="e.g. Allergy to peanuts, prefer cabin near elevator"
                        rows={3}
                        size="small"
                        value={switchPassengerData?.special_request || ""}
                        onChange={(e) => onChangeLeadPassenger("special_request", e.target.value)}
                        disabled={isDisabled}
                      />
                    </Grid>
                    <Grid item xs={12} md={2}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.newsletter || false}
                            onChange={(e) => onChangeLeadPassenger("newsletter", e.target.checked)}
                            disabled={isDisabled}
                          />
                        }
                        label="Newsletter"
                      />
                    </Grid>
                    <Grid item xs={12} md={2}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.travel_info || false}
                            onChange={(e) => onChangeLeadPassenger("travel_info", e.target.checked)}
                            disabled={isDisabled}
                          />
                        }
                        label="Travel Info"
                      />
                    </Grid>
                    {isSingleRoom && (
                      <Grid item xs={12} md={2}>
                        <Tooltip title="Single Ticket Agreement">
                          <FormControlLabel
                            control={
                              <Checkbox
                                size="small"
                                checked={switchPassengerData?.single_t_agreement || false}
                                onChange={(e) => onChangeLeadPassenger("single_t_agreement", e.target.checked)}
                                disabled={isDisabled}
                              />
                            }
                            label="STA"
                          />
                        </Tooltip>
                      </Grid>
                    )}
                    <Grid item xs={12} md={2}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.cabin_conf_accp ?? isLeadPassenger === false}
                            onChange={(e, checked) => onChangeLeadPassenger("cabin_conf_accp", checked)}
                            disabled={true}
                          />
                        }
                        label="Cabin Conf Acceptance"
                      />
                    </Grid>
                    <Grid item xs={12} md={2}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            size="small"
                            checked={switchPassengerData?.was_on_board || false}
                            onChange={(e) => onChangeLeadPassenger("was_on_board", e.target.checked)}
                            disabled={isDisabled}
                          />
                        }
                        label="Was On Board"
                      />
                    </Grid>
                  </Grid>
                </Grid>
              </DialogContentText>
            </>
          )}
          {switchStep === "review" && (
            <>
              {" "}
              <Grid container spacing={2}>
                <Grid item xs={12} md={6}>
                  <SectionTitle icon={PersonIcon} title="Current Lead Passenger" />
                  {leadPassengerCurrent && (
                    <Box>
                      <Typography>
                        <strong>Name:</strong> {leadPassengerCurrent.first_name}
                      </Typography>
                      <Typography>
                        <strong>Middle Name:</strong> {leadPassengerCurrent.middle_name}
                      </Typography>
                      <Typography>
                        <strong>Last Name:</strong> {leadPassengerCurrent.last_name}
                      </Typography>
                      <Typography>
                        <strong>Date of Birth:</strong> {leadPassengerCurrent.dob}
                      </Typography>
                      <Typography>
                        <strong>Gender:</strong> {leadPassengerCurrent.gender}{" "}
                      </Typography>
                      <Typography>
                        <strong>Email:</strong> {leadPassengerCurrent.email}
                      </Typography>
                      <Typography>
                        <strong>Phone:</strong> {leadPassengerCurrent.phone}
                      </Typography>
                      <Typography>
                        <strong>Survivor Number:</strong> {leadPassengerCurrent.survivor_number}
                      </Typography>
                      <Typography>
                        <strong>Address First:</strong> {leadPassengerCurrent.address_first}
                      </Typography>
                      <Typography>
                        <strong>Address Second:</strong> {leadPassengerCurrent.address_second}
                      </Typography>
                      <Typography>
                        <strong>City:</strong> {leadPassengerCurrent.city}
                      </Typography>
                      <Typography>
                        <strong>State:</strong> {leadPassengerCurrent.state}
                      </Typography>
                      <Typography>
                        <strong>Country:</strong> {leadPassengerCurrent.country}
                      </Typography>
                      <Typography>
                        <strong>Postal Code:</strong> {leadPassengerCurrent.postal_code}
                      </Typography>
                      <Typography>
                        <strong>Citizenship:</strong> {leadPassengerCurrent.citizenship}
                      </Typography>
                      <Typography>
                        <strong>Emergency Contact Name:</strong> {leadPassengerCurrent.emergency_c_name}
                      </Typography>
                      <Typography>
                        <strong>Emergency Contact Phone:</strong> {leadPassengerCurrent.emergency_c_phone}
                      </Typography>
                      <Typography>
                        <strong>Payment Method:</strong> {leadPassengerCurrent.payment_method}
                      </Typography>
                      <Typography>
                        <strong>Confirmed Booking Email:</strong>{" "}
                        {leadPassengerCurrent.confirmed_booking_email ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Special Request:</strong> {leadPassengerCurrent.special_request}
                      </Typography>
                      <Typography>
                        <strong>Newsletter:</strong> {leadPassengerCurrent.newsletter ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Travel Info:</strong> {leadPassengerCurrent.travel_info ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Terms & Conditions:</strong> {leadPassengerCurrent.terms_n_cons ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Cabin Conf Acceptance:</strong> {leadPassengerCurrent.cabin_conf_accp ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Single Ticket Agreement:</strong>{" "}
                        {leadPassengerCurrent.single_t_agreement ? "Yes" : "No"}
                      </Typography>
                      <Typography>
                        <strong>Was On Board:</strong> {leadPassengerCurrent.was_on_board ? "Yes" : "No"}
                      </Typography>
                    </Box>
                  )}
                </Grid>
                <Grid item xs={12} md={6}>
                  <SectionTitle icon={PersonIcon} title="New Lead Passenger Data" />
                  <Box>
                    <Typography>
                      <strong>Name:</strong> {switchPassengerData.first_name}
                    </Typography>
                    <Typography>
                      <strong>Middle Name:</strong> {switchPassengerData.middle_name}
                    </Typography>
                    <Typography>
                      <strong>Last Name:</strong> {switchPassengerData.last_name}
                    </Typography>
                    <Typography>
                      <strong>Date of Birth:</strong> {switchPassengerData.dob}
                    </Typography>
                    <Typography>
                      <strong>Gender:</strong> {switchPassengerData.gender}{" "}
                    </Typography>
                    <Typography>
                      <strong>Email:</strong> {switchPassengerData.email}
                    </Typography>
                    <Typography>
                      <strong>Phone:</strong> {switchPassengerData.phone}
                    </Typography>
                    <Typography>
                      <strong>Survivor Number:</strong> {switchPassengerData.survivor_number}
                    </Typography>
                    <Typography>
                      <strong>Address First:</strong> {switchPassengerData.address_first}
                    </Typography>
                    <Typography>
                      <strong>Address Second:</strong> {switchPassengerData.address_second}
                    </Typography>
                    <Typography>
                      <strong>City:</strong> {switchPassengerData.city}
                    </Typography>
                    <Typography>
                      <strong>State:</strong> {switchPassengerData.state}
                    </Typography>
                    <Typography>
                      <strong>Country:</strong> {switchPassengerData.country}
                    </Typography>
                    <Typography>
                      <strong>Postal Code:</strong> {switchPassengerData.postal_code}
                    </Typography>
                    <Typography>
                      <strong>Citizenship:</strong> {switchPassengerData.citizenship}
                    </Typography>
                    <Typography>
                      <strong>Emergency Contact Name:</strong> {switchPassengerData.emergency_c_name}
                    </Typography>
                    <Typography>
                      <strong>Emergency Contact Phone:</strong> {switchPassengerData.emergency_c_phone}
                    </Typography>
                    <Typography>
                      <strong>Payment Method:</strong> {switchPassengerData.payment_method}
                    </Typography>
                    <Typography>
                      <strong>Confirmed Booking Email:</strong>{" "}
                      {switchPassengerData.confirmed_booking_email ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Special Request:</strong> {switchPassengerData.special_request}
                    </Typography>
                    <Typography>
                      <strong>Newsletter:</strong> {switchPassengerData.newsletter ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Travel Info:</strong> {switchPassengerData.travel_info ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Terms & Conditions:</strong> {switchPassengerData.terms_n_cons ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Cabin Conf Acceptance:</strong> {switchPassengerData.cabin_conf_accp ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Single Ticket Agreement:</strong> {switchPassengerData.single_t_agreement ? "Yes" : "No"}
                    </Typography>
                    <Typography>
                      <strong>Was On Board:</strong> {switchPassengerData.was_on_board ? "Yes" : "No"}
                    </Typography>
                    {/* campos editados */}
                  </Box>
                </Grid>
              </Grid>
            </>
          )}
        </DialogContent>

        <DialogActions>
          {switchStep === "edit" && (
            <>
              <Button onClick={() => setSwitchStep("search")} color="secondary">
                Back
              </Button>
              <Button
                onClick={() => {
                  if (validateSwitchPassengerData()) {
                    setSwitchStep("review");
                  }
                }}
                variant="contained"
                color="primary"
              >
                Review
              </Button>
            </>
          )}
          {switchStep === "review" && (
            <>
              <Button onClick={() => setSwitchStep("edit")} color="secondary">
                Back
              </Button>
              <Button onClick={handleSwitchLeadPassengerConfirm} variant="contained" color="primary">
                Confirm
              </Button>
            </>
          )}
          {switchStep === "search" && (
            <Button onClick={handleCloseSwitchModal} color="secondary">
              Cancel
            </Button>
          )}
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default Passengers;
