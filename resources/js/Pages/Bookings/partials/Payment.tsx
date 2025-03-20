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
  TableContainer,
  TableHead,
  Modal,
  Dialog,
  DialogContent,
  IconButton,
  DialogTitle,
  DialogContentText,
  DialogActions,
} from "@mui/material";
import SectionPercentage from "@/Components/SectionPercentage";
import PaymentModal from "./PaymentModal";
import FeesForm from "./FeesForm";
import { LocalizationProvider } from "@mui/x-date-pickers";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { Delete } from "@mui/icons-material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { router } from "@inertiajs/react";
import LoadingOverlay from "@/Components/LoadingOverlay";
import DiscountForm from "./DiscountForm";

const formatCurrency = (value: number) =>
  `${new Intl.NumberFormat("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)} USD`;

const getOrdinalSuffix = (n: number): string => {
  if (n === 1) return "st";
  if (n === 2) return "nd";
  if (n === 3) return "rd";
  return "th";
};

type Payment = {
  id: number;
  amount: number;
  transactionDate: string;
  bipId?: string;
};

type Fee = {
  id: number;
  type: string;
  amount: number;
};

type Installment = {
  perc: number;
  installments: Array<{ due_date: string }>;
  passengerAllocatedCost: number;
};

type Passenger = {
  id: number;
  name: string;
  lead_passenger: boolean;
  passenger_allocated_cost: number;
  passenger_balance: number;
  installments: Installment[];
  payments: Payment[];
  fees: Fee[];
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

type Booking = {
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
  const canDeleteFee = hasPermission(Permissions.DeleteFees);
  const canCreateDiscount = hasPermission(Permissions.CreatePassengerDiscounts);
  const canDeleteDiscount = hasPermission(Permissions.DeletePassengerDiscounts);
  const pricePerPerson = booking.cabin.category.price;
  const { showSnackbar } = useSnackbar();
  const [loading, setLoading] = useState(false);

  // State for modal
  const [open, setModalOpen] = useState(false);
  const [openConfirm, setOpenConfirm] = useState(false);
  const [openConfirmDeleteDiscount, setOpenConfirmDeleteDiscount] = useState(false);
  const [openConfirmPayment, setOpenConfirmPayment] = useState(false);
  const [currentPassenger, setCurrentPassenger] = useState<Passenger | null>(null);
  const [selectedFeeId, setSelectedFeeId] = useState<number | null>(null);
  const [selectedPassengerId, setSelectedPassengerId] = useState<number | null>(null);
  const [selectedPaymentId, setSelectedPaymentId] = useState<number | null>(null);
  const [selectedDiscountId, setSelectedDiscountId] = useState<number | null>(null);

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
      }
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

  const handleOpenModal = (passenger: Passenger) => {
    setCurrentPassenger(passenger); // Set the selected passenger
    setModalOpen(true); // Open the modal
  };
  const handleOpenConfirm = (passengerId: number, feeId: number) => {
    setSelectedPassengerId(passengerId);
    setSelectedFeeId(feeId);
    setOpenConfirm(true);
  };

  const handleOpenConfirmDeleteDiscount = (passengerId: number, discountId: number) => {
    setSelectedPassengerId(passengerId);
    setSelectedDiscountId(discountId);
    setOpenConfirmDeleteDiscount(true);
  };

  const handleCancel = () => {
    setOpenConfirm(false);
    setOpenConfirmDeleteDiscount(false);
    setSelectedPassengerId(null);
    setSelectedFeeId(null);
    setSelectedDiscountId(null);
  };

  const handleOpenConfirmPayment = (paymentId: number) => {
    setSelectedPaymentId(paymentId);
    setOpenConfirmPayment(true);
  };

  const handleCancelPaymentDelete = () => {
    setOpenConfirmPayment(false);
    setSelectedPaymentId(null);
  };

  const handleConfirmPaymentDelete = () => {
    if (selectedPaymentId && currentPassenger) {
      handleDeletePayment(currentPassenger.id, selectedPaymentId);
    }
    handleCancelPaymentDelete();
  };

  const handleConfirm = () => {
    if (selectedPassengerId && selectedFeeId) {
      handleDeleteFee(selectedPassengerId, selectedFeeId);
    }
    handleCancel();
  };

  const handleConfirmDeleteDiscount = () => {
    if (selectedPassengerId && selectedDiscountId) {
      handleDeleteDiscount(selectedPassengerId, selectedDiscountId);
    }
    handleCancel();
  };


  const handleDeleteFee = (passengerId: number, feeId: number) => {
    setLoading(true);

    router.post(
      route("fees.delete", {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      { passenger_id: passengerId, fee_id: feeId },
      {
        onSuccess: () => {
          const updatedPassengers = passengers.map((pax) => { //FIXME
            if (pax.id === passengerId) {
              return {
                ...pax,
                fees: pax.fees.filter((fee) => fee.id !== feeId),
              };
            }
            return pax;
          });
          showSnackbar("Fee deleted successfully.", "success");
        },
        onError: (errors) => {
          console.error("Failed to delete fee:", errors);
          showSnackbar("An error occurred while trying to delete the fee.", "error");
        },
        onFinish: () => {
          setLoading(false);
        }
      },
    );
  };


  const handleDeletePayment = (passengerId: number, paymentId: number) => {
    setLoading(true);

    router.post(
      route("payments.delete", {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      { passenger_id: passengerId, payment_id: paymentId },
      {
        onSuccess: () => {
          showSnackbar("Payment deleted successfully.", "success");
          setCurrentPassenger((prev) => ({
            ...prev!,
            payments: prev!.payments.filter((payment) => payment.id !== paymentId),
          }));
        },
        onError: (errors) => {
          console.error("Failed to delete payment:", errors);
          showSnackbar("An error occurred while trying to delete the payment.", "error");
        },
        onFinish: () => {
          setLoading(false);
        }
      },
    );
  };


  const handleDeleteDiscount = (passengerId: number, discountId: number) => {
    setLoading(true);
    router.post(
      route("delete.discount", {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      { passenger_id: passengerId, discount_id: discountId },
      {
        onSuccess: () => {
          const updatedPassengers = passengers.map((pax) => { //FIXME
            if (pax.id === passengerId) {
              return {
                ...pax,
                fees: pax.discounts.filter((dis) => dis.id !== discountId),
              };
            }
            return pax;
          });
          showSnackbar("Discount deleted successfully.", "success");
        },
        onError: (errors) => {
          console.error("Failed to delete fee:", errors);
          showSnackbar("An error occurred while trying to delete the discount.", "error");
        },
        onFinish: () => {
          setLoading(false);
        }
      },
    );
  };

  const mergeFeesWithPayments = (fees = [], payments = []) => {
    fees = fees || [];
    payments = payments || [];

    const formatDate = (timestamp) => {
      if (!timestamp) return "N/A";
      return timestamp.split("T")[0];
    };

    const mergedFees = fees.map(fee => ({
      BIP_ID: "N/A",
      amount: fee.amount || "N/A",
      created_at: fee.created_at || "N/A",
      id: fee.id || "N/A",
      notes: "N/A",
      passenger_id: fee.passenger_id || "N/A",
      source: "FEE",
      transaction_date: formatDate(fee.created_at),
      type: fee.type || "N/A",
      updated_at: fee.updated_at || "N/A"
    }));

    return [...payments, ...mergedFees];
  };

  const paymentHistory = mergeFeesWithPayments(currentPassenger?.fees, currentPassenger?.payments);

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
        const filteredInstallments = pax.installments.filter(inst => inst.type !== "FEE");


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
              {displayText}
            </Typography>

            <Paper variant="outlined" sx={{ p: 3, backgroundColor: "#1c1c1c", mb: 4 }}>
              {/* SectionPercentage Integration */}
              <SectionPercentage
                passenger={{
                  passenger_allocated_cost: totalCostWihoutFees,
                  passenger_balance: pax.passenger_balance,
                }}
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
                    <TableCell style={{ color: "#4CAF50" }} sx={{ pl: "2rem" }}>
                      Total Discounts:
                    </TableCell>
                    <TableCell align="right">
                      <Box component="span" sx={{ color: "#4CAF50" }}>
                        {totalDiscounts > 0 ? `-${formatCurrency(totalPassengerDiscount)}` : `${formatCurrency(totalPassengerDiscount)}`}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                  {discounts.map((discount, i) => (
                    <TableRow key={`discount-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Discount ({discount.code}):</TableCell>
                      <TableCell align="right">
                        {discount.value > 0 ? `-${formatCurrency(discount.value)}` : `${formatCurrency(discount.value)}`}
                      </TableCell>
                      <TableCell></TableCell>
                    </TableRow>
                  ))}
                  {passengerDiscounts.map((discount, i) => (
                    <TableRow key={`discount-${i}`}>
                      <TableCell sx={{ pl: "3rem" }}>Discount ({discount.code}):</TableCell>
                      <TableCell align="right">
                        {discount.value > 0 ? `-${formatCurrency(discount.value)}` : `${formatCurrency(discount.value)}`}
                      </TableCell>
                      <TableCell align="center" style={{ margin: 0, padding: 0, width: "3%" }}>
                        <IconButton
                          aria-label="delete"
                          color="error"
                          size="small"
                          disabled={!editMode || !canDeleteDiscount}
                          onClick={() => handleOpenConfirmDeleteDiscount(pax.id, discount.id)}
                        >
                          <Delete style={{ fontSize: "1rem" }} />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))}

                  {/*** Official Ticket Price ***/}
                  <TableRow>
                    <TableCell>Net Ticket Price:</TableCell>
                    <TableCell align="right">{formatCurrency(Number(pricePerPerson - totalDiscounts || 0))}</TableCell>
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
                        <IconButton
                          aria-label="delete"
                          color="error"
                          size="small"
                          disabled={!editMode || !canDeleteFee}
                          onClick={() => handleOpenConfirm(pax.id, fee.id)}
                        >
                          <Delete style={{ fontSize: "1rem" }} />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))}

                  {/*** Total Ticket Price ***/}
                  <TableRow>
                    <TableCell>Total Ticket Price:</TableCell>
                    <TableCell align="right">
                      {formatCurrency(Number(pricePerPerson - totalDiscounts + totalAddons + totalFees || 0))}
                    </TableCell>
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
                        {formatCurrency((totalCostAfterAdjustments - pax.passenger_balance))}
                      </Box>
                    </TableCell>
                    <TableCell></TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <Divider sx={{ my: 2, borderColor: "gray" }} />

              <Grid container spacing={3}>
                {canCreatePayment && (
                  <Grid item xs={12} sm={3}>
                    <LocalizationProvider dateAdapter={AdapterDayjs}>
                      <PaymentModal
                        passenger_id={pax.id}
                        booking_id={booking.id}
                        event_id={booking.event_id}
                        editMode={editMode}
                      />
                    </LocalizationProvider>
                  </Grid>
                )}
                {canCreateFee && (
                  <Grid item xs={12} sm={3}>
                    <FeesForm
                      passenger_id={pax.id}
                      booking_id={booking.id}
                      event_id={booking.event_id}
                      editMode={editMode}
                    />
                  </Grid>
                )}
                {canCreateDiscount && (
                  <Grid item xs={12} sm={3}>
                    <DiscountForm
                      passenger_id={pax.id}
                      booking_id={booking.id}
                      event_id={booking.event_id}
                      editMode={editMode}
                    />
                  </Grid>
                )}

                <Grid item xs={12} sm={3}>
                  <Button
                    fullWidth
                    variant="outlined"
                    sx={{ color: "white", borderColor: "gray" }}
                    onClick={() => handleOpenModal(pax)}
                  >
                    Payment history
                  </Button>
                </Grid>
              </Grid>
            </Paper>
            <LoadingOverlay open={loading} />
          </Box>
        );
      })}

      {/* Modal */}
      <Dialog open={open} onClose={() => setModalOpen(false)} fullWidth maxWidth="md">
        <DialogContent>
          <Box>
            <Typography variant="h6" gutterBottom>
              Payment History for {currentPassenger?.full_name || "Unknown Passenger"}
            </Typography>

            {paymentHistory.length ? (
              <TableContainer component={Paper}>
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      <TableCell>Type</TableCell>
                      <TableCell>Amount</TableCell>
                      <TableCell>Transaction Date</TableCell>
                      <TableCell>BIP ID</TableCell>
                      <TableCell>SOURCE</TableCell>
                      <TableCell>Remove</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {paymentHistory.map((payment) => (
                      <TableRow key={payment.id}>
                        <TableCell>{payment.type}</TableCell>
                        <TableCell>
                          {payment.type === "PAYMENT" ? "+" : payment.type === "REFUND" ? "-" : ""}
                          {formatCurrency(payment.amount)}
                        </TableCell>
                        <TableCell>{new Date(payment.transaction_date).toLocaleDateString()}</TableCell>
                        <TableCell>{payment.BIP_ID || "N/A"}</TableCell>
                        <TableCell>{payment.source || "N/A"}</TableCell>
                        <TableCell align="center">
                          {payment.source === "MANUAL" ? (
                            <IconButton
                              aria-label="delete"
                              color="error"
                              size="small"
                              disabled={!editMode || !canDeleteFee}
                              onClick={() => handleOpenConfirmPayment(payment.id)}
                            >
                              <Delete style={{ fontSize: "1rem" }} />
                            </IconButton>
                          ) : (
                            ""
                          )}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            ) : (
              <Typography>No payments found for this passenger.</Typography>
            )}

            <Box mt={2} display="flex" justifyContent="flex-end">
              <Button variant="outlined" color="secondary" onClick={() => setModalOpen(false)}>
                Close
              </Button>
            </Box>
          </Box>
        </DialogContent>
        <LoadingOverlay open={loading} />
      </Dialog>

      <Dialog open={openConfirm} onClose={handleCancel}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>Are you sure you want to remove this fee? This action cannot be undone.</DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancel} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirm} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={openConfirmDeleteDiscount} onClose={handleCancel}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>Are you sure you want to remove this discount? This action cannot be undone.</DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancel} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirmDeleteDiscount} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      {/* Confirm Dialog */}
      <Dialog open={openConfirmPayment} onClose={handleCancelPaymentDelete}>
        <DialogTitle>Confirm Action</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Are you sure you want to delete this payment? This action cannot be undone.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancelPaymentDelete} color="secondary">
            Cancel
          </Button>
          <Button onClick={handleConfirmPaymentDelete} color="error" variant="contained">
            Confirm
          </Button>
        </DialogActions>
      </Dialog>
    </Grid>
  );
};

export default Payment;
