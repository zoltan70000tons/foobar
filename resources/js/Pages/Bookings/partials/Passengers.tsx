import React, { useState } from "react";
import {
  Avatar,
  Box,
  Button,
  Grid,
  Paper,
  Typography,
  TextField,
  Modal,
  Autocomplete,
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

type PassengersProps = {
  booking: Booking;
  editMode: boolean;
};

type Booking = {
  id: number;
};


const Passengers: React.FC<PassengersProps> = ({ booking, editMode }) => {
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



  // Open edit modal and set passenger data
  const handleEditPassenger = (passenger) => {
    setEditingPassenger(passenger);
    setEditedPassengerData(passenger);
    setEditPassengerOpen(true);
  };

  // Update passenger details
  const handleSavePassenger = async () => {
    try {
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
    }
  };

  const onDelete = () => {
    setOpenConfirm(true);
  };

  const handleConfirm = async () => {
    setOpenConfirm(false);

    try {
      const response = await axios.post(
        route('seat.release', { id: booking.event_id, booking_id: booking.id }),
        {
          slotId: editedPassengerData.id,
          bookingId: booking.id
        }
      );

      if (response.status === 200) {
        showSnackbar("Seat released succesfully!", "success");
        setEditPassengerOpen(false);
        setEditedPassengerData(response.data);
        router.reload({ only: ['booking'], preserveScroll: true });
      } else {
        console.error("Failed to release seat", response.data);
        showSnackbar("Failed to release seat", "error");
      }
    } catch (error) {
      console.error("Error releasing seat:", error.response || error);
    }
  };

  const handleCancel = () => {
    setOpenConfirm(false);
  };

  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Seats
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          {passengers.map((passenger, index) => (
            <Grid item xs={12} sm={3} key={passenger.id + passenger.email}>
              <Box display="flex" alignItems="center">
                <Avatar
                  sx={{
                    width: 50, height: 50, mr: 2, cursor: "pointer", bgcolor: passenger.lead_passenger
                      ? green[800]
                      : passenger.empty
                        ? grey[500]
                        : colors[index]
                          ? colors[index][500]
                          : grey[300]
                  }}
                  onClick={() => handleEditPassenger(passenger)}
                >
                  {passenger.full_name[0]}
                </Avatar>
                <Box>
                  <Typography>{passenger.full_name}</Typography>
                  {passenger.lead_passenger ? <Chip label="Lead Passenger" size="small" color="warning" /> : `Passenger ${index + 1}`}
                  {passenger.empty ? <><br /><Chip label="available" size="small" color="info" sx={{ color: "white" }} /></> : <></>}
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
