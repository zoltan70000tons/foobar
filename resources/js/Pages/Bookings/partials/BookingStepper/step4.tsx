import { formatCurrency } from "@/Helpers/stringUtils";
import React, { useEffect } from "react";
import {
  Box,
  Paper,
  Table,
  TableBody, TableCell,
  TableContainer,
  TableRow,
  Tabs,
  Tab,
  Typography
} from "@mui/material";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { selectBooking } from "@/store/slices/selectors";
import { fetchBookingFinalPrice } from "@/store/thunks/fetchBookingFinalPrice";
import CellValue from "@/Helpers/CellValue";
import { UnknownAction } from "@reduxjs/toolkit";

export type PriceCalc = {
  extras: number;
  save: string;
  total: number;
  totalPassenger: number;
};

interface TabPanelProps {
  children: React.ReactNode;
  value: number;
  index: number;
}

const TabPanel: React.FC<TabPanelProps> = ({ children, value, index }) => {
  return (
    <div role="tabpanel" hidden={value !== index}>
      {value === index && <Box sx={{ p: 2 }}>{children}</Box>}
    </div>
  );
};

export const BookingStepperStepFour = () => {
  const dispatch = useAppDispatch();
  const {
    setTabValue,
  } = useBookingActions();

  const {
    cabinCategory,
    cabinType,
    paymentPlan,
    cabinNumber,
    installments: numberOfInstallments,
    passenger,
    loading,
    tabValue,
    priceCalc,
    addons,
  } = useAppSelector(selectBooking);

  const rows = [
    { label: "Type", value: cabinType?.cabin_type },
    { label: "Category", value: cabinCategory?.title },
    { label: "Cabin Number", value: cabinNumber },
    { label: "Capacity", value: cabinCategory?.spec?.capacity },
    {
      label: "Price Per Person Before Calculations",
      value: formatCurrency(cabinCategory?.price),
    },
  ];

  const {
    carbonOffset,
    youChooseYourCabin,
  } = addons;

  const priceCalcPayload = React.useMemo(() => {
    if (!cabinType || !cabinCategory || !paymentPlan || !passenger) return null;

    return {
      event_id: cabinCategory.event_id,

      cabin_number: cabinNumber,
      cabin_capacity: cabinCategory.capacity,
      cabin_category_id: cabinCategory.id,
      cabin_type_id: cabinType.id,
      cabin_category_spec_id: cabinCategory.cabin_category_spec_id,

      payment_plan: paymentPlan.value,
      number_of_installments: numberOfInstallments?.value ?? null,

      carbon_offset: carbonOffset,
      you_choose_your_cabin: youChooseYourCabin,

      passenger: {
        id: passenger.id,
        gender: passenger.gender,
        survivor_number: passenger.survivor_number,
        payment_method: passenger.payment_method,
      },
    };
  }, [
    cabinType,
    cabinCategory,
    paymentPlan,
    cabinNumber,
    numberOfInstallments,
    carbonOffset,
    youChooseYourCabin,
    passenger,
  ]);

  useEffect(() => {
    if (!priceCalcPayload) return;

    dispatch(fetchBookingFinalPrice(priceCalcPayload) as UnknownAction);
  }, [dispatch, priceCalcPayload]);

  return (
    <Box>
      <Typography variant="h6" gutterBottom>
        Confirm Booking
      </Typography>
      <Tabs
        value={tabValue}
        onChange={(_, newValue) => setTabValue(newValue)}
        indicatorColor="primary"
        textColor="primary"
        sx={{ mb: 2 }}
        aria-label="Booking Details Tabs"
      >
        <Tab label="Cabin Details" />
        <Tab label="Lead Passenger" />
        <Tab label="Payment Info" />
      </Tabs>

      <TabPanel value={tabValue} index={0}>
        {cabinType && cabinCategory ? (
          <TableContainer component={Paper} elevation={3}>
            <Table size="small">
              <TableBody>
                {rows.map((row) => (
                  <TableRow key={row.label}>
                    <TableCell>
                      <strong>{row.label}:</strong>
                    </TableCell>
                    <CellValue loading={loading}>{row.value}</CellValue>
                  </TableRow>
                ))}

                {priceCalc?.totalPassenger != null && (
                  <TableRow>
                    <TableCell>
                      <strong>Price per Person with Tax after Discount and Add-Ons:</strong>
                    </TableCell>
                    <TableCell>{formatCurrency(priceCalc.totalPassenger)}</TableCell>
                  </TableRow>
                )}
                {priceCalc?.total != null && (
                  <TableRow>
                    <TableCell>
                      <strong>Total with Tax After Discounts and Add-Ons:</strong>
                    </TableCell>
                    <TableCell>{formatCurrency(priceCalc.total)}</TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </TableContainer>
        ) : (
          <Typography color="error">Unable to load cabin details</Typography>
        )}
      </TabPanel>

      {/* Tab Panel for Lead Passenger */}
      <TabPanel value={tabValue} index={1}>
        {passenger ? (
          <TableContainer component={Paper} elevation={3}>
            <Table size="small">
              <TableBody>
                <TableRow>
                  <TableCell>
                    <strong>Name:</strong>
                  </TableCell>
                  <TableCell>
                    {passenger.first_name} {passenger.last_name}
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>Email:</strong>
                  </TableCell>
                  <TableCell>{passenger.email}</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>Phone:</strong>
                  </TableCell>
                  <TableCell>{passenger.phone}</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>Gender:</strong>
                  </TableCell>
                  <TableCell>{passenger.gender}</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>Address 1:</strong>
                  </TableCell>
                  <TableCell>{passenger.address_first}</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>City:</strong>
                  </TableCell>
                  <TableCell>{passenger.city}</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell>
                    <strong>Country:</strong>
                  </TableCell>
                  <TableCell>{passenger.country}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </TableContainer>
        ) : (
          <Typography color="error">No passenger selected</Typography>
        )}
      </TabPanel>
      <TabPanel value={tabValue} index={2}>
        <TableContainer component={Paper} elevation={3}>
          <Table size="small">
            <TableBody>
              <TableRow>
                <TableCell>
                  <strong>Payment Plan:</strong>
                </TableCell>
                <TableCell>{paymentPlan?.value}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell>
                  <strong>Payment Method:</strong>
                </TableCell>
                <TableCell>{passenger.payment_method}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell>
                  <strong>Number of Installments:</strong>
                </TableCell>
                <TableCell>{numberOfInstallments?.value}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </TableContainer>
      </TabPanel>
    </Box>
  );
}
