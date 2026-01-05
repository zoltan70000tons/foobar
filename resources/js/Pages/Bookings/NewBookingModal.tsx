import React, { useCallback, useEffect, useState } from "react";
import { Button, Modal, Box } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import axios from "axios";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { CabinCategory } from "@/interfaces/CabinCategory";
import { Customer } from "@/interfaces/Customer";
import CustomerModal from "./partials/CustomerModal";
import { CabinType } from "@/types/cabin";

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
  cabinTypes: CabinType[];
  eventId: number;
  cabinCategories?: CabinCategory[];
  onBookingCreated: () => void;
};

const NewBookingModal: React.FC<NewBookingModalProps> = ({
  cabinTypes,
  cabinCategories: initialCabinCategories = [],
  eventId,
  onBookingCreated,
}: NewBookingModalProps) => {
  const { hasPermission } = usePermissions();
  const canCreateBooking = hasPermission(Permissions.CreateBookings);
  const { showSnackbar } = useSnackbar();

  const [isBookingOpen, setIsBookingOpen] = useState<boolean>(false);
  const [isCustomerModalOpen, setIsCustomerModalOpen] = useState<boolean>(false);
  const [isCreateCustomerVisible, setIsCreateCustomerVisible] = useState(false);
  const [cabinCategories, setCabinCategories] = useState<CabinCategory[]>(
    initialCabinCategories ?? []
  );
  const [loadingCategories, setLoadingCategories] = useState(false);
  const [createdCustomer, setCreatedCustomer] = useState<Customer | null>(null);

  const handleCustomerCreated = (customer: Customer) => {
    setCreatedCustomer(customer);
    setIsCustomerModalOpen(false);
  };

  const fetchCabinCategories = useCallback(async () => {
    try {
      setLoadingCategories(true);
      const { data } = await axios.get(
        route("bookings.cabinCategories", { id: eventId })
      );

      setCabinCategories(data?.cabinCategories ?? []);
    } catch (error) {
      console.error(error);
      showSnackbar("Unable to load cabin categories. Please try again.", "error");
    } finally {
      setLoadingCategories(false);
    }
  }, [eventId, showSnackbar]);

  useEffect(() => {
    if (isBookingOpen && cabinCategories.length === 0 && !loadingCategories) {
      fetchCabinCategories();
    }
  }, [isBookingOpen, cabinCategories.length, loadingCategories, fetchCabinCategories]);

  return (
    <>
      <Button
        variant="outlined"
        color="secondary"
        onClick={() => setIsBookingOpen(true)}
        disabled={!canCreateBooking}
        sx={{ height: 40 }}
      >
        New Booking
      </Button>
      <Modal
        open={isBookingOpen}
        onClose={() => setIsBookingOpen(false)}
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
              close={() => setIsBookingOpen(false)}
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
                  onOpen={() => setIsCustomerModalOpen(true)}
                  onClose={() => setIsCustomerModalOpen(false)}
                  onCustomerCreated={handleCustomerCreated}
                />
              )}
              <Button onClick={() => setIsBookingOpen(false)} variant="outlined" color="secondary">
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
