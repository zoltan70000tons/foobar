import React, { useState, useEffect } from "react";
import {
  Box,
  TextField,
  MenuItem,
  Button,
  Typography,
  Grid,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  List,
  ListItem,
  ListItemText,
  IconButton,
  Paper,
  ListItemIcon,
  Select,
  FormControl,
  InputLabel,
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import DiscountOutlined from "@mui/icons-material/DiscountOutlined";
import AddOutlined from "@mui/icons-material/AddOutlined";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { router, usePage } from "@inertiajs/react";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import LoadingOverlay from "@/Components/LoadingOverlay";

type AdjustmentFormProps = {
  booking: Booking;
  editMode: boolean;
  list: AdjustmentData[]; // Ensure available adjustments
};

type Booking = {
  id: number;
  event_id: number;
  adjustments?: AdjustmentData[];
};

type AdjustmentData = {
  id?: number;
  type: "DISCOUNT" | "ADDON";
  operation: "FIXED" | "PERCENTAGE";
  value: number;
  code: string;
  system: boolean;
};

const defaultFormData: AdjustmentData = {
  type: "DISCOUNT",
  operation: "FIXED",
  value: 0,
  code: "",
};

const AdjustmentForm: React.FC<AdjustmentFormProps> = ({ booking, editMode, list }) => {
  const { props } = usePage();

  const successMessage = props.flash.success;
  const errorMessage = props.flash.error;

  // Conditionally show the snackbar based on the flash message data
  useEffect(() => {
    if (successMessage) {
      showSnackbar(successMessage, "success");
    }

    if (errorMessage) {
      showSnackbar(errorMessage, "error");
    }
  }, [successMessage, errorMessage]);

  const [formData, setFormData] = useState<AdjustmentData>(defaultFormData);
  const [adjustments, setAdjustments] = useState<AdjustmentData[]>(booking.adjustments || []);
  const [open, setOpen] = useState(false);
  const [currentEditingId, setCurrentEditingId] = useState<number | null>(null);
  const [selectedAdjustmentId, setSelectedAdjustmentId] = useState<number | "new">("new");
  const [notAllowedEdition, setNotAllowedEdition] = useState(false);
  const [loading, setLoading] = useState(false);

  const { showSnackbar } = useSnackbar();
  const { hasPermission } = usePermissions();
  const canAddAdjustment = hasPermission(Permissions.CreateAdjustments);
  const canDeleteAdjustment = hasPermission(Permissions.DeleteAdjustments);
  const canEditAdjustment = hasPermission(Permissions.EditAdjustments);

  useEffect(() => {
    setAdjustments(booking.adjustments || []);
  }, [booking]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === "value" ? parseFloat(value) : value,
    }));
  };

  const handleSelectChange = (event: React.ChangeEvent<{ value: unknown }>) => {
    const selectedId = event.target.value as number | "new";
    setSelectedAdjustmentId(selectedId);

    if (selectedId === "new") {
      setFormData(defaultFormData);
      setNotAllowedEdition(false);
    } else {
      const selectedAdjustment = list.find((adj) => adj.id === selectedId);
      if (selectedAdjustment) {
        setFormData(selectedAdjustment);
        if (selectedAdjustment.system) {
          setNotAllowedEdition(true);
        }
      }
    }
  };

  const resetForm = () => {
    setFormData(defaultFormData);
    setCurrentEditingId(null);
    setSelectedAdjustmentId("new");
  };

  const handleOpen = () => {
    resetForm();
    setNotAllowedEdition(false);
    setOpen(true);
  };

  const handleEdit = (id: number) => {
    const adjustment = adjustments.find((adj) => adj.id === id);
    if (adjustment) {
      setFormData(adjustment);
      setCurrentEditingId(id);
      setOpen(true);
    }
  };

  const handleDelete = (id: number) => {
    setLoading(true);

    router.post(
      route("bookings.deleteAdjustment", {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      { id },
      {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
        onFinish: () => setLoading(false),
      },
    );
  };

  const createAdjustment = () => {
    setLoading(true);

    router.post(
      route("bookings.createAdjustment", {
        event_id: booking.event_id,
        booking_id: booking.id,
        selected_adjustment_id: selectedAdjustmentId,
      }),
      formData,
      {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
        onFinish: () => setLoading(false),
      },
    );
  };

  const updateAdjustment = () => {
    setLoading(true);

    router.post(
      route("bookings.updateAdjustment", {
        event_id: booking.event_id,
        booking_id: booking.id,
      }),
      { id: currentEditingId, ...formData, selected_adjustment_id: selectedAdjustmentId },
      {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
        onFinish: () => setLoading(false),
      },
    );
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (formData.value <= 0) {
      showSnackbar("Value must be greater than 0.", "error");
      return;
    }
    if (currentEditingId) {
      await updateAdjustment();
    } else {
      await createAdjustment();
    }
    resetForm();
    setOpen(false);
  };

  return (
    <Box>
      <Typography variant="h5" mb={2}>
        Adjustments
      </Typography>
      <Paper variant="outlined" sx={{ p: 2, mb: 4, backgroundColor: "#1c1c1c" }}>
        {adjustments.length > 0 ? (
          <List>
            {adjustments.map((adjustment) => (
              <ListItem
                key={adjustment.id}
                secondaryAction={
                  <>
                    {!adjustment?.system && (
                      <IconButton
                        edge="end"
                        aria-label="edit"
                        onClick={() => handleEdit(adjustment.id!)}
                        disabled={!canEditAdjustment || !editMode}
                      >
                        <EditIcon />
                      </IconButton>
                    )}
                    <IconButton
                      edge="end"
                      aria-label="delete"
                      onClick={() => handleDelete(adjustment.id!)}
                      disabled={!canDeleteAdjustment || !editMode}
                    >
                      <DeleteIcon />
                    </IconButton>
                  </>
                }
              >
                <ListItemIcon>
                  {adjustment.type === "DISCOUNT" ? (
                    <DiscountOutlined color="primary" />
                  ) : (
                    <AddOutlined color="secondary" />
                  )}
                </ListItemIcon>
                <ListItemText
                  primary={`${adjustment.type} (${adjustment.code})`}
                  secondary={`Value: ${adjustment.value} ${adjustment.operation === "PERCENTAGE" ? "%" : ""}`}
                />
              </ListItem>
            ))}
          </List>
        ) : (
          <Typography>No adjustments associated yet.</Typography>
        )}
        <Button
          variant="outlined"
          color="secondary"
          startIcon={<AddIcon />}
          onClick={handleOpen}
          sx={{ mt: 2 }}
          disabled={!editMode || !canAddAdjustment}
        >
          Add Adjustment
        </Button>
      </Paper>

      <Dialog open={open} onClose={() => setOpen(false)} fullWidth maxWidth="sm">
        <DialogTitle>{currentEditingId ? "Edit Adjustment" : "Create Adjustment"}</DialogTitle>
        <DialogContent>
          <FormControl fullWidth sx={{ mt: 1, mb: 2 }}>
            <InputLabel id="adjustment-select-label">Select an Adjustment Or Create New</InputLabel>
            <Select
              labelId="adjustment-select-label"
              value={selectedAdjustmentId}
              onChange={handleSelectChange}
              label="Select an Adjustment Or Create New"
            >
              <MenuItem value="new">New Adjustment</MenuItem>
              {list.map((adj) => (
                <MenuItem key={adj.id} value={adj.id}>
                  {adj.code}
                </MenuItem>
              ))}
            </Select>
          </FormControl>

          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                label="Code"
                name="code"
                value={formData.code}
                onChange={handleChange}
                fullWidth
                disabled={notAllowedEdition}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                select
                label="Type"
                name="type"
                value={formData.type}
                onChange={handleChange}
                fullWidth
                disabled={notAllowedEdition}
              >
                <MenuItem value="DISCOUNT">Discount</MenuItem>
                <MenuItem value="ADDON">Addon</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                select
                label="Operation"
                name="operation"
                value={formData.operation}
                onChange={handleChange}
                fullWidth
                disabled={notAllowedEdition}
              >
                <MenuItem value="FIXED">Fixed</MenuItem>
                <MenuItem value="PERCENTAGE">Percentage</MenuItem>
              </TextField>
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Value"
                name="value"
                type="number"
                value={formData.value}
                onChange={handleChange}
                fullWidth
                disabled={notAllowedEdition}
                inputProps={{ step: 0.01, min: 0 }}
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button variant="outlined" onClick={() => setOpen(false)} color="secondary">
            Cancel
          </Button>
          <Button variant="contained" onClick={handleSubmit} color="primary">
            {currentEditingId ? "Update Adjustment" : "Save Adjustment"}
          </Button>
        </DialogActions>
      </Dialog>
      <LoadingOverlay open={loading} />
    </Box>
  );
};

export default AdjustmentForm;
