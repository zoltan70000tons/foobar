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

const SplitPaymentModal2: React.FC<SplitPaymentModalProps> = ({
  passengers,
  booking_id,
  event_id,
  editMode,
  open,
  onClose,
}) => {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({});
  const [splitDeposit, setSplitDeposit] = useState({});
  const [amountToDistribute, setAmountToDistribute] = useState(0);


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
        amount: passenger.passenger_balance,
        passengerLeftToPay,
      };
    });

    updatedFormData.totalLeftToPay = totalLeftToPay;
    setFormData(updatedFormData);
  }, [passengers]);


  const { showSnackbar } = useSnackbar();

  const handlePay = (totalAmount) => {
    if (!passengers || passengers.length === 0 || totalAmount <= 0) return;

    if (formData.totalLeftToPay < totalAmount) {
      totalAmount = formData.totalLeftToPay;
    }

    // Step 1: Build a fresh formData and initial payment structure
    let totalLeftToPay = 0;
    const updatedFormData = {totalLeftToPay:0, totalPaymentAdded:0};

    const depositThis = {}

    passengers.forEach((passenger) => {
      const passengerLeftToPay = passenger.passenger_allocated_cost - passenger.passenger_balance;
      totalLeftToPay += passengerLeftToPay;
      updatedFormData[`passenger_${passenger.id}`] = {
        passengerId: passenger.id,
        passengerName: passenger.full_name,
        passengerAllocatedCost: passenger.passenger_allocated_cost,
        passengerBalance: passenger.passenger_balance,
        amount: passenger.passenger_balance,
        passengerLeftToPay,
      };
      depositThis[`passenger_${passenger.id}`] = 0;
    });

    updatedFormData.totalLeftToPay = totalLeftToPay;

    let maxIteration = 50;
    // Step 2: Distribute the amount fairly
    while (true) {
      const payToEach = totalAmount / passengers.length;

      passengers.forEach((passenger) => {
        const passengerLeftToPay = updatedFormData[`passenger_${passenger.id}`].passengerLeftToPay;
        const amount = updatedFormData[`passenger_${passenger.id}`].amount;

        const payToThis = Math.min(payToEach, passengerLeftToPay)

        updatedFormData[`passenger_${passenger.id}`] = {
          ...updatedFormData[`passenger_${passenger.id}`],
          amount: parseFloat(String(amount)) + parseFloat(String(payToThis)),
          passengerLeftToPay: parseFloat(String(passengerLeftToPay)) - parseFloat(String(payToThis)),
        };

        depositThis[`passenger_${passenger.id}`] += parseFloat(String(payToThis));

        totalAmount -= payToThis
        totalLeftToPay -= payToThis
      });

      maxIteration--;


      if (maxIteration < 1) {
        break;
        return;
      }

      if (totalAmount < 1  || totalLeftToPay < 1) {
        break;
        return;
      }
    }

    updatedFormData.totalLeftToPay = totalLeftToPay

    setSplitDeposit(depositThis);
    setFormData(updatedFormData);
  };


  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (formData === {}) {
      showSnackbar("Something went wrong.", "error");
      return;
    }

    let correctedFormData = formData;

    passengers.forEach((pax) => {
      const passengerId = pax.id;

      correctedFormData[`passenger_${passengerId}`].amount = splitDeposit[`passenger_${passengerId}`];
    });

    setLoading(true);
    router.post(
      route("manual.split-payment", { event_id, booking_id }),
      {
        formData: correctedFormData
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
            <Grid item container justifyContent="space-between" alignItems="center" >
              <TextField
                label="Amount to Pay"
                name="total_pay"
                type="number"
                onChange={(e) => setAmountToDistribute(Number(e.target.value))}
                inputProps={{ step: 0.01, min: 0 }}
                size={"medium"}
              />
              <Button variant="outlined" size={"large"} onClick={() => handlePay(amountToDistribute)}>
                Apply Split
              </Button>
            </Grid>
            <Grid container spacing={2} mt={1} ml={0}>
              {passengers.length && passengers.map((pax) => {
                //const amountLeftToPay = formData[`passenger_${pax.id}`]?.passengerLeftToPay;
                const fullName = formData[`passenger_${pax.id}`]?.passengerName ?? 'Error';
                const amount = formData[`passenger_${pax.id}`]?.amount ?? 0;

                return (
                  <Grid container spacing={2} mt={1}>
                    <Grid item xs={12} md={6}>
                      <TextField
                        label="Full Name"
                        name="full_name"
                        disabled
                        fullWidth
                        value={fullName}
                      />
                    </Grid>
                    <Grid item xs={6} md={3}>
                      <TextField
                        label="Amount"
                        name="amount"
                        type="number"
                        value={amount}
                        disabled
                        fullWidth
                        inputProps={{ step: 0.01, min: 0 }}
                      />
                    </Grid>
                  </Grid>
                );
              })}
              <Box mt={2}>
                Total left to pay: {formData?.totalLeftToPay}
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

export default SplitPaymentModal2;
