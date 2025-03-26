import React, { useState } from "react";
import {
  Avatar,
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
} from "@mui/material";
import axios from "axios";
import { deepOrange, deepPurple, red, pink, purple, yellow, lime, brown, grey, blueGrey, teal, green } from '@mui/material/colors';
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import EditPassengerModal from "./EditPassengerModal";
import { router } from "@inertiajs/react";
import { PersonAdd } from "@mui/icons-material";
import PersonIcon from '@mui/icons-material/Person';
import Person2Icon from '@mui/icons-material/Person2';


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
  const [selectedUser, setSelectedUser] = useState(null);
  const [openConfirm, setOpenConfirm] = useState(false);
  const [passengers, setPassengers] = useState(
    [...booking.passengers].sort((a, b) => a.passenger_order - b.passenger_order)
  );
  const [editingPassenger, setEditingPassenger] = useState(null);
  const [editedPassengerData, setEditedPassengerData] = useState({});
  const [errors, setErrors] = useState({});
  const colors = [blueGrey, deepPurple, red, yellow];
  const { showSnackbar } = useSnackbar();
  const [savinLoading, setSavingLoading] = useState(false);
  const [releaseLoading, setReleaseLoading] = useState(false);
  const [passengerToCancelInvitationFor, setPassengerToCancelInvitationFor] = useState(null);
  const isSingleRoom = [CabinType.SINGLE_MALE, CabinType.SINGLE_FEMALE].includes(
    booking?.cabin?.cabin_type_id
  );

  // Open edit modal and set passenger data
  const handleEditPassenger = (passenger) => {
    setEditingPassenger(passenger);
    setEditedPassengerData(passenger);
    setEditPassengerOpen(true);
  };

  const handleCancelPassengerInvitation = (passenger) => {
    setPassengerToCancelInvitationFor(passenger);
    setOpenConfirmCancelInvitation(true);

  }

  const handleCloseCancelInvitationModal = () => {
    setOpenConfirmCancelInvitation(false);
  }

  const handleConfirmCancelInvitation = async () => {
    setOpenConfirmCancelInvitation(false);
    setReleaseLoading(true);

    try {
      if (!passengerToCancelInvitationFor) {
        throw new Error("Passenger not found");
      }
      const response = await cancelInvitation(passengerToCancelInvitationFor.id, booking.id, booking.event_id);

      setPassengers(response.data.passengers);

      showSnackbar('Invitation successfully cancelled!', 'success');

      setErrors({});
    } catch (error) {
      console.error("Error cancelling invitation:", error.response?.data || error);
      showSnackbar("Failed to cancel invitation", "error");
    } finally {
      setPassengerToCancelInvitationFor(null);
      setReleaseLoading(false);
    }
  }

  // Update passenger details
  const handleSavePassenger = async () => {
    try {
      setSavingLoading(true);
      const response = await axios.post(route('seat.update', { id: booking.event_id, booking_id: booking.id }), {
        ...editedPassengerData
      });
      setPassengers((prev) =>
        prev.map((p) =>
          p.id === editingPassenger.id
            ? { ...p, ...response.data }
            : p
        )
      );
      setEditPassengerOpen(false);
      setEditingPassenger(null);
      setErrors({});
      showSnackbar("Passenger data updated succesfully!", "success");
    } catch (error) {
      setErrors(error);
      showSnackbar("Error updating passenger data!", "error");
    } finally {
      setSavingLoading(false);
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

      let updatedPassengers = passengers.filter(
        (passenger) => passenger.id !== editedPassengerData.id
      );

      if (response.data && Object.keys(response.data).length > 0) {
        updatedPassengers.push(response.data);
      }

      updatedPassengers = updatedPassengers.sort((a, b) => a.passenger_order - b.passenger_order);

      setPassengers(updatedPassengers);

      showSnackbar("Seat released successfully!", "success");
      setEditPassengerOpen(false);
      setEditingPassenger(null);
      setErrors({});
    } catch (error) {
      console.error("Error releasing seat:", error.response?.data || error);
      showSnackbar("Failed to release seat", "error");
    } finally {
      setReleaseLoading(false);
    }
  };

  const releaseSeat = async (slotId: number, bookingId: number, eventId: number) => {
    return axios.post(route('seat.release', { id: eventId, booking_id: bookingId }), {
      slotId,
      bookingId
    });
  };

  const cancelInvitation = async (passengerId: number, bookingId: number, eventId: number) => {
    return axios.post(route('passenger_invitation.cancel', { id: eventId, booking_id: bookingId }), {
      passengerId,
      bookingId,
    })
  }

  const handleCancel = () => {
    setOpenConfirm(false);
  };

  const getAvatar = (passenger) => {
    console.log(passenger);
    const iconProps = {
      sx: {
        width: 50,
        height: 50,
        mr: 2,
        cursor: "pointer",
        color: passenger.lead_passenger ? '#ffa726' : getAvatarColor(passenger),
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

  console.log(passengers)

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
                  position: 'relative',
                  padding: '10px',
                  borderRadius: '5px',
                  border: '1px solid grey',
                  cursor: 'pointer',
                  minHeight: '120px',
                }}
                onClick={() =>
                  passenger?.passenger_invitation?.length
                    ? handleCancelPassengerInvitation(passenger)
                    : handleEditPassenger(passenger)
                }
              >
                {getAvatar(passenger)}
                <Box>
                  <Typography>
                    {passenger?.passenger_invitation?.length
                      ? `Passenger ${index + 1}`
                      : passenger.full_name}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    position: 'absolute',
                    bottom: 8,
                    right: 8,
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '4px',
                    alignItems: 'flex-end',
                  }}
                >
                  {passenger.passenger_invitation?.length > 0 ? (
                    <Chip label="Invited" size="small" color="success" sx={{ color: "white" }} />
                  ) : (
                    <>
                      {passenger.lead_passenger ? (
                        <Chip label="Lead Passenger" size="small" color="warning" />
                      ) : passenger.empty ? (
                        <Chip label={`Passenger #${passenger.passenger_order}`} size="small" color="default" sx={{ color: "white" }} />
                      ) : (
                        <Chip label="Available" size="small" color="info" sx={{ color: "white" }} />
                      )}
                    </>
                  )}
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
        savingLoading={savinLoading}
        releaseLoading={releaseLoading}
        isSingleRoom={isSingleRoom}
        onChange={(field, value) =>
          setEditedPassengerData((prev) => ({ ...prev, [field]: value }))
        }
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

    </Box>
  );
};

export default Passengers;
