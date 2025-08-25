import React, { useEffect, useState } from "react";
import {
  TextField,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  Divider, Box,
} from "@mui/material";
import { router } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

type SplitPaymentModalProps = {
  passengers: any;
  booking_id: number;
  event_id: number;
  editMode: boolean;
  open: boolean;
  onClose: () => void;
};

type SplitPayment = {
  amount: number;
  type: "PAYMENT";
};

enum PaymentType {
  PAYMENT = "Payment",
}

const SplitPaymentModal: React.FC<SplitPaymentModalProps> = ({
  passengers,
  booking_id,
  event_id,
  editMode,
  open,
  onClose,
}) => {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({});

  useEffect(() => {
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
  }, [passengers]);


  const { showSnackbar } = useSnackbar();

  const handleChange = (passengerId, newAmount) => {
    setFormData((prevData) => {
      const key = `passenger_${passengerId}`;

      // Get the correct passenger from props
      const passenger = passengers.find((p) => p.id === passengerId);
      if (!passenger) return prevData;

      const maxAmount = passenger.passenger_allocated_cost - passenger.passenger_balance;

      // Clamp the amount
      const clampedAmount = Math.min(Math.max(newAmount, 0), maxAmount);

      // Update passenger
      const updatedPassenger = {
        ...prevData[key],
        amount: clampedAmount
      };

      // Create updated formData
      const newFormData = {
        ...prevData,
        [key]: updatedPassenger
      };

      // Recalculate totals
      let totalLeftToPay = 0;
      let totalPaymentAdded = 0;

      passengers.forEach((passenger) => {
        const passengerKey = `passenger_${passenger.id}`;
        const leftToPay = passenger.passenger_allocated_cost - passenger.passenger_balance;
        const enteredAmount = newFormData[passengerKey]?.amount || 0;

        totalLeftToPay += (leftToPay - enteredAmount);
        totalPaymentAdded += enteredAmount;
      });

      newFormData.totalLeftToPay = +totalLeftToPay.toFixed(2);
      newFormData.totalPaymentAdded = +totalPaymentAdded.toFixed(2);

      return newFormData;
    });
  };



  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (formData === {}) {
      showSnackbar("Something went wrong.", "error");
      return;
    }

    setLoading(true);
    router.post(
      route("manual.split-payment", { event_id, booking_id }),
      {
        formData
      },
      {
        onSuccess: () => {
          showSnackbar(`All payments were created successfully`, "success");
          setFormData({totalLeftToPay:0, totalPaymentAdded:0});
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
            <Grid container spacing={2} mt={1}>
              {passengers.length && passengers.map((pax) => {
                const amountLeftToPay = formData[`passenger_${pax.id}`]?.passengerLeftToPay;
                const fullName = formData[`passenger_${pax.id}`]?.passengerName ?? 'Error';
                const amount = formData[`passenger_${pax.id}`]?.amount ?? 0;

                return (
                  <Grid container spacing={2} mt={1}>
                    <Grid item xs={5} md={5}>
                      <TextField
                        label="Full Name"
                        name="full_name"
                        disabled
                        value={fullName}
                        fullWidth
                      />
                    </Grid>
                    <Grid item xs={3} md={3}>
                      <TextField
                        label="Amount"
                        name="amount"
                        type="number"
                        value={amount}
                        onChange={(e) => handleChange(pax.id, Number(e.target.value))}
                        inputProps={{ step: 0.01, min: 0, max: amountLeftToPay }}
                        fullWidth
                      />
                    </Grid>
                    <Grid item xs={2} md={2}>
                      Cost: {pax.passenger_allocated_cost}
                    </Grid>
                    <Grid item xs={2} md={2}>
                      Balance: {pax.passenger_balance}
                    </Grid>
                  </Grid>
                );
              })}
              <Box mt={2}>
                <Box>
                  Total left to pay: {formData?.totalLeftToPay}
                </Box>
                <Box>
                  Total payment added: {formData?.totalPaymentAdded}
                </Box>
              </Box>
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
