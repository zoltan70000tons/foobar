import React, { useEffect, useMemo } from "react";
import { Box, Button, Grid, Step, StepLabel, Stepper, } from "@mui/material";

import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import LoadingOverlay from "@/Components/LoadingOverlay";
import { LoadingButton } from "@mui/lab";
import SpecialRequest from "@/Pages/Bookings/partials/SpecialRequest";
import { CabinCategory, CabinType as CabinTypeType } from "@/types/cabin";
import { Customer } from "@/interfaces/Customer";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { BookingStepperStepOne } from "@/Pages/Bookings/partials/BookingStepper/step1";
import { BookingStepperStepZero } from "@/Pages/Bookings/partials/BookingStepper/step0";
import { BookingStepperStepThree } from "@/Pages/Bookings/partials/BookingStepper/step3";
import { BookingStepperStepFour } from "@/Pages/Bookings/partials/BookingStepper/step4";
import { selectBooking } from "@/store/slices/selectors";
import { useValidateGenders } from "@/Hooks/booking/useValidateGenders";
import { submitBooking } from "@/store/thunks/submitBooking";
import { UnknownAction } from "@reduxjs/toolkit";
import { Passenger } from "@/interfaces/Passenger";

type BookingStepperProps = {
  cabinTypes: CabinTypeType[];
  cabinCategories: CabinCategory[];
  close: () => void;
  setIsCreateCustomerVisible: (visible: boolean) => void;
  onBookingCreated: () => void;
  createdCustomer: Customer | null;
  eventId: number;
};

const BookingStepper: React.FC<BookingStepperProps> = ({
  cabinTypes,
  cabinCategories,
  close,
  setIsCreateCustomerVisible,
  onBookingCreated,
  createdCustomer,
  eventId,
}) => {
  const {
    nextStep,
    prevStep,
    setCabinTypes,
    setCabinCategories,
    resetBooking,
    setPassengerField,
    setCreatedCustomer,
    setEventId,
  } = useBookingActions();
  const dispatch = useAppDispatch();

  useEffect(() => {
    if (!cabinTypes.length && !cabinCategories?.length) return;

    setCabinTypes(cabinTypes);
    setCabinCategories(cabinCategories);
    setCreatedCustomer(createdCustomer);
    setEventId(eventId);
  }, [cabinTypes, cabinCategories, createdCustomer, eventId, setCabinTypes, setCabinCategories, setCreatedCustomer, setEventId]);

  const {
    step: activeStep,
    cabinCategory,
    cabinType,
    paymentPlan,
    cabinNumber,
    bedConfig,
    installments: numberOfInstallments,
    isSingleRoom,
    passenger,
  } = useAppSelector(selectBooking);

  const { selectedUser } = useAppSelector(selectBooking);
  const validateGenders = useValidateGenders({ cabinType, selectedUser });

  const validateStep = () => {
    switch (activeStep) {
      case 0: {
        let rule =
          cabinType &&
          cabinCategory &&
          cabinNumber &&
          paymentPlan &&
          bedConfig;

        if (paymentPlan?.value === "INSTALLMENTS") {
          rule = rule && numberOfInstallments;
        }

        return !!rule;
      }
      case 1:
        return (
          !!(
            passenger.first_name &&
            passenger.last_name &&
            passenger.address_first &&
            passenger.city &&
            passenger.country &&
            passenger.email &&
            passenger.dob &&
            passenger.gender &&
            passenger.payment_method &&
            passenger.terms_n_cons &&
            passenger.emergency_c_phone &&
            passenger.emergency_c_name &&
            (isSingleRoom ? passenger.single_t_agreement : true)
          ) && validateGenders(true)
        );
      case 2:
        return true;
      case 3:
        return true;
      case 4:
        return true;
      default:
        return false;
    }
  };
  const isNextDisabled = useMemo(() => !validateStep(), [
    activeStep,
    cabinType,
    cabinCategory,
    cabinNumber,
    passenger,
    paymentPlan,
    numberOfInstallments,
    bedConfig,
    isSingleRoom,
    validateGenders,
  ]);

  const loading = useAppSelector((s) => s.booking.loading);

  const handleClose = () => {
    resetBooking();
    close();
  };

  useEffect(() => {
    setIsCreateCustomerVisible(activeStep === 1);
  }, [activeStep, setIsCreateCustomerVisible]);

  const { showSnackbar } = useSnackbar();

  const steps = ["Select Cabin", "Passenger Details", "Special Request", "Discounts/Addons", "Confirm & Submit"];

  const onChange = <K extends keyof Passenger>(field: K, value: Passenger[K]) => {
    setPassengerField(field, value);
  };

  const handleSubmit = () => {
    const result = dispatch(submitBooking() as UnknownAction);

    if (submitBooking.fulfilled.match(result)) {
      showSnackbar("Booking created successfully!", "success");
      resetBooking();
      onBookingCreated?.();
      handleClose();
    }

    if (submitBooking.rejected.match(result)) {
      showSnackbar(result.payload as string, "error");
    }
  };

  return (
    <Box sx={{ width: "100%", margin: "0 auto", mt: 4 }}>
      <Stepper activeStep={activeStep}>
        {steps.map((label, index) => (
          <Step key={index}>
            <StepLabel>{label}</StepLabel>
          </Step>
        ))}
      </Stepper>

      <Box>
        {activeStep === 0 && (
          <BookingStepperStepZero />
        )}

        {activeStep === 1 && (
          <BookingStepperStepOne />
        )}

        {activeStep === 2 && (
          <Box sx={{ mt: 4 }}>
            <Grid item xs={12}>
              <SpecialRequest disabledByDesign={false} onChange={onChange} passenger={passenger} />
            </Grid>
          </Box>
        )}

        {activeStep === 3 && (
          <BookingStepperStepThree />
        )}

        {activeStep === 4 && (
          <BookingStepperStepFour />
        )}

        <Box sx={{ display: "flex", justifyContent: "space-between", mt: 4 }}>
          <Button disabled={activeStep === 0} onClick={prevStep} variant="outlined">
            Back
          </Button>
          {activeStep === steps.length - 1 ? (
            <LoadingButton
              onClick={handleSubmit}
              variant="outlined"
              color="success"
              loading={loading}
              loadingPosition="center"
            >
              Create Booking
            </LoadingButton>
          ) : (
            <Button onClick={nextStep} variant="contained" color="primary" disabled={isNextDisabled}>
              Next
            </Button>
          )}
        </Box>
      </Box>
      <LoadingOverlay open={loading} />
    </Box>
  );
};

export default BookingStepper;
