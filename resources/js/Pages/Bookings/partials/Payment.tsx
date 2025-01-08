import React from "react";
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
    IconButton,
    Avatar,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import SectionPercentage from "@/Components/SectionPercentage";

const getOrdinalSuffix = (n: number): string => {
    if (n === 1) return "st";
    if (n === 2) return "nd";
    if (n === 3) return "rd";
    return "th";
};

type Passenger = {
    lead_passenger: boolean;
    passenger_allocated_cost: number;
    passenger_balance: number;
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
};

const Payment = ({ booking, editMode }: { booking: Booking; editMode: boolean }) => {
    const passengers = booking.passengers;
    const totalPassengers = passengers.length;
    const pricePerPassenger = booking?.cabin?.category?.price;
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

    return (
        <Grid>
            {passengers.map((pax, index) => {
                const displayText = pax.lead_passenger
                    ? "Lead Passenger"
                    : `${index + 1}${getOrdinalSuffix(index + 1)} Passenger`;

                const allocatedCost = pax.passenger_allocated_cost;
                const { discounts, addons, totalDiscounts, totalAddons } =
                    calculateAdjustments(allocatedCost);

                const totalCostAfterAdjustments = pricePerPassenger - totalDiscounts + totalAddons;

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
                                booking={{ payment_plan: booking.payment_plan }}
                                installments={[
                                    { due_date: "2025-01-10" },
                                    { due_date: "2025-02-10" },
                                    { due_date: "2025-03-10" },
                                ]}
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
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Paid:</TableCell>
                                        <TableCell align="right" >
                                            ${Number(pax.passenger_balance || 0).toFixed(2)}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Balance:</TableCell>
                                        <TableCell align="right">
                                            ${(
                                                totalCostAfterAdjustments - Number(pax.passenger_balance || 0)
                                            ).toFixed(2)}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Price Per Passenger:</TableCell>
                                        <TableCell align="right">
                                            ${Number(pricePerPassenger || 0).toFixed(2)}
                                        </TableCell>
                                    </TableRow>

                                    {/* List Discounts */}
                                    {discounts.map((discount, i) => (
                                        <TableRow key={`discount-${i}`}>
                                            <TableCell>Discount ({discount.code}):</TableCell>
                                            <TableCell align="right">
                                                -${discount.value.toFixed(2)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell style={{color:"#4CAF50" }}>Total Discounts:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#4CAF50" }}>
                                                -${totalDiscounts.toFixed(2)}
                                            </Box>
                                        </TableCell>
                                    </TableRow>


                                    {/* List Addons */}
                                    {addons.map((addon, i) => (
                                        <TableRow key={`addon-${i}`}>
                                            <TableCell>Addon ({addon.code}):</TableCell>
                                            <TableCell align="right">
                                                +${addon.value.toFixed(2)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow>
                                        <TableCell style={{color:"#FF9800" }}>Total Addons:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#FF9800" }}>
                                                +${totalAddons.toFixed(2)}
                                            </Box>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>Amount to Pay:</TableCell>
                                        <TableCell align="right">
                                            <Box component="span" sx={{ color: "#2196F3" }}>
                                                ${totalCostAfterAdjustments.toFixed(2)}
                                            </Box>
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
            })}
        </Grid>
    );
};

export default Payment;
