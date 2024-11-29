import React, { useState, useEffect } from "react";
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
} from "@mui/material";
import { Axios } from "axios";

const Passengers = ({ booking }) => {
  const [addPassengerOpen, setAddPassengerOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [searchResults, setSearchResults] = useState([]);
  const [selectedUser, setSelectedUser] = useState(null);
  const [passengers, setPassengers] = useState(booking.passengers);

  const maxCapacity = booking.cabin.cabin_category.capacity;

  // Fetch users based on the search query
  const fetchUsers = async (query) => {
    try {
      const response = await axios.get("/passengers/search", {
        params: { query },
      });
      setSearchResults(response.data);
    } catch (error) {
      console.error("Error fetching users:", error);
    }
  };

  // Add passenger to the booking
  const handleAddPassenger = async () => {
    try {
      const response = await axios.post("/passengers/add", {
        booking_id: booking.id,
        user_id: selectedUser.id,
        full_name: selectedUser.name,
        email: selectedUser.email,
      });
      setPassengers([...passengers, response.data]);
      setAddPassengerOpen(false);
      setSelectedUser(null);
    } catch (error) {
      console.error("Error adding passenger:", error);
      alert(error.response?.data?.error || "An error occurred.");
    }
  };

  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Passengers
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          {passengers.map((passenger, index) => (
            <Grid item xs={12} sm={4} key={passenger.id || index}>
              <Box display="flex" alignItems="center">
                <Avatar sx={{ width: 50, height: 50, mr: 2 }}>
                  {passenger.full_name[0]}
                </Avatar>
                <Box>
                  <Typography>{passenger.full_name}</Typography>
                  <Typography variant="caption">
                    {passenger.lead_passenger
                      ? "Lead Passenger"
                      : `Passenger ${index + 1}`}
                  </Typography>
                </Box>
              </Box>
            </Grid>
          ))}

          {passengers.length < maxCapacity && (
            <Grid item xs={12} sm={4}>
              <Box
                display="flex"
                alignItems="center"
                justifyContent="center"
                sx={{
                  border: "2px dashed gray",
                  borderRadius: "50%",
                  width: 50,
                  height: 50,
                  cursor: "pointer",
                  color: "gray",
                  ":hover": { borderColor: "blue", color: "blue" },
                }}
                onClick={() => setAddPassengerOpen(true)}
              >
                <Typography variant="h6" component="div" sx={{ fontWeight: "bold" }}>
                  +
                </Typography>
              </Box>
            </Grid>
          )}
        </Grid>
      </Paper>

      {/* Modal para añadir pasajero */}
      <Modal open={addPassengerOpen} onClose={() => setAddPassengerOpen(false)}>
        <Paper sx={{ p: 4, margin: "auto", maxWidth: 600 }}>
          <Typography variant="h6" gutterBottom>
            Add Passenger
          </Typography>
          <Autocomplete
            options={searchResults}
            getOptionLabel={(option) => `${option.name} (${option.email})`}
            onInputChange={(e, value) => {
              setSearchQuery(value);
              fetchUsers(value);
            }}
            onChange={(e, value) => setSelectedUser(value)}
            renderInput={(params) => (
              <TextField {...params} label="Search Users" variant="outlined" fullWidth />
            )}
          />
          <Box mt={2} display="flex" justifyContent="space-between">
            <Button
              variant="contained"
              color="primary"
              onClick={handleAddPassenger}
              disabled={!selectedUser}
            >
              Add Passenger
            </Button>
            <Button
              variant="outlined"
              color="secondary"
              onClick={() => setAddPassengerOpen(false)}
            >
              Cancel
            </Button>
          </Box>
        </Paper>
      </Modal>
    </Box>
  );
};

export default Passengers;
