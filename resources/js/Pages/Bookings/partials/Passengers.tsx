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
  const isSingleRoom = [CabinType.SINGLE_MALE, CabinType.SINGLE_FEMALE].includes(
    booking?.cabin?.cabin_type_id
  );

  // Open edit modal and set passenger data
  const handleEditPassenger = (passenger) => {
    setEditingPassenger(passenger);
    setEditedPassengerData(passenger);
    setEditPassengerOpen(true);
  };

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

  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Seats
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          {passengers.map((passenger, index) => (
            <Grid item xs={12} sm={6} md={3} key={passenger.id + passenger.email}>
              <Box display="flex" alignItems="center" sx={{padding:'10px', borderRadius:'5px', border: '1px solid grey', cursor:'pointer'}} onClick={() => handleEditPassenger(passenger)}>
                {getAvatar(passenger)} 
                <Box>
                  <Typography variant="h6" style={{ fontSize: '0.8rem', fontWeight: '400' }}>{passenger.full_name}</Typography>
                  {!passenger.empty && (<Chip
                    label={passenger.lead_passenger ? "Lead Passenger" : `Passenger ${[passenger.passenger_order]}`}
                    size="small"
                    color={passenger.lead_passenger ? "warning" : "default"}
                    sx={{ color: "white" }}
                  />)}
                  {passenger.empty == true && (
                    <>
                      <Chip label="Available" size="small" color="info" sx={{ color: "white" }} />
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

    </Box>
  );
};

export default Passengers;
