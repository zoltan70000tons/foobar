import React, { useState } from "react";

import {
  Grid,
  Typography,
  Table,
  TableHead,
  TableBody,
  TableFooter,
  TableRow,
  TableCell,
  Paper,
  Box,
  IconButton,
} from "@mui/material";
import "dayjs/locale/en";
import { BookingTagEnum } from "@/enums/TagEnum";
import EditIcon from '@mui/icons-material/Edit';
const Detail = ({ booking, editMode }) => {
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
                  <TableCell>
                    {booking?.cabin?.cabin_type?.cabin_type}
                  </TableCell>
                  <TableCell align="right">
                  <IconButton color="secondary" disabled={!editMode}>
                                    <EditIcon />
                                </IconButton>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>Category</TableCell>
                  <TableCell>
                    {booking?.cabin?.cabin_category?.title}
                  </TableCell>
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
    </>
  );
};

export default Detail;
