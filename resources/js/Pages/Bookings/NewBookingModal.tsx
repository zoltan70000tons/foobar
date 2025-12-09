import { useState } from "react";
import { Button, Box, Modal } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import CustomerModal from "./partials/CustomerModal";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Customer } from "@/interfaces/Customer";

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

type Props = {
  cabinTypes: Array<{ id: number; name: string }>;
  cabinCategories: CabinCategory[];
  onBookingCreated: () => void;
};

export default function NewBookingModal({ cabinTypes, cabinCategories, onBookingCreated }: Props) {
  const [isBookingOpen, setIsBookingOpen] = useState<boolean>(false);
  const [isCustomerModalOpen, setIsCustomerModalOpen] = useState<boolean>(false);
  const [createdCustomer, setCreatedCustomer] = useState<Customer | null>(null);

  const openBookingModal = () => setIsBookingOpen(true);
  const closeBookingModal = () => setIsBookingOpen(false);
  const openCustomerModal = () => setIsCustomerModalOpen(true);
  const closeCustomerModal = () => setIsCustomerModalOpen(false);
  const handleCustomerCreated = (customer: Customer) => {
    setCreatedCustomer(customer);
    closeCustomerModal();
  };

  const { hasPermission } = usePermissions();
  const canCreateBooking = hasPermission(Permissions.CreateBookings);
  const [isCreateCustomerVisible, setIsCreateCustomerVisible] = useState(false);

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
}
