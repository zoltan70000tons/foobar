import { useEffect } from "react";
import { Box, Typography, alpha } from "@mui/material";
import { green, blue, red } from "@mui/material/colors";
import { InfoRounded } from "@mui/icons-material";
import dayjs from "dayjs";
import FeeInstallmentList from "@/Components/FeeInstallmentList";
import { getOrdinalName } from "@/Helpers/stringUtils";
import type { InstallmentItem, InstallmentStatus, Fee, Installment } from "@/types/payments"; // Adjust the import path as needed

// Helpers
import { formatDate, formatCurrency } from "@/Helpers/stringUtils";
import { Passenger } from "@/interfaces/Passenger";
import { Booking } from "@/types/booking";


// Define types
type Props = {
  passenger: Passenger;
  booking: Booking;
  installments: Installment[];
};

type InstallmentPaymentProps = {
  order: number;
  installment: Installment;
  status: "paid" | "unpaid";
  isOverdue: boolean;
  fillPerc: number;
  installmentsLength: number;
  installmentCost: number;
  perc: number;
};

// Installment payment part
const InstallmentPayment = ({
  order,
  installment,
  status,
  isOverdue,
  fillPerc,
  installmentsLength,
  installmentCost,
  perc,
}: InstallmentPaymentProps) => {

  return (
    <Box
      sx={{
        width: { xs: "100%", md: `${100 / installmentsLength}%` },
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
          height: "6px",
          "&:before": {
            content: '""',
            position: "absolute",
            top: 0,
            left: 0,
            height: "100%",
            width: `${fillPerc}%`,
            backgroundColor: isOverdue ? red[500] : green[500], // Use red if overdue
          },
        }}
      />
      <Box
        sx={{
          display: "flex",
          mt: "5px",
          alignItems: "flex-start",
          flexDirection: "column",
          justifyContent: "space-between",
          textTransform: "capitalize",
          p: 1,
          my: 1,
          backgroundColor:
            status === "paid" ? alpha(green[500], 0.2) : isOverdue ? alpha(red[500], 0.2) : alpha("#fff", 0.1),
          color: status === "paid" ? green[500] : isOverdue ? red[500] : "inherit",
        }}
      >
        <Typography fontSize="12px">
          {status === "paid" ? `${formatCurrency(installment?.amount)}` : `${formatCurrency(installment?.amount_due)}`}
        </Typography>

        {status === "paid" ? (
          <Typography fontSize="12px">Paid</Typography>
        ) : isOverdue ? (
          <Typography fontSize={"12px"}>Due Immediately</Typography>
        ) : (
          <Typography fontSize={"12px"}>Due Date: {formatDate(installment?.due_date)}</Typography>
        )}
        
        <Typography fontSize="12px" fontWeight="bold">
          {getOrdinalName(order + 1)} Installment
        </Typography>
      </Box>
    </Box>
  );
};

// Installments
const Installments = ({
  perc,
  installments,
  passengerAllocatedCost,
  installment_plan,
}: {
  perc: number;
  installments: Installment[];
  passengerAllocatedCost: number;
  installment_plan: InstallmentStatus;
}) => {
  if (!installments || installments.length === 0) {
    return <Box>Something went wrong</Box>;
  }

  const installmentsLength = installments.length;

  // Total cost for each installment
  const installmentCost = passengerAllocatedCost / installmentsLength || 0;

  const installmentFillPercentages: number[] = [];
  const today = dayjs(); // Today's date
  // Distribute the remaining percentage across installments
  let remainingPercentage = perc; // Start with the paid percentage

  installments.forEach(() => {
    if (remainingPercentage >= 100 / installmentsLength) {
      installmentFillPercentages.push(100); // Fully paid
      remainingPercentage -= 100 / installmentsLength;
    } else {
      installmentFillPercentages.push((remainingPercentage / (100 / installmentsLength)) * 100); // Partially paid
      remainingPercentage = 0; // No remaining percentage
    }
  });

  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: { xs: "column", md: "row" },
        alignItems: "center",
        width: "100%",
        gap: 1,
      }}
    >
      {installments.map((installment: Installment, index: number) => {
        const fillPerc = installmentFillPercentages[index] || 0;
        const dueDate = dayjs(installment?.due_date, "YYYY-MM-DD").add(2, "day");

        // Get status of the installment based on which array it belongs to
        const id = installment.id;
        let status: "paid" | "unpaid" = "unpaid";

        if (installment_plan?.paid_installments?.some((i) => i.installment_id === id)) {
          status = "paid";
        } else {
          status = "unpaid";
        }

        // Get matching installment Item from installment status
        const installmentEntry =
          installment_plan?.paid_installments?.find((i: InstallmentItem) => i.installment_id === id) ??
          installment_plan?.remaining_installments?.find((i: InstallmentItem) => i.installment_id === id) ??
          null;

        const isOverdue = fillPerc < 100 && today.isAfter(dueDate);

        return (
          <InstallmentPayment
            key={index}
            order={index}
            installment={installmentEntry}
            status={status}
            isOverdue={isOverdue}
            fillPerc={fillPerc}
            installmentsLength={installmentsLength}
            installmentCost={installmentCost}
            perc={perc}
          />
        );
      })}
    </Box>
  );
};

// Section Percentage
export default function SectionPercentage({ passenger, booking, installments }: Props) {
  const paymentInstallments = installments
    .filter((inst: Installment) => inst.type === "PAYMENT")
    .sort((a: Installment, b: Installment) => new Date(a.due_date).getTime() - new Date(b.due_date).getTime());
  const feeInstallments = installments
    .filter((inst: Installment) => inst.type === "FEE")
    .sort((a: Installment, b: Installment) => new Date(a.due_date).getTime() - new Date(b.due_date).getTime());

  // Get the total fees and calculate total fees
  const passengerFees = (passenger?.fees as Fee[]) || [];
  const totalFees = passengerFees.reduce((acc: number, fee: Fee) => acc + Number(fee.amount || 0), 0);

  // Get the installment status
  const installment_status = passenger?.installment_status;

  // Get the paid installments and calculate the total paid fees
  const paidFeeIds = installment_status?.paid_installments?.filter((i: InstallmentItem) => i.type === "FEE") ?? [];
  const passengerPaidFees = paidFeeIds.reduce((acc: number, fee: InstallmentItem) => acc + Number(fee.amount || 0), 0);

  const passengerAllocatedCost = Number(passenger?.passenger_allocated_cost - totalFees); // We do not want to include fees to not affect installments. This are counted separately
  const passengerBalance = Number(passenger?.passenger_balance) - Number(passengerPaidFees) || 0;

  // Calculate percentage of total payment that has been paid
  const passengerPercentage = (passengerBalance / passengerAllocatedCost) * 100;

  const passengerPercentageRounded = Math.min(100, Math.round(passengerPercentage));

  // Debugging logs - DO NOT REMOVE
  console.log(
    "passengerid",
    passenger?.id,
    "installmentStatus",
    installment_status,
    "balanceMinusPaidFees",
    passengerBalance,
    "allocatedcost",
    passengerAllocatedCost,
    "passengerPercentage",
    passengerPercentageRounded,
  );

  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        width: "100%",
        overflow: "hidden",
      }}
    >
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
        <Installments
          perc={passengerPercentage}
          passengerAllocatedCost={passengerAllocatedCost}
          installments={paymentInstallments}
          installment_plan={installment_status}
        />

        <Typography
          sx={{
            display: { xs: "none", md: "block" },
            fontSize: "14px",
          }}
        >
          {`${passengerPercentageRounded}%`}
        </Typography>
      </Box>

      {passengerFees.length > 0 && (
        <Box mt={3}>
          <Typography fontSize="14px" fontWeight="bold" mb={1}>
            Additional Fees
          </Typography>
          <FeeInstallmentList
            fees={passengerFees}
            feeInstallments={feeInstallments}
            installmentPlan={installment_status}
          />
        </Box>
      )}

      <Box
        sx={{
          mt: 2,
        }}
      >
        <Typography
          sx={{
            fontSize: "14px",
          }}
        >
          <InfoRounded
            sx={{
              fontSize: "1rem",
              color: "white",
              verticalAlign: "middle",
              marginRight: "0.5rem",
              pb: "2px",
            }}
          />
          Please note this information is not updated immediately. It may take up to 48 hours for the payment to be
          reflected.
        </Typography>
      </Box>
    </Box>
  );
}
