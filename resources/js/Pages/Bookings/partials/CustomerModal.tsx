import { Button, Box, Modal } from "@mui/material";
import CreateCustomer from "../../Customer/partials/CreateCustomer";
import { Customer } from "@/interfaces/Customer";

const modalStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -44%)",
  bgcolor: "grey.900",
  border: "1px solid #3f3f3f",
  borderRadius: 2,
  boxShadow: 24,
  pt: 2,
  px: 4,
  pb: 3,
  width: 1300,
  height: "84vh",
  overflow: "auto",
  alignItems: "center",
  justifyContent: "center",
  py: 0,
};

type CustomerModalProps = {
  open: boolean;
  onOpen: () => void;
  onClose: () => void;
  onCustomerCreated: (customer: Customer) => void;
};

// Customer Modal
export default function CustomerModal({ open, onOpen, onClose, onCustomerCreated }: CustomerModalProps) {
  return (
    <>
      <Button variant="outlined" color="warning" onClick={onOpen} style={{ height: "40px", marginRight: "10px" }}>
        Create Customer
      </Button>
      <Modal
        open={open}
        onClose={onClose}
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
        <Box sx={modalStyle}>
          <Box sx={{ position: "relative" }}>
            <CreateCustomer isCloseBtn={true} handleClose={onClose} setCreatedCustomer={onCustomerCreated} />
          </Box>
        </Box>
      </Modal>
    </>
  );
}
