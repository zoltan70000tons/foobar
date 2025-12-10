import React, { useEffect, useState } from "react";
import { Button, Dialog, DialogTitle, DialogContent, DialogActions } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { CabinCategory } from "@/interfaces/CabinCategory";

type NewBookingModalProps = {
  cabinTypes: Array<{ id: number; name: string }>;
  eventId: number | string;
  cabinCategories?: CabinCategory[];
  onBookingCreated: () => void;
};

const NewBookingModal: React.FC<NewBookingModalProps> = ({
  cabinTypes,
  cabinCategories: initialCabinCategories = [],
  eventId,
  onBookingCreated,
}) => {
  const [open, setOpen] = useState(false);
  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);
  const { hasPermission } = usePermissions();
  const canCreateBooking = hasPermission(Permissions.CreateBookings);
  const [isCreateCustomerVisible, setIsCreateCustomerVisible] = useState(false);
  const [cabinCategories, setCabinCategories] = useState<CabinCategory[]>(initialCabinCategories);
  const [loadingCategories, setLoadingCategories] = useState(false);
  const { showSnackbar } = useSnackbar();

  const fetchCabinCategories = async () => {
    try {
      setLoadingCategories(true);
      const response = await axios.get(route("bookings.cabinCategories", { id: eventId }));
      setCabinCategories(response?.data?.cabinCategories || []);
    } catch (error) {
      console.error("Error loading cabin categories", error);
      showSnackbar("Unable to load cabin categories. Please try again.", "error");
    } finally {
      setLoadingCategories(false);
    }
  };

  useEffect(() => {
    if (open && cabinCategories.length === 0 && !loadingCategories) {
      fetchCabinCategories();
    }
  }, [open]);

  return (
    <>
      <Button variant="outlined" color="secondary" onClick={handleOpen} disabled={!canCreateBooking} style={{height:'40px'}}>
        New Booking
      </Button>

      {/* Modal */}
      <Dialog open={open} onClose={handleClose} maxWidth="lg" fullWidth>
        <DialogTitle>New Booking</DialogTitle>
        <DialogContent>
          <BookingStepper
            cabinTypes={cabinTypes}
            cabinCategories={cabinCategories}
            close={handleClose}
            setIsCreateCustomerVisible={setIsCreateCustomerVisible}
            onBookingCreated={onBookingCreated}
          />
        </DialogContent>
        <DialogActions>
          {isCreateCustomerVisible && (
            <a
              href={route('customers.create')}
              target="_blank"
              rel="noopener noreferrer"
              style={{ textDecoration: 'none' }}
            >
              <Button
                variant="outlined"
                color="warning"
              >
                Create Customer
              </Button>
            </a>
          )}
          <Button onClick={handleClose} variant="outlined" color="secondary">
            Cancel
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default NewBookingModal;
