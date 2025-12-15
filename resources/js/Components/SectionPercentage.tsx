import { useEffect, useState } from "react";
import { Box, Button, Dialog, DialogActions, DialogContent, DialogTitle, IconButton, TextField, Typography, alpha } from "@mui/material";
import { green, blue, red } from "@mui/material/colors";
import { InfoRounded } from "@mui/icons-material";
import FeeInstallmentList from "@/Components/FeeInstallmentList";
import { getOrdinalName } from "@/Helpers/stringUtils";
import type { InstallmentItem, InstallmentStatus, Fee, Installment } from "@/types/payments"; // Adjust the import path as needed
import { useForm, router } from '@inertiajs/react';

// Helpers
import { formatDate, formatCurrency } from "@/Helpers/stringUtils";
import { Passenger } from "@/interfaces/Passenger";
import { Booking } from "@/types/booking";
import EditCalendarIcon from "@mui/icons-material/EditCalendar";
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import dayjs from 'dayjs';
import { editingStateInitializer } from "@mui/x-data-grid/internals";
import { Permissions } from "@/enums/PermissionEnum";
import { usePermissions } from '@/Providers/PermissionContext';


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
  nextDue?: string | null;
  onEditDate?: (installment: Installment, newDate: string) => Promise<void>;
  editMode?: boolean;
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
  nextDue,
  onEditDate,
  editMode
}: InstallmentPaymentProps) => { 
  const [open, setOpen] = useState(false);
  const [selectedDate, setSelectedDate] = useState(
  installment?.due_date ? dayjs(installment.due_date) : null
  );
  const [saving, setSaving] = useState(false);

  const nextDueDayjs = nextDue ? dayjs(nextDue) : null;
  const dateInvalid = selectedDate && nextDueDayjs && selectedDate.isAfter(nextDueDayjs, "day");
  const { hasPermission } = usePermissions();
  const canEdit = hasPermission(Permissions.EditInstallments);
  const isDisabled = status === "paid" || !editMode || !canEdit;

  useEffect(() => {
    setSelectedDate(installment?.due_date ? dayjs(installment.due_date) : null);
  }, [installment?.due_date]);

  const handleOpen = () => {
    setSelectedDate(installment?.due_date ? dayjs(installment.due_date) : null);
    setOpen(true);
  };

  const handleClose = () => {
    setSelectedDate(installment?.due_date ? dayjs(installment.due_date) : null);
    setOpen(false);
  };


  const handleSave = async () => {
    if (!selectedDate) return;
    const iso = (selectedDate as dayjs.Dayjs).toISOString();
    setSaving(true);
    try {
      if (typeof onEditDate === "function") {
        await onEditDate(installment as Installment, iso);
      } else {
        console.warn("onEditDate callback not provided");
      }
      setOpen(false);
    } catch (e) {
      console.error("Save due date failed", e);
    } finally {
      setSaving(false);
    }
  };

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
          flexDirection: "column",
          mt: "5px",
          textTransform: "capitalize",
          p: 1,
          my: 1,
          backgroundColor:
            status === "paid" ? alpha(green[500], 0.2) : isOverdue ? alpha(red[500], 0.2) : alpha("#fff", 0.1),
          color: status === "paid" ? green[500] : isOverdue ? red[500] : "inherit",
          borderRadius: 1,
        }}
      >
        <Box
          sx={{
            width: "100%",
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            mb: 0.5,
          }}
        >
          <Typography fontSize="12px">
            {status === "paid"
              ? `${formatCurrency(installment?.amount)}`
              : `${formatCurrency(installment?.amount_due)}`}
          </Typography>

          <IconButton size="small" onClick={handleOpen} aria-label="edit due date" disabled={isDisabled}>
            <EditCalendarIcon fontSize="small" />
          </IconButton>
        </Box>

        {status === "paid" ? (
          <Typography fontSize="12px">Paid</Typography>
        ) : isOverdue ? (
          <Typography fontSize="12px">Due Immediately</Typography>
        ) : (
          <Typography fontSize="12px">Due Date: {formatDate(installment?.due_date)}</Typography>
        )}

        <Typography fontSize="12px" fontWeight="bold">
          {getOrdinalName(order + 1)} Installment
        </Typography>
      </Box>
      <Dialog open={open} onClose={handleClose} fullWidth maxWidth="xs">
        <DialogTitle>Edit due date</DialogTitle>
        <DialogContent>
          <LocalizationProvider dateAdapter={AdapterDayjs}>
            <DatePicker
              disablePast
              value={selectedDate}
              onChange={(newVal) => setSelectedDate(newVal)}
              maxDate={nextDueDayjs || undefined}
              sx={{width: "100%", mt: 2}}
              renderInput={(params) => (
                <TextField
                  {...params}
                  fullWidth
                  error={!!dateInvalid}
                  helperText={
                    dateInvalid
                      ? `Due date cannot be after next installment (${nextDueDayjs?.format("YYYY-MM-DD")})`
                      : params?.inputProps?.placeholder || ""
                  }
                />
              )}
            />
          </LocalizationProvider>
        </DialogContent>

        <DialogActions sx={{ px: 3, pb: 2 }}>
          <Button onClick={handleClose} disabled={saving}>
            Cancel
          </Button>
          <Button variant="contained" onClick={handleSave} disabled={saving || !selectedDate}>
            {saving ? "Saving..." : "Save"}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

// Installments
const Installments = ({
  perc,
  installments,
  passengerAllocatedCost,
  installment_plan,
  editMode
}: {
  perc: number;
  installments: Installment[];
  passengerAllocatedCost: number;
  installment_plan: InstallmentStatus;
  editMode?: boolean;
}) => {
  if (!installments || installments.length === 0) {
    return <Box>Something went wrong</Box>;
  }

  const installmentsLength = installments.length;

  const saveInstallmentDate = (inst: InstallmentItem, newDate: string) => {
    router.patch(route("installments.update-due"), 
      { due_date: newDate, installment: inst.installment_id },
      {
        preserveScroll: true,
      }
    );
  };

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
        const nextInstallment = installments[index + 1] ?? null;
        const nextDue = nextInstallment ? nextInstallment.due_date : null;

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
            nextDue={nextDue}
            onEditDate={saveInstallmentDate}
            editMode={editMode}
          />
        );
      })}
    </Box>
  );
};

// Section Percentage
export default function SectionPercentage({ passenger, booking, installments, setIsBookingError, editMode}: Props) {
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
    "editMode", editMode
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
          editMode={editMode}
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
