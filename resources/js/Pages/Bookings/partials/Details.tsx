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
} from "@mui/material";
import "dayjs/locale/en";
import { BookingTagEnum } from "@/enums/TagEnum";

const Detail = ({ booking }) => {
  console.log(booking);

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
              {/* <TableHead>
              <TableRow>
                <TableCell align="left">Campo</TableCell>
                <TableCell align="left">Valor</TableCell>
              </TableRow>
            </TableHead> */}
              <TableBody>
                <TableRow>
                  <TableCell>Cabin Type</TableCell>
                  <TableCell>
                    {booking?.cabin?.cabin_type?.cabin_type}
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>Category</TableCell>
                  <TableCell>
                    {booking?.cabin?.cabin_category?.category_name}
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>Number</TableCell>
                  <TableCell>{booking?.cabin?.cabin_number}</TableCell>
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
