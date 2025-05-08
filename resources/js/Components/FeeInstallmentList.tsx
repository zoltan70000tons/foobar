import { List, ListItem, ListItemAvatar, Avatar, ListItemText, Typography, Box } from "@mui/material";
import { WarningAmberRounded, Paid, CheckCircle, Timer } from "@mui/icons-material";
import { formatCurrency, formatDate } from "@/Helpers/stringUtils";
import type { Fee, InstallmentStatus, Installment } from "@/types/payments";

type Props = {
  fees: Fee[];
  feeInstallments: Installment[];
  installmentPlan: InstallmentStatus;
};

export default function FeeInstallmentList({ fees, feeInstallments, installmentPlan }: Props) {
  if (!fees?.length || !feeInstallments?.length) return null;

  return (
    <List sx={{ width: "100%", bgcolor: "transparent", p: 0 }}>
      {feeInstallments.map((feeInst) => {
        // Get full fee object from passenger fees
        const fee = fees.find((f) => f.id === feeInst.fee_id);
        if (!fee) return null;

        const isLate = fee.type.toLowerCase().includes("late");
        const Icon = isLate ? Timer : Paid;

        const isPaid = installmentPlan?.paid_installments?.some((i) => i.installment_id === feeInst.id);
        const status = isPaid ? "Paid" : "Due Immediately";
        const statusIcon = isPaid ? (
          <CheckCircle color="success" fontSize="small" />
        ) : (
          <WarningAmberRounded color="error" fontSize="small" />
        );

        return (
          <ListItem key={feeInst.id} disableGutters sx={{ alignItems: "flex-start" }}>
            <ListItemAvatar>
              <Avatar sx={{ bgcolor: !isPaid ? "error.main" : "success.main" }}>
                <Icon fontSize="small" />
              </Avatar>
            </ListItemAvatar>
            <ListItemText
              primary={
                <Box display="flex" alignItems="center" gap={1}>
                  <Typography fontSize={14} fontWeight="bold">
                    {fee.type}
                  </Typography>
                  {statusIcon}
                </Box>
              }
              secondary={
                <>
                  <Typography fontSize={12}>
                    {formatCurrency(fee.amount)} — {status}
                  </Typography>
                  <Typography fontSize={12} color="text.secondary">
                    Added on {formatDate(fee.created_at)}
                  </Typography>
                </>
              }
              primaryTypographyProps={{ component: "div" }}
              secondaryTypographyProps={{ component: "div" }}
            />
          </ListItem>
        );
      })}
    </List>
  );
}
