import React from "react";
import {
  Box,
  Typography,
  LinearProgress,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableRow,
  Button,
  Divider,
  Grid,
  IconButton,
  Avatar,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import EditIcon from "@mui/icons-material/Edit";

const Payment = ({ booking, passenger, number, count, lead = false }) => {
  return (
    <Box minHeight="100vh">
      <Box display="flex" sx={{textAlign:"center", width:"100%",justifyContent:"center"}}>
        <Divider orientation="vertical" variant="middle" flexItem sx={{height:'100px', "&::before, &::after": {
      borderColor: "secondary.light", border:'1px dashed',
    },}}> <Avatar
          sx={{ background: "#20a22d", color: "#fff", fontSize: "0.9rem" }}
          
        >
          {number}/{count}
        </Avatar></Divider>
      </Box>

      <Box display="flex" alignItems="center" justifyContent="center" mb={4}>
       
      </Box>

      <Typography variant="h5" mb={2}>
        {lead ? "Lead Passenger" : "Passenger"}
      </Typography>

      <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c" }}>
        {/* Indicador de Pago Completo */}
        <Box display="flex" alignItems="center" mb={2}>
          <CheckCircleIcon sx={{ color: "green", mr: 1 }} />
          <Typography color="green">Payment is complete</Typography>
        </Box>

        <Box
          display="flex"
          alignItems="center"
          justifyContent="space-between"
          mb={1}
        >
          <Typography>Paid:</Typography>
          <Typography>100%</Typography>
        </Box>

        <LinearProgress
          variant="determinate"
          value={100}
          sx={{ height: 10, borderRadius: 5, bgcolor: "gray" }}
        />

        <Table size="small" sx={{ mt: 2, color: "white" }}>
          <TableBody>
            <TableRow>
              <TableCell>Payment type:</TableCell>
              <TableCell align="right">Full Payment</TableCell>
              <TableCell align="right">
                <IconButton color="secondary">
                  <EditIcon />
                </IconButton>
              </TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Paid:</TableCell>
              <TableCell align="right" sx={{ color: "#3f8cff" }}>
                $3,178.35
              </TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Balance:</TableCell>
              <TableCell align="right">$0</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Official Ticket Price:</TableCell>
              <TableCell align="right">USD 2,833.00</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>5% Discount:</TableCell>
              <TableCell align="right">USD 141.65</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Net Ticket Price:</TableCell>
              <TableCell align="right">USD 1,800.00</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>Taxes & Fees:</TableCell>
              <TableCell align="right">USD 180.00</TableCell>
            </TableRow>
            <TableRow>
              <TableCell>
                <Typography fontWeight="bold">Total Ticket Price:</Typography>
              </TableCell>
              <TableCell align="right">
                <Typography fontWeight="bold">USD 3,178.35</Typography>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <Divider sx={{ my: 2, borderColor: "gray" }} />

        <Grid container spacing={2}>
          <Grid item xs={12} sm={6}>
            <Button
              fullWidth
              variant="outlined"
              sx={{ color: "white", borderColor: "gray" }}
            >
              User details
            </Button>
          </Grid>
          <Grid item xs={12} sm={6}>
            <Button
              fullWidth
              variant="outlined"
              sx={{ color: "white", borderColor: "gray" }}
            >
              Payment history
            </Button>
          </Grid>
        </Grid>
      </Paper>
    </Box>
  );
};

export default Payment;
