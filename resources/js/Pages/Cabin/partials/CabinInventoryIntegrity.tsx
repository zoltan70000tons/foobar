import { useState } from "react";
import {
  Box,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Divider,
  Typography,
} from "@mui/material";
import FactCheckIcon from "@mui/icons-material/FactCheck";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

type IntegrityIssue = {
  event_id: number;
  event_name?: string;
  cabin_id: number;
  cabin_number?: string | null;
  cabin_status?: string;
  cabin_type?: string | null;
  category_code?: string | null;
  category_capacity?: number | null;
  capacity_name?: string | null;
  inventory?: number | null;
  booking_count?: number;
  passenger_count?: number;
  message?: string;
};

type IntegrityReport = {
  run_at?: string;
  summary?: {
    events_checked?: number;
    cabins_checked?: number;
    issues_count?: number;
  };
  issues?: IntegrityIssue[];
  message?: string;
};

type Props = {
  eventId: number;
  canRun: boolean;
};

export default function CabinInventoryIntegrity({ eventId, canRun }: Props) {
  const { showSnackbar } = useSnackbar();
  const [integrityDialogOpen, setIntegrityDialogOpen] = useState(false);
  const [integrityReport, setIntegrityReport] = useState<IntegrityReport | null>(null);
  const [integrityRunning, setIntegrityRunning] = useState(false);
  const [integrityEmailSent, setIntegrityEmailSent] = useState<boolean | null>(null);

  console.log("Integrity Report:", integrityReport);

  // category_code
  // category_capacity

  const handleCloseIntegrityDialog = () => {
    setIntegrityDialogOpen(false);
  };

  const runIntegrityCheck = async () => {
    setIntegrityRunning(true);
    try {
      const res = await axios.post(route("cabins.integrity-check", { id: eventId }));
      const report = res.data?.report as IntegrityReport | undefined;
      const emailSent = Boolean(res.data?.email_sent);
      setIntegrityReport(report ?? null);
      setIntegrityEmailSent(emailSent);
      setIntegrityDialogOpen(true);

      if ((report?.summary?.issues_count ?? 0) > 0) {
        showSnackbar(`Integrity check completed with ${report?.summary?.issues_count} issues.`, "warning");
      } else {
        showSnackbar("Integrity check completed with no issues found.", "success");
      }
    } catch (error) {
      console.error("Integrity check failed:", error);
      showSnackbar("Failed to run integrity check.", "error");
    } finally {
      setIntegrityRunning(false);
    }
  };

  return (
    <>
      {canRun && (
        <Button
          variant="outlined"
          color="primary"
          startIcon={<FactCheckIcon />}
          disabled={integrityRunning}
          onClick={runIntegrityCheck}
        >
          {integrityRunning ? "Running Integrity Check..." : "Run Integrity Check"}
        </Button>
      )}
      <Dialog open={integrityDialogOpen} onClose={handleCloseIntegrityDialog} maxWidth="md" fullWidth>
        <DialogTitle>Cabin Inventory Integrity Report</DialogTitle>
        <DialogContent dividers>
          <DialogContentText>Generated at: {integrityReport?.run_at ?? "N/A"}</DialogContentText>
          <DialogContentText>
            Events checked: {integrityReport?.summary?.events_checked ?? 0}, Cabins checked:{" "}
            {integrityReport?.summary?.cabins_checked ?? 0}, Issues found: {integrityReport?.summary?.issues_count ?? 0}
          </DialogContentText>
          <DialogContentText>
            Email sent: {integrityEmailSent === null ? "N/A" : integrityEmailSent ? "Yes" : "No"}
          </DialogContentText>
          {integrityReport?.message && <DialogContentText sx={{ mt: 1 }}>{integrityReport.message}</DialogContentText>}
          <Divider sx={{ my: 2 }} />
          {(integrityReport?.summary?.issues_count ?? 0) === 0 ? (
            <Typography>No integrity issues detected.</Typography>
          ) : (
            <Box component="ul" sx={{ pl: 3, m: 0 }}>
              {(integrityReport?.issues ?? []).map((issue, index) => (
                <Box component="li" key={`${issue.cabin_id}-${index}`} sx={{ mb: 1 }}>
                  <Typography variant="body2">
                    <strong>{issue.event_name ?? "Event"}</strong> — Cabin {issue.category_code ?? "N/A"} /{" "}
                    {issue.capacity_name ?? "N/A"} / {issue.cabin_number + " (ID: " + issue.cabin_id + ")"} (
                    {issue.cabin_status ?? "N/A"}) — {issue.message ?? "Issue detected"}
                  </Typography>
                </Box>
              ))}
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseIntegrityDialog} color="primary">
            Close
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
}
