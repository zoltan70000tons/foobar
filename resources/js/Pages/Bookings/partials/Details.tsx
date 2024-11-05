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
} from "@mui/material";
import "dayjs/locale/en";
import { BookingTagEnum } from "@/enums/TagEnum";

const Detail = ({ booking }) => {
   console.log(booking);

  return (
    <>
      <Grid container spacing={2}>
        <Grid item xs={12} md={6}>
          <Typography variant="h5">Cabin Detail</Typography>
        </Grid>
        <Grid item xs={12}>
          <Table>
            {/* <TableHead>
              <TableRow>
                <TableCell align="left">Campo</TableCell>
                <TableCell align="left">Valor</TableCell>
              </TableRow>
            </TableHead> */}
            <TableBody>
                <TableRow >
                  <TableCell>Cabin Type</TableCell>
                  <TableCell>{booking?.cabin?.cabin_type?.cabin_type}</TableCell>
                </TableRow>
                <TableRow >
                  <TableCell>Category</TableCell>
                  <TableCell>{booking?.cabin?.cabin_category?.category_name}</TableCell>
                </TableRow>
                <TableRow >
                  <TableCell>Number</TableCell>
                  <TableCell>{booking?.cabin?.cabin_number}</TableCell>
                </TableRow>
            </TableBody>
          </Table>
        </Grid>
      </Grid>
    </>
  );
};

export default Detail;
