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
  Tooltip,
  IconButton,
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import InfoIcon from "@mui/icons-material/Info";
import SectionPercentage from "@/Components/SectionPercentage";
import PaymentModal from "./PaymentModal";
import FeesForm, { Fee } from "./FeesForm";
import { LocalizationProvider } from "@mui/x-date-pickers";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { Payment as PaymentIcon } from "@mui/icons-material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import DiscountForm, { Discount } from "./DiscountForm";
import OnboardCreditForm from "./OnboardCreditForm";

import { formatCurrency } from "@/Helpers/stringUtils";
import PaymentTransferForm, { PaymentTransfer } from "@/Pages/Bookings/partials/PaymentTransferForm";
import SplitPaymentModal from "@/Pages/Bookings/partials/SplitPaymentModal";
import SplitPaymentModal2 from "@/Pages/Bookings/partials/SplitPaymentModal2";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import ExpandLessIcon from "@mui/icons-material/ExpandLess";
import dayjs from "dayjs";

const getOrdinalSuffix = (n: number): string => {
  if (n === 1) return "st";
  if (n === 2) return "nd";
  if (n === 3) return "rd";
  return "th";
};

type MergedTransfer = {
  payment_id_from: number;
  payment_id_to: number;
};

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
};

export type Passenger = {
  id: number;
  name: string;
  lead_passenger: boolean;
  survivor_number: string | null;
  dob: Date;
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
  passenger_order: number;
  special_request: string;
  special_options: string;
  dietary_preferences: string;
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

export type DeletedPayments = {
  id: number;
  BIP_ID: string;
  amount: string;
  passenger_id: number;
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
  const [deletedPayments, setDeletedPayments] = useState<DeletedPayments[]>(null);
  const [showDiscountsMap, setShowDiscountsMap] = useState<Record<number, boolean>>({});
  const [showTaxesAndFeesMap, setShowTaxesAndFeesMap] = useState<Record<number, boolean>>({});
  const [showAditionalFeesMap, setShowAdditionalFeesMap] = useState<Record<number, boolean>>({});

  const toggleShowDiscounts = (passengerId: number) => {
    setShowDiscountsMap((prev) => ({ ...prev, [passengerId]: !prev[passengerId] }));
  };
  const toggleShowTaxesAndFees = (passengerId: number) => {
    setShowTaxesAndFeesMap((prev) => ({ ...prev, [passengerId]: !prev[passengerId] }));
  };
  const toggleShowAdditionalFees = (passengerId: number) => {
    setShowAdditionalFeesMap((prev) => ({ ...prev, [passengerId]: !prev[passengerId] }));
  };

  // Payment Model States
  const [openPaymentModal, setOpenPaymentModal] = useState(false);
  const handleOpenAddPaymentModal = (passenger: Passenger) => {
    setCurrentPassenger(passenger);
    setOpenPaymentModal(true);
  };

  const [openSplitPaymentModal, setOpenSplitPaymentModal] = useState(false);
  const [openSplitPaymentModal2, setOpenSplitPaymentModal2] = useState(false);

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

        acc.passengerDiscounts.push({ code: dis.type, value: discountValue, id: dis.id, notes: dis.notes });
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

  const handleOpenSplitPaymentModal = () => {
    setOpenSplitPaymentModal(true);
  };

  const handleSplitPaymentDeleted = () => {
    handleOpenSplitPaymentModal();
    setOpenPaymentModal(false);
  };

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
        <Box sx={{ gap: 2, display: "flex" }}>
          <Button
            variant="outlined"
            color="secondary"
            startIcon={<AddIcon />}
            onClick={handleOpenSplitPaymentModal}
            sx={{ mt: 2 }}
            disabled={!editMode || !canCreatePayment}
          >
            Add Split Payment
          </Button>
          <Button
            variant="outlined"
            color="secondary"
            startIcon={<AddIcon />}
            onClick={() => setOpenSplitPaymentModal2(true)}
            sx={{ mt: 2 }}
            disabled={!editMode || !canCreatePayment}
          >
            Add Auto Split Payment
          </Button>
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

            <Typography variant="h5" mb={2} sx={{ textAlign: "center" }}>
              {displayText} {pax?.first_name ? `- ${pax?.first_name} ${pax?.last_name}` : null}
            </Typography>

            <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c", mb: 4 }}>
              {/* SectionPercentage Integration */}
              <SectionPercentage
                passenger={pax}
                booking={booking}
                installments={filteredInstallments}
                setIsBookingError={(error) => console.error("Booking Error:", error)}
                editMode={editMode}
              />

              {/* Payment Details */}
              <Table
                size="small"
                sx={{
                  mt: 2,
                  color: "white",
                  "& td, & th": {
                    border: 0,
                  },
                  "& .MuiTableCell-root": {
                    padding: 0.5,
                    borderBottom: "none",
                  },
                }}
              >
                <TableBody>
                  {/*** Official Ticket Price ***/}
                  <TableRow sx={{ borderTop: "4px solid #A97E1E" }}>
                    <TableCell>OFFICIAL TICKET PRICE</TableCell>
                    <TableCell align="right" sx={{ fontWeight: "500" }}>
                      {formatCurrency(Number(pricePerPerson || 0))}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Discounts ***/}
                  <TableRow>
                    <TableCell sx={{ color: "#4CAF50" }}>
                      TOTAL DISCOUNTS{" "}
                      <IconButton
                        size="small"
                        onClick={() => toggleShowDiscounts(pax.id)}
                        aria-label={`toggle-discounts-${pax.id}`}
                        sx={{ p: 0, m: 0 }}
                      >
                        {showDiscountsMap[pax.id] ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                      </IconButton>
                    </TableCell>

                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#4CAF50", fontWeight: "500" }}>
                        {totalPassengerDiscount > 0
                          ? `-${formatCurrency(totalPassengerDiscount)}`
                          : `${formatCurrency(totalPassengerDiscount)}`}
                      </Box>
                    </TableCell>
                  </TableRow>
                  {showDiscountsMap[pax.id] && (
                    <>
                      {discounts.map((discount, i) => (
                        <TableRow key={`global-discount-${pax.id}-${i}`}>
                          <TableCell sx={{ ml: "3rem" }}>
                            <Box sx={{ ml: 6, color: "grey", py: 1 }}>
                              Discount ({discount.code}){" "}
                              <b>
                                {discount.value > 0
                                  ? `-${formatCurrency(discount.value)}`
                                  : formatCurrency(discount.value)}
                              </b>
                              {discount.notes && (
                                <Tooltip title={discount.notes}>
                                  <IconButton>
                                    <InfoIcon fontSize="small" />
                                  </IconButton>
                                </Tooltip>
                              )}
                            </Box>
                          </TableCell>
                          <TableCell align="right"></TableCell>
                          <TableCell />
                        </TableRow>
                      ))}
                      {passengerDiscounts.map((discount, i) => (
                        <TableRow key={`passenger-discount-${pax.id}-${i}`}>
                          <TableCell>
                            <Box sx={{ ml: 6, color: "grey" }}>
                              {discount.code}{" "}
                              <b>
                                {discount.value > 0
                                  ? `-${formatCurrency(discount.value)}`
                                  : formatCurrency(discount.value)}
                              </b>
                              {discount.notes && (
                                <Tooltip title={discount.notes}>
                                  <IconButton>
                                    <InfoIcon fontSize="small" />
                                  </IconButton>
                                </Tooltip>
                              )}
                            </Box>
                          </TableCell>
                          <TableCell align="right"></TableCell>
                          <TableCell />
                        </TableRow>
                      ))}
                    </>
                  )}

                  {/*** Official Ticket Price ***/}
                  <TableRow>
                    <TableCell>NET TICKET PRICE</TableCell>
                    <TableCell align="right" sx={{ fontWeight: "500" }}>
                      {formatCurrency(Number(pricePerPerson - totalDiscounts - totalPassengerDiscounts || 0))}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Addons ***/}
                  <TableRow sx={{ borderTop: "2px solid #A97E1E" }}>
                    <TableCell>TAXES & FEES</TableCell>
                    <TableCell align="right"></TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell>
                      TOTAL ADD-ONS{" "}
                      <IconButton
                        size="small"
                        onClick={() => toggleShowTaxesAndFees(pax.id)}
                        aria-label={`toggle-taxes-and-fees-${pax.id}`}
                        sx={{ p: 0, m: 0 }}
                      >
                        {showTaxesAndFeesMap[pax.id] ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                      </IconButton>
                    </TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#FF9800", fontWeight: "500" }}>
                        {totalAddons > 0 ? `+${formatCurrency(totalAddons)}` : `${formatCurrency(totalAddons)}`}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {showTaxesAndFeesMap[pax.id] && (
                    <>
                      {/* addons */}
                      {Array.isArray(addons) &&
                        addons.map((addon, aIdx) => (
                          <TableRow key={`addon-${pax.id}-${aIdx}`}>
                            <TableCell sx={{ pl: "3rem" }}>
                              <Box sx={{ ml: 6, color: "gray", py: 1 }}>
                                Addon ({addon.code}) <b>+{formatCurrency(addon.value)}</b>
                              </Box>
                            </TableCell>
                            <TableCell align="right"></TableCell>
                            <TableCell></TableCell>
                          </TableRow>
                        ))}
                    </>
                  )}

                  {/*** Fees ***/}
                  <TableRow sx={{ borderTop: "2px solid #A97E1E" }}>
                    <TableCell>
                      TOTAL ADDITIONAL FEES{" "}
                      <IconButton
                        size="small"
                        onClick={() => toggleShowAdditionalFees(pax.id)}
                        aria-label={`toggle-additional-fees-${pax.id}`}
                        sx={{ p: 0, m: 0 }}
                      >
                        {showAditionalFeesMap[pax.id] ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                      </IconButton>
                    </TableCell>
                    <TableCell align="right" style={{ fontWeight: "500", color: "#FFC107" }}>
                      {totalFees > 0 ? `+${formatCurrency(totalFees)}` : `${formatCurrency(totalFees)}`}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {showAditionalFeesMap[pax.id] && (
                    <>
                      {Array.isArray(pax.fees) &&
                        pax.fees.map((fee, i) => (
                          <TableRow key={`fee-${pax.id}-${i}`}>
                            <TableCell sx={{ pl: "3rem" }}>
                              <Box sx={{ ml: 6, color: "gray" }}>
                                Fee ({fee.type}){" "}
                                <b>
                                  {Number(fee.amount) > 0
                                    ? `+${formatCurrency(Number(fee.amount))}`
                                    : formatCurrency(Number(fee.amount))}
                                </b>
                                <Tooltip title={fee.notes || ""}>
                                  <IconButton aria-label={`fee-info-${pax.id}-${i}`}>
                                    <InfoIcon fontSize="small" />
                                  </IconButton>
                                </Tooltip>
                              </Box>
                            </TableCell>

                            <TableCell align="right">
                              <Box component="span"></Box>
                            </TableCell>

                            <TableCell align="center" sx={{ m: 0, p: 0, width: "3%" }} />
                          </TableRow>
                        ))}
                    </>
                  )}
                  {/*** Total Ticket Price ***/}
                  <TableRow sx={{ borderTop: "2px solid #A97E1E" }}>
                    <TableCell>TOTAL TICKET PRICE:</TableCell>
                    <TableCell align="right" sx={{ fontWeight: "500" }}>
                      {formatCurrency(Number(totalCostAfterAdjustments || 0))}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>

                  {/*** Total Paid ***/}
                  <TableRow>
                    <TableCell sx={{ color: "#C6FE6D" }}>TOTAL PAID</TableCell>
                    <TableCell align="right" sx={{ color: "#C6FE6D", fontWeight: "500" }}>
                      {formatCurrency(Number(pax.passenger_balance || 0))}
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  <TableRow>
                    <TableCell sx={{ fontSize: "0.95rem" }}>OUTSTANDING BALANCE:</TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ fontWeight: "500", fontSize: "0.95rem" }}>
                        {formatCurrency(totalCostAfterAdjustments - pax.passenger_balance)}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  <TableRow sx={{ borderTop: "2px solid #A97E1E" }}>
                    <TableCell sx={{ color: "#b89a18" }}>
                      NEXT PAYMENT DUE:{" "}
                      <b>
                        {pax.installment_status.fully_paid ? (
                          <span style={{ color: "#4CAF50" }}>PAID</span>
                        ) : pax.installment_status?.next_installment?.due_date &&
                          dayjs(pax.installment_status.next_installment.due_date).isBefore(dayjs(), "day") ? (
                          <span style={{ color: "#FF5252" }}>
                            {dayjs(pax.installment_status.next_installment.due_date)
                              .format("MMM DD, YYYY")
                              .toUpperCase()}{" "}
                            (OVERDUE)
                          </span>
                        ) : pax.installment_status?.next_installment?.due_date ? (
                          dayjs(pax.installment_status.next_installment.due_date).format("MMM DD, YYYY").toUpperCase()
                        ) : null}
                      </b>
                    </TableCell>

                    <TableCell
                      align="right"
                      sx={!pax.installment_status.next_installment ? { color: "#4CAF50" } : {}}
                    ></TableCell>

                    <TableCell></TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <br></br>
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
                    <FeesForm passenger={pax} booking_id={booking.id} event_id={booking.event_id} editMode={editMode} />
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
                {canCreatePayment && passengers.length > 1 && (
                  <Grid item xs={12} sm={3}>
                    <PaymentTransferForm passenger={pax} booking={booking} editMode={editMode} />
                  </Grid>
                )}
              </Grid>
            </Paper>
            {/* <LoadingOverlay open={loading} /> */}
          </Box>
        );
      })}

      <LocalizationProvider dateAdapter={AdapterDayjs}>
        <SplitPaymentModal
          passengers={passengers}
          booking_id={booking.id}
          event_id={booking.event_id}
          editMode={editMode}
          open={openSplitPaymentModal}
          onClose={() => setOpenSplitPaymentModal(false)}
          deletedPayments={deletedPayments}
        />

        <SplitPaymentModal2
          passengers={passengers}
          booking_id={booking.id}
          event_id={booking.event_id}
          editMode={editMode}
          open={openSplitPaymentModal2}
          onClose={() => setOpenSplitPaymentModal2(false)}
        />

        <PaymentModal
          passenger={currentPassenger}
          booking_id={booking.id}
          event_id={booking.event_id}
          editMode={editMode}
          paymentHistory={paymentHistory || []}
          open={openPaymentModal}
          onClose={() => setOpenPaymentModal(false)}
          handleSplitPaymentDeleted={handleSplitPaymentDeleted}
          setDeletedPayments={setDeletedPayments}
        />
      </LocalizationProvider>
    </Grid>
  );
};

export default Payment;
