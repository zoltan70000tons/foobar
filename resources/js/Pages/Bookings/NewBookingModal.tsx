import React, { useState } from "react";
import { Button, Dialog, DialogTitle, DialogContent, DialogActions } from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import { Permissions } from "@/enums/PermissionEnum";
import BookingStepper from "./BookingStepper";
import { router } from "@inertiajs/react";

const NewBookingModal: React.FC = ({cabinTypes, cabinCategories}) => {

  const [open, setOpen] = useState(false);
  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);
  const {hasPermission} = usePermissions(); 
  const canCreateBooking = hasPermission(Permissions.CreateBookings);
  const [isCreateCustomerVisible, setIsCreateCustomerVisible] = useState(false);

  return (
    <>
      <Button variant="outlined" color="secondary" onClick={handleOpen} disabled={!canCreateBooking} style={{height:'40px'}}>
        New Booking
      </Button>

      {/* Modal */}
      <Dialog open={open} onClose={handleClose} maxWidth="lg" fullWidth>
        <DialogTitle>New Booking</DialogTitle>
        <DialogContent>
          <BookingStepper cabinTypes={cabinTypes} cabinCategories={cabinCategories} close={handleClose} setIsCreateCustomerVisible={setIsCreateCustomerVisible} />
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
