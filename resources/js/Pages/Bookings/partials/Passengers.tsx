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
} from "@mui/material";
import EditIcon from '@mui/icons-material/Edit';

const Passengers = () => {
  return (
    <Box>
      <Typography variant="h5" gutterBottom>
        Passengers
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} sm={4}>
            <Box display="flex" alignItems="center">
              <Avatar src="https://via.placeholder.com/50" sx={{ width: 50, height: 50, mr: 2 }} />
              <Box>
                <Typography>John Doe</Typography>
                <Typography variant="caption">Lead passenger</Typography>
              </Box>
            </Box>
          </Grid>
          <Grid item xs={12} sm={4}>
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
          </Grid>
          <Grid item xs={12} textAlign="right">
            <Button variant="outlined" color="secondary" startIcon={<EditIcon />}>Edit</Button>
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
                <Button variant="outlined" color="secondary" startIcon={<EditIcon />}>Edit</Button>
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
