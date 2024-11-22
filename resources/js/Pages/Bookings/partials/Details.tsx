import React, { useState } from "react";
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
} from "@mui/material";
import EditIcon from '@mui/icons-material/Edit';
import { router } from "@inertiajs/react";

const Detail = ({ event, booking, editMode }) => {
  const [open, setOpen] = useState(false); 
  const [cabinNumber, setCabinNumber] = useState(booking?.cabin?.cabin_number || ""); //cabin number

  const handleEditClick = () => {
    setOpen(true); 
  };

  const handleClose = () => {
    setOpen(false); 
  };

  const handleSave = () => {
   router.post(
    route("bookings.updateCabin", {id: event.id }),
    { cabin_number: cabinNumber, booking_id : booking.id },
    {
      onSuccess: () => {
        setOpen(false); 
      },
      onError: (errors) => {
        console.error(errors); 
      },
    }
  );
    setOpen(false); 
  };

  return (
    <>
      <Box>
        <Typography variant="h5" mb={2}>Cabin Details</Typography>
        <Paper
          variant="outlined"
          sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}
        >
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <Table>
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
                </TableBody>
              </Table>
            </Grid>
          </Grid>
        </Paper>
      </Box>

      {/* Dialog for Editing Cabin Number */}
      <Dialog open={open} onClose={handleClose}>
        <DialogTitle>Edit Cabin Number</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Cabin Number"
            type="text"
            fullWidth
            value={cabinNumber}
            onChange={(e) => setCabinNumber(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} color="secondary">Cancel</Button>
          <Button onClick={handleSave} color="primary">Save</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default Detail;

