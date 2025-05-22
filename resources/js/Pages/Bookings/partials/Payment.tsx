import React, { useState } from "react";
import {
  Box,
  Typography,
  Divider,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableRow,
  Button,
  Grid,
  Avatar,
} from "@mui/material";
import SectionPercentage from "@/Components/SectionPercentage";
import PaymentModal from "./PaymentModal";
import FeesForm, { Fee } from "./FeesForm";
import { LocalizationProvider } from "@mui/x-date-pickers";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { Payment as PaymentIcon } from "@mui/icons-material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { router } from "@inertiajs/react";
import DiscountForm, { Discount } from "./DiscountForm";
import OnboardCreditForm from "./OnboardCreditForm";

import { formatCurrency } from "@/Helpers/stringUtils";
import PaymentTransferForm, { PaymentTransfer } from "@/Pages/Bookings/partials/PaymentTransferForm";

const getOrdinalSuffix = (n: number): string => {
  if (n === 1) return "st";
  if (n === 2) return "nd";
  if (n === 3) return "rd";
  return "th";
};

type MergedTransfer = {
  payment_id_from: number;
  payment_id_to: number;
}

type Payment = {
  id: number;
  amount: number;
  transactionDate: string;
  bipId?: string;
  type: string;
  mergedTransfers: MergedTransfer[];
  payment_transfer_from?: PaymentTransfer;
  payment_transfer_to?: PaymentTransfer;
  created_at: string;
};

type Installment = {
  //perc: number;
  //installments: Array<{ due_date: string }>;
  //passengerAllocatedCost: number;
  due_date: string;
  type: "PAYMENT" | "FEE";
  id: number;
  passenger_id: number;
};

type InstallmentItem = {
  installment_id: number;
  type: "PAYMENT" | "FEE";
  amount?: number;
  amount_due?: number;
  due_date: string;
};

type InstallmentStatus = {
  paid_installments: InstallmentItem[];
  remaining_installments: InstallmentItem[];
  next_installment?: InstallmentItem;
  fully_paid: boolean;
};

type OnboardCredit = {
  id: number;
  reason: string;
  amount: number;
  passenger_id: number;
  created_at: string;
}

export type Passenger = {
  id: number;
  name: string;
  lead_passenger: boolean;
  passenger_allocated_cost: string;
  passenger_balance: string;
  installments: Installment[];
  payments: Payment[];
  fees: Fee[];
  installment_status: InstallmentStatus;
  onboard_credits: OnboardCredit[];
  full_name?: string;
  discounts: Discount[];
  first_name?: string;
  last_name?: string;
};

type Adjustment = {
  id: number;
  code: string;
  type: "DISCOUNT" | "ADDON";
  operation: "FIXED" | "PERCENTAGE";
  value: string;
};

type Cabin = {
  category: {
    price: number;
  };
};

export type Booking = {
  id: number;
  passengers: Passenger[];
  payment_plan: string;
  adjustments: Adjustment[];
  event_id: number;
  cabin: Cabin;
};

const Payment = ({ booking, editMode }: { booking: Booking; editMode: boolean }) => {
  const passengers = booking.passengers;
  const totalPassengers = passengers.length;
  const { hasPermission } = usePermissions();
  const canCreateFee = hasPermission(Permissions.CreateFees);
  const canCreatePayment = hasPermission(Permissions.CreatePayments);
  const canCreateDiscount = hasPermission(Permissions.CreatePassengerDiscounts);
  const canCreateOnboardCredit = hasPermission(Permissions.CreatePassengerOnboardCredit);
  const pricePerPerson = booking.cabin.category.price;
  const { showSnackbar } = useSnackbar();
  const [currentPassenger, setCurrentPassenger] = useState<Passenger | null>(null);
  const [selectedFeeId, setSelectedFeeId] = useState<number | null>(null);
  const [selectedPassengerId, setSelectedPassengerId] = useState<number | null>(null);
  const [selectedPaymentId, setSelectedPaymentId] = useState<number | null>(null);
  const [selectedDiscountId, setSelectedDiscountId] = useState<number | null>(null);

  // Payment Model States
  const [openPaymentModal, setOpenPaymentModal] = useState(false);
  const handleOpenAddPaymentModal = (passenger: Passenger) => {
    setCurrentPassenger(passenger);
    setOpenPaymentModal(true);
  };

  const calculateAdjustments = (pricePerPerson: number) => {
    const grouped = booking.adjustments.reduce(
      (acc, adj) => {
        let adjustmentValue = parseFloat(adj.value);

        if (adj.operation === "PERCENTAGE") {
          adjustmentValue = (adjustmentValue / 100) * pricePerPerson;
        }

        if (adj.type === "DISCOUNT") {
          acc.discounts.push({ code: adj.code, value: adjustmentValue });
          acc.totalDiscounts += adjustmentValue;
        } else if (adj.type === "ADDON") {
          acc.addons.push({ code: adj.code, value: adjustmentValue });
          acc.totalAddons += adjustmentValue;
        }

        return acc;
      },
      {
        discounts: [] as { code: string; value: number }[],
        addons: [] as { code: string; value: number }[],
        totalDiscounts: 0,
        totalAddons: 0,
      },
    );
    return grouped;
  };

  const calculateDiscounts = (passenger, pricePerPerson) => {
    if (!passenger || !Array.isArray(passenger.discounts)) {
      return { passengerDiscounts: [], totalPassengerDiscounts: 0 };
    }

    const grouped = passenger.discounts.reduce(
      (acc, dis) => {
        let discountValue = parseFloat(dis.amount);
        if (dis.operation === "PERCENTAGE") {
          discountValue = (discountValue / 100) * pricePerPerson;
        }

        acc.passengerDiscounts.push({ code: dis.type, value: discountValue, id: dis.id });
        acc.totalPassengerDiscounts += discountValue;

        return acc;
      },
      {
        passengerDiscounts: [],
        totalPassengerDiscounts: 0,
      },
    );

    return grouped;
  };

  const getSummaryAllocatedCost = (booking: Booking): number => {
    return booking.passengers.reduce((sum, passenger) => sum + Number(passenger.passenger_allocated_cost || 0), 0);
  };
  const getSummaryBalance = (booking: Booking): number => {
    return booking.passengers.reduce((sum, passenger) => sum + Number(passenger.passenger_balance || 0), 0);
  };

  const summaryAllocatedCost = getSummaryAllocatedCost(booking);
  const summaryBalance = getSummaryBalance(booking);
  const summaryToPay = Number(summaryAllocatedCost) - Number(summaryBalance);

  const formatDate = (timestamp) => {
    if (!timestamp) return "N/A";
    return timestamp.split("T")[0];
  };

  const paymentHistory = currentPassenger?.payments;

  return (
    <Grid>
      <Typography variant="h5" mb={2} sx={{ textAlign: "center" }}>
        Payment Summary
      </Typography>
      <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c", mb: 4 }}>
        <Box>
          <Table size="small" sx={{ mt: 2, color: "white" }}>
            <TableBody>
              <TableRow>
                <TableCell>Grand Total Booking Price:</TableCell>
                <TableCell align="right">{formatCurrency(summaryAllocatedCost)}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell>Total Balance:</TableCell>
                <TableCell align="right">{formatCurrency(summaryBalance)}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell>Remaining balance:</TableCell>
                <TableCell align="right">{formatCurrency(summaryToPay)}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </Box>
      </Paper>
      {passengers.map((pax, index) => {
        const displayText = pax.lead_passenger
          ? "Lead Passenger"
          : `${index + 1}${getOrdinalSuffix(index + 1)} Passenger`;

        const { discounts, addons, totalDiscounts, totalAddons } = calculateAdjustments(pricePerPerson);
        const { passengerDiscounts, totalPassengerDiscounts } = calculateDiscounts(pax, pricePerPerson);
        const totalPassengerDiscount = totalDiscounts + totalPassengerDiscounts;
        const totalFees = pax.fees.reduce((acc, fee) => acc + Number(fee.amount || 0), 0);
        const totalCostAfterAdjustments = pricePerPerson - totalPassengerDiscount + totalAddons + totalFees;
        const totalCostWihoutFees = totalCostAfterAdjustments - totalFees;
        const filteredInstallments = pax.installments;

        return (
          <Box key={index}>
            <Box
              display="flex"
              sx={{
                textAlign: "center",
                width: "100%",
                justifyContent: "center",
              }}
            >
              <Divider
                orientation="vertical"
                variant="middle"
                flexItem
                sx={{
                  height: "100px",
                  "&::before, &::after": {
                    borderColor: "secondary.light",
                    border: "1px dashed",
                  },
                }}
              >
                <Avatar
                  sx={{
                    background: "#20a22d",
                    color: "#fff",
                    fontSize: "0.9rem",
                  }}
                >
                  {index + 1}/{totalPassengers}
                </Avatar>
              </Divider>
            </Box>

            <Typography variant="h5" mb={2} sx={{ textAlign: 'center' }}>
              {displayText} { pax?.first_name ? `- ${pax?.first_name} ${pax?.last_name}` : null }
            </Typography>

            <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c", mb: 4 }}>
              {/* SectionPercentage Integration */}
              <SectionPercentage
                passenger={pax}
                booking={booking}
                installments={filteredInstallments}
                setIsBookingError={(error) => console.error("Booking Error:", error)}
              />

              {/* Payment Details */}
              <Table size="small" sx={{ mt: 2, color: "white" }}>
                <TableBody>
                  {/*** Official Ticket Price ***/}
                  <TableRow>
                    <TableCell>Official Ticket Price:</TableCell>
                    <TableCell align="right">{formatCurrency(Number(pricePerPerson || 0))}</TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Discounts ***/}
                  <TableRow>
                    <TableCell sx={{ pl: "2rem", color: "#4CAF50" }}>Total Discounts:</TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#4CAF50" }}>
                        {totalDiscounts > 0
                          ? `-${formatCurrency(totalPassengerDiscount)}`
                          : `${formatCurrency(totalPassengerDiscount)}`}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {discounts.map((discount, i) => (
                    <TableRow key={`discount-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Discount ({discount.code}):</TableCell>
                      <TableCell align="right">
                        {discount.value > 0
                          ? `-${formatCurrency(discount.value)}`
                          : `${formatCurrency(discount.value)}`}
                      </TableCell>
                      <TableCell></TableCell>
                    </TableRow>
                  ))}
                  {passengerDiscounts.map((discount, i) => (
                    <TableRow key={`discount-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Discount ({discount.code}):</TableCell>
                      <TableCell align="right">
                        {discount.value > 0
                          ? `-${formatCurrency(discount.value)}`
                          : `${formatCurrency(discount.value)}`}
                      </TableCell>
                      <TableCell align="center" style={{ margin: 0, padding: 0, width: "3%" }}>
                      </TableCell>
                    </TableRow>
                  ))}

                  {/*** Official Ticket Price ***/}
                  <TableRow>
                    <TableCell>Net Ticket Price:</TableCell>
                    <TableCell align="right">
                      {formatCurrency(Number(pricePerPerson - totalDiscounts - totalPassengerDiscounts || 0))}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Addons ***/}
                  <TableRow>
                    <TableCell style={{ color: "#FF9800" }} sx={{ pl: "2rem" }}>
                      Total Addons:
                    </TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#FF9800" }}>
                        {totalAddons > 0 ? `+${formatCurrency(totalAddons)}` : `${formatCurrency(totalAddons)}`}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {addons.map((addon, i) => (
                    <TableRow key={`addon-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Addon ({addon.code}):</TableCell>
                      <TableCell align="right">+{formatCurrency(addon.value)}</TableCell>
                      <TableCell></TableCell>
                    </TableRow>
                  ))}

                  {/*** Fees ***/}
                  <TableRow>
                    <TableCell style={{ fontWeight: "400", color: "#FFC107" }} sx={{ pl: "2rem" }}>
                      Total Fees:
                    </TableCell>
                    <TableCell align="right" style={{ fontWeight: "400", color: "#FFC107" }}>
                      {totalFees > 0 ? `+${formatCurrency(totalFees)}` : `${formatCurrency(totalFees)}`}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {pax.fees.map((fee, i) => (
                    <TableRow key={`fee-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Fee ({fee.type}):</TableCell>
                      <TableCell align="right">
                        <Box component="span">
                          {Number(fee.amount) > 0
                            ? `+${formatCurrency(Number(fee.amount))}`
                            : `${formatCurrency(Number(fee.amount))}`}
                        </Box>
                      </TableCell>
                      <TableCell align="center" style={{ margin: 0, padding: 0, width: "3%" }}>
                      </TableCell>
                    </TableRow>
                  ))}

                  {/*** Total Ticket Price ***/}
                  <TableRow>
                    <TableCell>Total Ticket Price:</TableCell>
                    <TableCell align="right">{formatCurrency(Number(totalCostAfterAdjustments || 0))}</TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Total Paid ***/}
                  <TableRow>
                    <TableCell sx={{ pl: "2rem", color: "#4CAF50" }}>Paid</TableCell>
                    <TableCell align="right">{formatCurrency(Number(pax.passenger_balance || 0))}</TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Outstanding Balance:</TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#2196F3", fontWeight: "600", fontSize: "1.2rem" }}>
                        {formatCurrency(totalCostAfterAdjustments - pax.passenger_balance)}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>Next Payment</TableCell>
                    <TableCell align="right" sx={!pax.installment_status.next_installment ? { color: '#4CAF50' } : {}}>
                      {pax.installment_status.fully_paid ? 'Paid' : formatDate(pax.installment_status?.next_installment?.due_date)}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <Divider sx={{ my: 2, borderColor: "gray" }} />

              <Grid container spacing={3}>
                {canCreatePayment && (
                  <Grid item xs={12} sm={3}>
                    <Button
                      fullWidth
                      variant="outlined"
                      sx={{ color: "white", borderColor: "gray" }}
                      onClick={() => handleOpenAddPaymentModal(pax)}
                      startIcon={<PaymentIcon />}
                      disabled={!editMode}
                    >
                      Payments/Refunds
                    </Button>
                  </Grid>
                )}
                {canCreateFee && (
                  <Grid item xs={12} sm={3}>
                    <FeesForm
                      passenger={pax}
                      booking_id={booking.id}
                      event_id={booking.event_id}
                      editMode={editMode}
                    />
                  </Grid>
                )}
                {canCreateDiscount && (
                  <Grid item xs={12} sm={3}>
                    <DiscountForm
                      passenger={pax}
                      booking_id={booking.id}
                      event_id={booking.event_id}
                      editMode={editMode}
                    />
                  </Grid>
                )}
                {canCreateOnboardCredit && (
                  <Grid item xs={12} sm={3}>
                    <OnboardCreditForm
                      passenger={pax}
                      booking_id={booking.id}
                      event_id={booking.event_id}
                      editMode={editMode}
                    />
                  </Grid>
                )}
                {canCreatePayment && (
                  <Grid item xs={12} sm={3}>
                    <PaymentTransferForm
                      passenger={pax}
                      booking={booking}
                      editMode={editMode}
                    />
                  </Grid>
                )}
              </Grid>
            </Paper>
            {/* <LoadingOverlay open={loading} /> */}
          </Box>
        );
      })}


      <LocalizationProvider dateAdapter={AdapterDayjs}>
        <PaymentModal
          passenger={currentPassenger}
          booking_id={booking.id}
          event_id={booking.event_id}
          editMode={editMode}
          paymentHistory={paymentHistory || []}
          open={openPaymentModal}
          onClose={() => setOpenPaymentModal(false)}
        />
      </LocalizationProvider>
    </Grid>
  );
};

export default Payment;
