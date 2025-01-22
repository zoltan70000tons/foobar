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
import { LocalizationProvider } from '@mui/x-date-pickers';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import { GridDeleteIcon } from "@mui/x-data-grid";
import { Delete } from "@mui/icons-material";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { router } from '@inertiajs/react'

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

type Passenger = {
    id: number;
    name: string;
    lead_passenger: boolean;
    passenger_allocated_cost: number;
    passenger_balance: number;
    payments: Payment[];
};

type Adjustment = {
    id: number;
    code: string;
    type: "DISCOUNT" | "ADDON";
    operation: "FIXED" | "PERCENTAGE";
    value: string;
};

type Booking = {
    passengers: Passenger[];
    payment_plan: string;
    adjustments: Adjustment[];
    event_id: number;
    cabin: object;
};

const Payment = ({ booking, editMode }: { booking: Booking; editMode: boolean }) => {
    const passengers = booking.passengers;
    const totalPassengers = passengers.length;
    const { hasPermission } = usePermissions();
    const canCreateFee = hasPermission(Permissions.CreateFees);
    const canCreatePayment = hasPermission(Permissions.CreatePayments);
    const canDeleteFee = hasPermission(Permissions.DeleteFees);
    const pricePerPerson = booking.cabin.category.price;
    const { showSnackbar } = useSnackbar();

    // State for modal
    const [open, setModalOpen] = useState(false);
    const [openConfirm, setOpenConfirm] = useState(false);
    const [openConfirmPayment, setOpenConfirmPayment] = useState(false);
    const [currentPassenger, setCurrentPassenger] = useState<Passenger | null>(null);
    const [selectedFeeId, setSelectedFeeId] = useState<number | null>(null);
    const [selectedPassengerId, setSelectedPassengerId] = useState<number | null>(null);
    const [selectedPaymentId, setSelectedPaymentId] = useState<number | null>(null);

    const calculateAdjustments = (allocatedCost: number) => {
        const grouped = booking.adjustments.reduce(
            (acc, adj) => {
                let adjustmentValue = parseFloat(adj.value);

                if (adj.operation === "PERCENTAGE") {
                    adjustmentValue = (adjustmentValue / 100) * allocatedCost;
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
            }
        );
        return grouped;
    };

    const handleOpenModal = (passenger: Passenger) => {
        setCurrentPassenger(passenger); // Set the selected passenger
        setModalOpen(true); // Open the modal
    };
    const handleOpenConfirm = (passengerId: number, feeId: number) => {
        setSelectedPassengerId(passengerId);
        setSelectedFeeId(feeId);
        setOpenConfirm(true);
    };
    const handleCancel = () => {
        setOpenConfirm(false);
        setSelectedPassengerId(null);
        setSelectedFeeId(null);
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



    const handleDeleteFee = (passengerId: number, feeId: number) => {
        router.post(
            route("fees.delete", { event_id: booking.event_id, booking_id: booking.id }),
            { passenger_id: passengerId, fee_id: feeId },
            {
                onSuccess: () => {
                    const updatedPassengers = passengers.map((pax) => {
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
            }
        );
    };

    const handleDeletePayment = (passengerId: number, paymentId: number) => {
        router.post(
            route("payments.delete", { event_id: booking.event_id, booking_id: booking.id }),
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
            }
        );
    };

    return (
        <Grid>
            {passengers.map((pax, index) => {
                const displayText = pax.lead_passenger
                    ? "Lead Passenger"
                    : `${index + 1}${getOrdinalSuffix(index + 1)} Passenger`;

                const allocatedCost = pax.passenger_allocated_cost;
                const { discounts, addons, totalDiscounts, totalAddons } =
                    calculateAdjustments(allocatedCost);

                const totalCostAfterAdjustments = allocatedCost - totalDiscounts + totalAddons;
                const totalFees = pax.fees.reduce((acc, fee) => acc + Number(fee.amount || 0), 0);

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

                        <Paper
                            variant="outlined"
                            sx={{ p: 3, backgroundColor: "#1c1c1c", mb: 4 }}
                        >
                            {/* SectionPercentage Integration */}
                            <SectionPercentage
                                passenger={{
                                    passenger_allocated_cost: totalCostAfterAdjustments,
                                    passenger_balance: pax.passenger_balance,
                                }}
                                booking={booking}
                                installments={pax.installments}
                                setIsBookingError={(error) => console.error("Booking Error:", error)}
                            />

                            {/* Payment Details */}
                            <Table size="small" sx={{ mt: 2, color: "white" }}>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>Payment type:</TableCell>
                                        <TableCell align="right">
                                            {booking.payment_plan === "PAY_IN_FULL"
                                                ? "Full Payment"
                                                : "Installments"}
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Cabin Price Per Person:</TableCell>
                                        <TableCell align="right" >
                                            ${Number(pricePerPerson || 0).toFixed(2)}
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    {pax.fees.map((fee, i) => (
                                        <TableRow key={`fee-${i}`}>
                                            <TableCell>Fee ({fee.type}):</TableCell>
                                            <TableCell align="right">
                                                {Number(fee.amount) > 0
                                                    ? `+$${Number(fee.amount).toFixed(2)}`
                                                    : `$${Number(fee.amount).toFixed(2)}`}
                                            </TableCell>
                                            <TableCell align="center" style={{ margin: 0, padding: 0, width: "3%" }}>
                                                <IconButton
                                                    aria-label="delete"
                                                    color="error"
                                                    size="small"
                                                    disabled={!editMode || !canDeleteFee}
                                                    onClick={() => handleOpenConfirm(pax.id, fee.id)}
                                                >
                                                    <Delete style={{ fontSizeL: '1rem' }} />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell style={{ fontWeight: "400" }}>Total Fees:</TableCell>
                                        <TableCell align="right" style={{ fontWeight: "400" }}>
                                            {totalFees > 0 ? `+$${totalFees.toFixed(2)}` : `$${totalFees.toFixed(2)}`}
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    {addons.map((addon, i) => (
                                        <TableRow key={`addon-${i}`}>
                                            <TableCell>Addon ({addon.code}):</TableCell>
                                            <TableCell align="right">
                                                +${addon.value.toFixed(2)}
                                            </TableCell>
                                            <TableCell></TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell style={{ color: "#FF9800" }}>Total Addons:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#FF9800" }}>
                                                {totalAddons > 0 ? `+$${totalAddons.toFixed(2)}` : `$${totalAddons.toFixed(2)}`}
                                            </Box>
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    {discounts.map((discount, i) => (
                                        <TableRow key={`discount-${i}`}>
                                            <TableCell>Discount ({discount.code}):</TableCell>
                                            <TableCell align="right">
                                                {discount.value > 0 ? `-$${discount.value.toFixed(2)}` : `$${discount.value.toFixed(2)}`}
                                            </TableCell>
                                            <TableCell></TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell style={{ color: "#4CAF50" }}>Total Discounts:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#4CAF50" }}>
                                                {totalDiscounts > 0 ? `-$${totalDiscounts.toFixed(2)}` : `$${totalDiscounts.toFixed(2)}`}
                                            </Box>
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Amount to Pay:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#2196F3" }}>
                                                ${totalCostAfterAdjustments.toFixed(2)}
                                            </Box>
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Paid:</TableCell>
                                        <TableCell align="right" >
                                            ${Number(pax.passenger_balance || 0).toFixed(2)}
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Outstanding balance:</TableCell>
                                        <TableCell align="right">
                                            ${(
                                                totalCostAfterAdjustments - Number(pax.passenger_balance || 0)
                                            ).toFixed(2)}
                                        </TableCell>
                                        <TableCell></TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <Divider sx={{ my: 2, borderColor: "gray" }} />

                            <Grid container spacing={2}>
                                {canCreatePayment && (<Grid item xs={12} sm={4}>
                                    <LocalizationProvider dateAdapter={AdapterDayjs}>
                                        <PaymentModal passenger_id={pax.id} booking_id={booking.id} event_id={booking.event_id} />
                                    </LocalizationProvider>
                                </Grid>)}
                                {canCreateFee && (<Grid item xs={12} sm={4}>
                                    <FeesForm passenger_id={pax.id} booking_id={booking.id} event_id={booking.event_id} />
                                </Grid>)}
                                <Grid item xs={12} sm={4}>
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
                    </Box >
                );
            })}

            {/* Modal */}
            <Dialog
                open={open}
                onClose={() => setModalOpen(false)}
                fullWidth
                maxWidth="md" // Define un tamaño máximo para el diálogo
            >
                <DialogContent>
                    <Box
                    >
                        <Typography variant="h6" gutterBottom>
                            Payment History for {currentPassenger?.full_name || "Unknown Passenger"}
                        </Typography>

                        {currentPassenger?.payments?.length ? (
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
                                        {currentPassenger.payments.map((payment) => (
                                            <TableRow key={payment.id}>
                                                <TableCell>{payment.type}</TableCell>
                                                <TableCell>
                                                    {payment.type === "PAYMENT"
                                                        ? "+"
                                                        : payment.type === "REFOUND"
                                                            ? "-"
                                                            : ""}
                                                    {payment.amount}
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(payment.transaction_date).toLocaleDateString()}
                                                </TableCell>
                                                <TableCell>{payment.BIP_ID || "N/A"}</TableCell>
                                                <TableCell>{payment.source || "N/A"}</TableCell>
                                                <TableCell align="center">{payment.source === 'MANUAL' ? <IconButton
                                                    aria-label="delete"
                                                    color="error"
                                                    size="small"
                                                    disabled={!editMode || !canDeleteFee}
                                                    onClick={() => handleOpenConfirmPayment(payment.id)}
                                                >
                                                    <Delete style={{ fontSize: '1rem' }} />
                                                </IconButton> : ''}</TableCell>
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
            </Dialog>

            <Dialog open={openConfirm} onClose={handleCancel}>
                <DialogTitle>Confirm Action</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                        Are you sure you want to remove this fee? This action cannot be undone.
                    </DialogContentText>
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
        </Grid >
    );
};

export default Payment;
