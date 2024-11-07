import React from "react";
import {
  Avatar,
  Box,
  Button,
  Grid,
  Paper,
  Typography,
  Table,
  TableBody,
  TableRow,
  TableCell,
  Divider,
  IconButton,
} from "@mui/material";
import EditIcon from '@mui/icons-material/Edit';
import PersonIcon from '@mui/icons-material/Person';

const Passengers = ({passengers}) => {

    console.log(passengers);
    const getOrdinalSuffix = (n: number): string => {
        if (n === 1) return "st";
        if (n === 2) return "nd";
        if (n === 3) return "rd";
        return "th";
    };
  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Passengers
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
            {passengers.map((passenger, index) => {
                 const isLeadPassenger = passenger.lead_passenger;
                 const displayText = isLeadPassenger
                 ? "Lead Passenger"
                 : `${index+1}${getOrdinalSuffix(index+1)} Passenger`;
                return(<Grid item xs={12} sm={4}>
                    <Box display="flex" alignItems="center">
                      {/* <Avatar src="https://via.placeholder.com/50" sx={{ width: 50, height: 50, mr: 2 }} /> */}
                      <Avatar sx={{ width: 50, height: 50, mr: 2 }}>
                        <PersonIcon />
                      </Avatar>
                      <Box>
                        <Typography>{passenger.full_name}</Typography>
                        <Typography variant="caption">{displayText}</Typography>
                      </Box>
                    </Box>
                  </Grid>)
            })}
          
          {/* <Grid item xs={12} sm={4}>
            <Box display="flex" alignItems="center">
              <Avatar sx={{ width: 50, height: 50, mr: 2, bgcolor: "grey.700" }}>KD</Avatar>
              <Box>
                <Typography>Karen Doe</Typography>
                <Typography variant="caption" color="error">
                  <Box component="span" color="error.main" sx={{ borderRadius: 1, px: 1, backgroundColor: "red", color:"white"}}>
                    Not confirmed account
                  </Box>
                </Typography>
              </Box>
            </Box>
          </Grid>
          <Grid item xs={12} sm={4}>
            <Box display="flex" alignItems="center">
              <Avatar sx={{ width: 50, height: 50, mr: 2, bgcolor: "grey.800" }}>?</Avatar>
              <Typography variant="caption">Missing Passenger</Typography>
            </Box>
          </Grid> */}
          <Grid item xs={12} textAlign="right">
            <IconButton color="secondary">
                                    <EditIcon />
                                </IconButton>
          </Grid>
        </Grid>
      </Paper>

      {/* Resumen de Pago */}
      <Typography variant="h5" gutterBottom>
        Payment summary
      </Typography>
      <Typography variant="body2" color="gray" mb={1}>
        Please note this information is not updated immediately. It may take up to 48 hours for the payment to be reflected.
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Table>
          <TableBody>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell align="right">345435sdads3-343434</TableCell>
              <TableCell align="right">
                {/* <Button variant="outlined" color="secondary" startIcon={<EditIcon />}>Edit</Button> */}
                <IconButton color="secondary">
                                    <EditIcon />
                                </IconButton>
              </TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Total to pay</TableCell>
              <TableCell align="right">USD 4,833.00</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Current payments:</TableCell>
              <TableCell align="right">USD 4,833.00</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>To pay:</TableCell>
              <TableCell align="right">USD 0</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </Paper>

      {/* Detalles de Pago (Placeholder) */}
      <Typography variant="h5" align="center">
        Payment details
      </Typography>
    </Box>
  );
};

export default Passengers;
