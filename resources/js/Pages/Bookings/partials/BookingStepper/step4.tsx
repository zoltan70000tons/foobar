import { formatCurrency } from "@/Helpers/stringUtils";
import React, { useEffect } from "react";
import {
  Box,
  CircularProgress,
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

export type PriceCalc = {
  extras: number;
  save: string;
  total: number;
  totalPassenger: number;
};

const TabPanel = ({ children, value, index }) => {
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
    carbonOffset,
    youChooseYourCabin,
    passenger,
    loading,
    tabValue,
    priceCalc
  } = useAppSelector(selectBooking);

  const priceCalcPayload = React.useMemo(() => {
    if (!cabinType || !cabinCategory || !paymentPlan) return null;

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

    dispatch(fetchBookingFinalPrice(priceCalcPayload));
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

      {/* Tab Panel for Cabin Details */}
      {loading && (
        <CircularProgress
          color="inherit"
          size={40}
          style={{ position: "absolute", inset: "50%", marginLeft: "-20px" }}
        />
      )}
      {!loading && (
        <TabPanel value={tabValue} index={0}>
          {cabinType && cabinCategory ? (
            <TableContainer component={Paper} elevation={3}>
              <Table size="small">
                <TableBody>
                  <TableRow>
                    <TableCell>
                      <strong>Type:</strong>
                    </TableCell>
                    <TableCell>{cabinType.cabin_type}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>
                      <strong>Category:</strong>
                    </TableCell>
                    <TableCell>{cabinCategory.title}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>
                      <strong>Cabin Number:</strong>
                    </TableCell>
                    <TableCell>{cabinNumber}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>
                      <strong>Capacity:</strong>
                    </TableCell>
                    <TableCell>{cabinCategory.spec.capacity}</TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>
                      <strong>Price Per Person Before Calculations:</strong>
                    </TableCell>
                    <TableCell>{formatCurrency(cabinCategory.price)}</TableCell>
                  </TableRow>
                  {priceCalc?.totalPassenger && (
                    <TableRow>
                      <TableCell>
                        <strong>Price per Person with Tax after Discount and Add-Ons:</strong>
                      </TableCell>
                      <TableCell>{formatCurrency(priceCalc.totalPassenger)}</TableCell>
                    </TableRow>
                  )}
                  {priceCalc?.total && (
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
      )}

      {/* Tab Panel for Lead Passenger */}
      <TabPanel value={tabValue} index={1}>
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
      </TabPanel>
      <TabPanel value={tabValue} index={2}>
        <TableContainer component={Paper} elevation={3}>
          <Table size="small">
            <TableBody>
              <TableRow>
                <TableCell>
                  <strong>Payment Plan:</strong>
                </TableCell>
                <TableCell>{paymentPlan.value}</TableCell>
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
