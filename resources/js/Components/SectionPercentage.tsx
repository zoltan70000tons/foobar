import React, { useEffect } from "react";
import { Box, Typography, alpha } from "@mui/material";
import { green, blue, red } from "@mui/material/colors";
import { InfoRounded } from "@mui/icons-material";
import dayjs from "dayjs";

type Props = {
  passenger: {
    passenger_allocated_cost: number;
    passenger_balance: number;
  };
  booking: {
    payment_plan: string;
  };
  installments: Array<{
    due_date: string;
  }>;
  setIsBookingError: (errorMessage: string) => void; // Error setter as prop
};

type InstallmentPaymentProps = {
  installment: { due_date: string };
  isOverdue: boolean;
  fillPerc: number;
  installmentsLength: number;
  installmentCost: number;
};

const InstallmentPayment: React.FC<InstallmentPaymentProps> = ({
  installment,
  isOverdue,
  fillPerc,
  installmentsLength,
  installmentCost,
}) => {
  useEffect(() => {
    if (isOverdue) {
      console.warn("Payment for booking is overdue");
    }
  }, [isOverdue]);

  return (
    <Box
      sx={{
        width: `${100 / installmentsLength}%`,
        height: "100%",
      }}
    >
      <Box
        component={"span"}
        sx={{
          display: "block",
          position: "relative",
          width: "100%",
          backgroundColor: alpha("#fff", 0.1),
          height: "100%",
          "&:before": {
            content: '""',
            position: "absolute",
            top: 0,
            left: 0,
            height: "100%",
            width: `${fillPerc}%`,
            backgroundColor: isOverdue ? red[500] : green[500],
          },
        }}
      />
      <Box
        sx={{
          display: "flex",
          mt: "5px",
          alignItems: "center",
          justifyContent: "space-between",
          px: 1,
          my: 1,
          backgroundColor: alpha("#fff", 0.1),
          color: isOverdue ? red[500] : "white",
        }}
      >
        <Typography fontSize={"12px"}>{`$${installmentCost.toFixed(2)}`}</Typography>
        <Typography fontSize={"12px"}>
          Due date: {installment.due_date}
        </Typography>
      </Box>
    </Box>
  );
};

type InstallmentsProps = {
  perc: number;
  installments: Array<{ due_date: string }>;
  passengerAllocatedCost: number;
};

const Installments: React.FC<InstallmentsProps> = ({
  perc,
  installments,
  passengerAllocatedCost,
}) => {
  if (installments.length === 0) {
    return <Box>Something went wrong</Box>;
  }

  const installmentsLength = installments.length;
  const installmentCost = passengerAllocatedCost / installmentsLength || 0;

  const installmentFillPercentages: number[] = [];
  const today = dayjs();
  let remainingPercentage = perc;

  installments.forEach(() => {
    if (remainingPercentage >= 100 / installmentsLength) {
      installmentFillPercentages.push(100);
      remainingPercentage -= 100 / installmentsLength;
    } else {
      installmentFillPercentages.push(
        (remainingPercentage / (100 / installmentsLength)) * 100
      );
      remainingPercentage = 0;
    }
  });

  return (
    <Box
      sx={{
        display: "flex",
        alignItems: "center",
        width: "100%",
        height: "6px",
        gap: 1,
      }}
    >
      {installments.map((installment, index) => {
        const fillPerc = installmentFillPercentages[index] || 0;
        const dueDate = dayjs(installment.due_date, "YYYY-MM-DD");
        const isOverdue = fillPerc < 100 && today.isAfter(dueDate);

        return (
          <InstallmentPayment
            key={index}
            installment={installment}
            isOverdue={isOverdue}
            fillPerc={fillPerc}
            installmentsLength={installmentsLength}
            installmentCost={installmentCost}
          />
        );
      })}
    </Box>
  );
};

type FullPaymentProps = {
  perc: number;
};

const FullPayment: React.FC<FullPaymentProps> = ({ perc }) => {
  return (
    <Box
      component={"span"}
      sx={{
        display: "block",
        position: "relative",
        height: "10px",
        backgroundColor: alpha(blue[500], 0.1),
        width: "90%",
        ml: "auto",
        mr: "auto",
      }}
    >
      <Box
        sx={{
          position: "absolute",
          top: 0,
          left: 0,
          height: "100%",
          width: `${perc}%`,
          backgroundColor: green[500],
        }}
      />
    </Box>
  );
};

const SectionPercentage: React.FC<Props> = ({
  passenger,
  booking,
  installments,
  setIsBookingError,
}) => {
  const paymentPlan = booking?.payment_plan;
  const passengerAllocatedCost = Number(passenger?.passenger_allocated_cost);
  const passengerBalance = Number(passenger?.passenger_balance) || 0;

  const passengerPercentage = (passengerBalance / passengerAllocatedCost) * 100;
  const passengerPercentageRounded = Math.round(passengerPercentage);

  return (
    <Box sx={{ width: "100%" }}>
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          width: "100%",
          gap: 2,
          mt: 1,
        }}
      >
        <Typography sx={{ fontSize: "14px" }}>Paid</Typography>
        {paymentPlan === "PAY_IN_FULL" ? (
          <FullPayment perc={passengerPercentage} />
        ) : (
          <Installments
            perc={passengerPercentage}
            passengerAllocatedCost={passengerAllocatedCost}
            installments={installments}
          />
        )}
        <Typography sx={{ fontSize: "14px" }}>
          {`${passengerPercentageRounded}%`}
        </Typography>
      </Box>
      <Typography sx={{ mt: 4, fontSize: "14px" }}>
        <InfoRounded
          sx={{
            fontSize: "1rem",
            color: "white",
            verticalAlign: "middle",
            marginRight: "0.5rem",
            pb: "2px",
          }}
        />
        Please note this information is not updated immediately. It may take up
        to 48 hours for the payment to be reflected.
      </Typography>
    </Box>
  );
};

export default SectionPercentage;
