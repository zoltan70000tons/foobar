import React, { useEffect, useState } from "react";
import { Button, Modal, Dialog, DialogTitle, DialogContent, DialogActions, Box } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Customer } from "@/interfaces/Customer";
import CustomerModal from "./partials/CustomerModal";

const bookingModalStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  bgcolor: "grey.900",
  border: "1px solid #3f3f3f",
  borderRadius: 2,
  boxShadow: 24,
  width: "100%",
  maxWidth: 1200,
  p: 2,
};

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
  const [isCustomerModalOpen, setIsCustomerModalOpen] = useState<boolean>(false);
  const [isBookingOpen, setIsBookingOpen] = useState<boolean>(false);
  const [loadingCategories, setLoadingCategories] = useState(false);
  const { showSnackbar } = useSnackbar();
  const [createdCustomer, setCreatedCustomer] = useState<Customer | null>(null);
  const handleCustomerCreated = (customer: Customer) => {
    console.log(customer);
    setCreatedCustomer(customer);
    closeCustomerModal();
  };
  const openBookingModal = () => setIsBookingOpen(true);
  const closeBookingModal = () => setIsBookingOpen(false);
  const openCustomerModal = () => setIsCustomerModalOpen(true);
  const closeCustomerModal = () => setIsCustomerModalOpen(false);

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
    if (isBookingOpen && cabinCategories.length === 0 && !loadingCategories) {
      fetchCabinCategories();
    }
  }, [isBookingOpen, eventId]);

  return (
    <>
      <Button
        variant="outlined"
        color="secondary"
        onClick={openBookingModal}
        disabled={!canCreateBooking}
        style={{ height: "40px" }}
      >
        New Booking
      </Button>
      <Modal
        open={isBookingOpen}
        onClose={closeBookingModal}
        aria-labelledby="parent-modal-title"
        aria-describedby="parent-modal-description"
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
        }}
      >
        <Box sx={{ ...bookingModalStyle, width: 1160 }}>
          <Box
            sx={{
              display: "flex",
              flexDirection: "column",
              gap: 2,
            }}
          >
            <h2 id="parent-modal-title">New Booking</h2>
            <BookingStepper
              cabinTypes={cabinTypes}
              cabinCategories={cabinCategories}
              close={closeBookingModal}
              setIsCreateCustomerVisible={setIsCreateCustomerVisible}
              onBookingCreated={onBookingCreated}
              createdCustomer={createdCustomer}
            />
            <Box
              sx={{
                display: "flex",
                gap: 2,
                justifyContent: "flex-end",
              }}
            >
              {isCreateCustomerVisible && (
                <CustomerModal
                  open={isCustomerModalOpen}
                  onOpen={openCustomerModal}
                  onClose={closeCustomerModal}
                  onCustomerCreated={handleCustomerCreated}
                />
              )}
              <Button onClick={closeBookingModal} variant="outlined" color="secondary">
                Cancel
              </Button>
            </Box>
          </Box>
        </Box>
      </Modal>
    </>
  );
};

export default NewBookingModal;
