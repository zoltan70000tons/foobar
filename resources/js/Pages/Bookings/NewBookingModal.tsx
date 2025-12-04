import { useState } from "react";
import { Button, Box, Modal, Paper } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import CreateCustomer from "../Customer/partials/CreateCustomer";
import { CabinCategory } from "@/interfaces/CabinCategory";

const style = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  bgcolor: "grey.900",
  border: "1px solid #3f3f3f",
  borderRadius: 2,
  boxShadow: 24,
  pt: 2,
  px: 4,
  pb: 3,
  width: "100%",
  maxWidth: 1200,
};

type Props = {
  cabinTypes: Array<{ id: number; name: string }>;
  cabinCategories: CabinCategory[];
  onBookingCreated: () => void;
};

export default function NewBookingModal({ cabinTypes, cabinCategories, onBookingCreated }: Props) {
  const [open, setOpen] = useState<boolean>(false);
  const [createdCustomerId, setCreatedCustomerId] = useState<string | null>(null);

  // open and set param modal in url
  const handleOpen = () => {
    // router.visit(window.location.pathname, {
    //   data: { modal: "new_booking" },
    //   preserveScroll: true,
    //   preserveState: true,
    // });

    setOpen(true);
  };

  // close and remove param modal from url
  const handleClose = () => {
    // router.visit(window.location.pathname, {
    //   // Remove modal param from URL
    //   data: { modal: null },
    //   preserveScroll: true,
    //   preserveState: true,
    // });
    setOpen(false);
  };

  const { hasPermission } = usePermissions();
  const canCreateBooking = hasPermission(Permissions.CreateBookings);
  const [isCreateCustomerVisible, setIsCreateCustomerVisible] = useState(false);

  return (
    <>
      <Button
        variant="outlined"
        color="secondary"
        onClick={handleOpen}
        disabled={!canCreateBooking}
        style={{ height: "40px" }}
      >
        New Booking
      </Button>
      <Modal
        open={open}
        onClose={handleClose}
        aria-labelledby="parent-modal-title"
        aria-describedby="parent-modal-description"
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
        }}
      >
        <Box sx={{ ...style, width: 1160 }}>
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
              close={handleClose}
              setIsCreateCustomerVisible={setIsCreateCustomerVisible}
              onBookingCreated={onBookingCreated}
              createdCustomerId={createdCustomerId}
            />
            <Box
              sx={{
                display: "flex",
                gap: 2,
                justifyContent: "flex-end",
              }}
            >
              {isCreateCustomerVisible && <CustomerModal setCreatedCustomerId={setCreatedCustomerId} />}
              <Button onClick={handleClose} variant="outlined" color="secondary">
                Cancel
              </Button>
            </Box>
          </Box>
        </Box>
      </Modal>
    </>
  );
}

type CustomerModalProps = {
  setCreatedCustomerId?: (id: string) => void;
};

// Customer Modal
const CustomerModal = ({ setCreatedCustomerId }: CustomerModalProps) => {
  const [open, setOpen] = useState(false);
  const handleOpen = () => {
    console.log("Opening Customer Modal");

    setOpen(true);
  };
  const handleClose = () => {
    setOpen(false);
  };

  return (
    <>
      <Button variant="outlined" color="warning" onClick={handleOpen}>
        Create Customer
      </Button>
      <Modal
        open={open}
        onClose={handleClose}
        aria-labelledby="child-modal-title"
        aria-describedby="child-modal-description"
        slotProps={{
          backdrop: {
            sx: {
              backgroundColor: "rgba(0, 0, 0, 0.3)",
            },
          },
        }}
      >
        <Box
          sx={{
            ...style,
            width: 1300,
            height: "84vh",
            transform: "translate(-50%, -44%)",
            overflow: "auto",
            alignItems: "center",
            justifyContent: "center",
            py: 0,
            zIndex: (theme) => theme.zIndex.modal + 1,
          }}
        >
          <Box sx={{ position: "relative" }}>
            <CreateCustomer handleClose={handleClose} setCreatedCustomerId={setCreatedCustomerId} />
          </Box>
        </Box>
      </Modal>
    </>
  );
};
