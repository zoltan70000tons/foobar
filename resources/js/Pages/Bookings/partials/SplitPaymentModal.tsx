import React, { useEffect, useState } from "react";
import {
  TextField,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  Box,
  Divider, IconButton, Tooltip,
} from "@mui/material";
import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { InfoOutlined } from "@mui/icons-material";
import { DeletedPayments } from "@/Pages/Bookings/partials/Payment";

type SplitPaymentModalProps = {
  passengers: any;
  booking_id: number;
  event_id: number;
  editMode: boolean;
  open: boolean;
  onClose: () => void;
  deletedPayments: DeletedPayments[];
};

type SplitPayment = {
  amount: number;
  type: "PAYMENT";
};

enum PaymentType {
  PAYMENT = "Payment",
}

export type PassengerFormEntry = {
  passengerId: number;
  passengerName: string;
  passengerAllocatedCost: string;
  passengerBalance: string;
  passengerLeftToPay: number;
  amount: number | string;
  parsedAmount?: number;
}

export type FormData = {
  [key: `passenger_${number}`]: PassengerFormEntry;
  totalLeftToPay: number;
  totalPaymentAdded: number;
}

const SplitPaymentModal: React.FC<SplitPaymentModalProps> = ({
  passengers,
  booking_id,
  event_id,
  editMode,
  open,
  onClose,
  deletedPayments,
}) => {
  const [loading, setLoading] = useState<boolean>(false);
  const [formData, setFormData] = useState<FormData>({
    totalLeftToPay: 0,
    totalPaymentAdded: 0,
  });

  const [transactionId, setTransactionId] = useState<string>("");

  /*useEffect(() => {
    if (!passengers || passengers.length === 0) return;

    let totalLeftToPay = 0;
    const updatedFormData = {totalLeftToPay:0, totalPaymentAdded:0};

    passengers.forEach((passenger) => {
      const passengerLeftToPay = passenger.passenger_allocated_cost - passenger.passenger_balance;
      totalLeftToPay += passengerLeftToPay;
      updatedFormData[`passenger_${passenger.id}`] = {
        passengerId: passenger.id,
        passengerName: passenger.full_name,
        passengerAllocatedCost: passenger.passenger_allocated_cost,
        passengerBalance: passenger.passenger_balance,
        amount: 0,
        passengerLeftToPay,
      };
    });

    updatedFormData.totalLeftToPay = totalLeftToPay;
    setFormData(updatedFormData);
  }, [passengers]);*/

  useEffect(() => {
    if (!passengers || passengers.length === 0) return;

    let totalLeftToPay = 0;
    let totalPaymentAdded = 0;

    const updatedFormData: FormData = {
      totalLeftToPay: 0,
      totalPaymentAdded: 0,
    };

    passengers.forEach((passenger) => {
      const passengerLeftToPay =
        passenger.passenger_allocated_cost - passenger.passenger_balance;

      // Base entry
      const entry = {
        passengerId: passenger.id,
        passengerName: passenger.full_name,
        passengerAllocatedCost: passenger.passenger_allocated_cost,
        passengerBalance: passenger.passenger_balance,
        amount: 0,
        passengerLeftToPay,
      };

      // 🔎 if we have deleted payments, adjust the amount
      if (deletedPayments && deletedPayments.length > 0) {
        const deleted = deletedPayments.find(
          (p) => p.passenger_id === passenger.id
        );
        if (deleted) {
          entry.amount = parseFloat(deleted.amount) || 0;
        }
      }

      totalLeftToPay += passengerLeftToPay - (entry.amount || 0);
      totalPaymentAdded += entry.amount || 0;

      updatedFormData[`passenger_${passenger.id}`] = entry;
    });

    updatedFormData.totalLeftToPay = +totalLeftToPay.toFixed(2);
    updatedFormData.totalPaymentAdded = +totalPaymentAdded.toFixed(2);

    setFormData(updatedFormData);
  }, [passengers, deletedPayments]);


  const { showSnackbar } = useSnackbar();

  const handleChange = (passengerId: number, rawValue: string) => {
    setFormData((prevData: FormData): FormData => {
      const key = `passenger_${passengerId}`;
      const passenger = passengers.find((p) => p.id === passengerId);
      if (!passenger) return prevData;

      const maxAmount =
        passenger.passenger_allocated_cost - passenger.passenger_balance;

      // Allow only digits, commas, dots
      const cleaned = rawValue.replace(/[^0-9.,]/g, "");

      // Parse to number
      const numericValue = parseFloat(cleaned.replace(/,/g, "")) || 0;

      // Clamp value
      const cappedValue = Math.min(Math.max(numericValue, 0), maxAmount);

      const updatedPassenger = {
        ...prevData[key],
        amount: cleaned,          // use cleaned string, so letters never appear
        parsedAmount: cappedValue // numeric representation
      };

      return { ...prevData, [key]: updatedPassenger };
    });
  };

  const usNumberFormatter = new Intl.NumberFormat("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  const handleBlur = (passengerId: number) => {
    setFormData((prevData: FormData): FormData => {
      const key = `passenger_${passengerId}`;
      const passenger = passengers.find((p) => p.id === passengerId);
      if (!passenger) return prevData;

      const maxAmount =
        passenger.passenger_allocated_cost - passenger.passenger_balance;

      // Strip commas before parsing
      let numericValue = parseFloat(
        (prevData[key]?.amount || "").replace(/,/g, "")
      ) || 0;

      // Clamp
      if (numericValue < 0) numericValue = 0;
      if (numericValue > maxAmount) numericValue = maxAmount;

      const updatedPassenger = {
        ...prevData[key],
        amount: usNumberFormatter.format(numericValue) // e.g. 1,234.23
      };

      // Build updated formData
      const newFormData = { ...prevData, [key]: updatedPassenger };

      // Recalculate totals
      let totalLeftToPay = 0;
      let totalPaymentAdded = 0;

      passengers.forEach((p) => {
        const passengerKey = `passenger_${p.id}`;
        const leftToPay = p.passenger_allocated_cost - p.passenger_balance;

        const enteredAmount = parseFloat(
          (newFormData[passengerKey]?.amount || "").replace(/,/g, "")
        ) || 0;

        totalLeftToPay += leftToPay - enteredAmount;
        totalPaymentAdded += enteredAmount;
      });

      newFormData.totalLeftToPay = +totalLeftToPay.toFixed(2);
      newFormData.totalPaymentAdded = +totalPaymentAdded;

      return newFormData;
    });
  };

  const sanitizeFormData = (data) => {
    const sanitized = {};

    Object.entries(data).forEach(([key, value]) => {
      if (key.startsWith("passenger_") && value?.amount !== undefined) {
        sanitized[key] = {
          ...value,
          amount: Number(
            String(value.amount).replace(/,/g, "")
          ),
          //same with balance and cost when needed
        };
      } else {
        sanitized[key] = value;
      }
    });

    return sanitized;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    const sanitizedFormData = sanitizeFormData(formData);

    if (sanitizedFormData === {}) {
      showSnackbar("Something went wrong.", "error");
      return;
    }

    if (formData.totalPaymentAdded === 0) {
      showSnackbar("No payment was added.", "warning");
      return;
    }

    setLoading(true);
    router.post(
      route("manual.split-payment", { event_id, booking_id }),
      {
        sanitizedFormData,
        transactionId,
      },
      {
        onSuccess: () => {
          showSnackbar(`All payments were created successfully`, "success");
          setFormData({ totalLeftToPay: 0, totalPaymentAdded: 0 });
          onClose();
        },
        onError: (err) => {
          console.error(err);
          showSnackbar(`Failed to save the payments.`, "error");
        },
        onFinish: () => {
          setLoading(false);
        },
      },
    );
  };

  return (
    <>
      <Dialog open={open} onClose={onClose} fullWidth maxWidth="md" >
        <DialogTitle>Split Payment</DialogTitle>
        <DialogContent>
          <form onSubmit={handleSubmit}>
            <Grid container spacing={2} mt={1} rowGap={2}>
              {passengers.length && passengers.map((pax, idx) => {
                const fullName = formData[`passenger_${pax.id}`]?.passengerName ?? 'Error';
                const amount = formData[`passenger_${pax.id}`]?.amount ?? 0;

                const index = idx + 1;

                console.log(parseFloat(pax.passenger_allocated_cost) <= parseFloat(pax.passenger_balance), pax.passenger_allocated_cost, pax.passenger_balance)

                return (
                  <Grid container spacing={2} mt={1} ml={2} justifyContent={"flex-start"} alignItems={"center"} gap={2}>
                    <Grid xs={4} md={4}>
                      <TextField
                        label={index === 1 ? 'Lead Passenger' : `Passenger #${index}`}
                        name="full_name"
                        disabled
                        value={fullName}
                        fullWidth
                      />
                    </Grid>
                    <Grid xs={3} md={3}>
                      <TextField
                        label="Amount"
                        name="amount"
                        type="text"
                        value={amount}
                        onChange={(e) => handleChange(pax.id, e.target.value)}
                        onBlur={() => handleBlur(pax.id)}
                        fullWidth
                        disabled={parseFloat(pax.passenger_allocated_cost) <= parseFloat(pax.passenger_balance)}
                      />
                    </Grid>
                    <Grid xs={2} md={2}>
                      <TextField
                        label="Cost"
                        name="cost"
                        type="text"
                        value={pax.passenger_allocated_cost}
                        fullWidth
                        disabled
                      />
                    </Grid>
                    <Grid xs={2} md={2}>
                      <TextField
                        label="Balance"
                        name="balance"
                        type="text"
                        value={pax.passenger_balance}
                        fullWidth
                        disabled
                      />
                    </Grid>
                  </Grid>
                );
              })}
              <Grid xs={4} md={4} ml={2} display={"flex"}>
                <TextField
                  label="Transaction identifier"
                  name="transaction_id"
                  type="text"
                  value={transactionId}
                  onChange={(e) => setTransactionId(e.target.value)}
                  fullWidth
                />
                <Tooltip title="If not provided, we will autogenerate one for you">
                  <IconButton>
                    <InfoOutlined />
                  </IconButton>
                </Tooltip>
              </Grid>
              <Divider sx={{ my: 3, ml: 2, width: "calc(100% - 16px)" }} />
              <Grid mt={0} ml={2} width={"100%"}>
                <Grid display={"flex"} width={"100%"}>
                  <Grid xs={3} sx={{fontWeight: 700}} mb={1}>
                    Total left to pay:
                  </Grid>
                  <Grid>
                    {formData?.totalLeftToPay?.toFixed(2)}
                  </Grid>
                </Grid>
                <Grid display={"flex"} width={"100%"}>
                  <Grid xs={3} sx={{fontWeight: 700}}>
                    Total payment added:
                  </Grid>
                  <Grid>
                    {formData?.totalPaymentAdded?.toFixed(2)}
                  </Grid>
                </Grid>
              </Grid>
            </Grid>

            <DialogActions>
              <Button onClick={onClose} color="secondary">
                Cancel
              </Button>
              <Button type="submit" color="primary" variant="contained" disabled={loading}>
                Save
              </Button>
            </DialogActions>
          </form>
        </DialogContent>
      </Dialog>
    </>
  );
};

export default SplitPaymentModal;
