import React, { useEffect, useMemo, useState } from "react";
import {
  Box,
  Button,
  Stepper,
  Step,
  StepLabel,
  Grid,
} from "@mui/material";

import { useSnackbar } from "@/Providers/SnackBarAlertProvider";
import { router } from "@inertiajs/react";
import LoadingOverlay from "@/Components/LoadingOverlay";
import { LoadingButton } from "@mui/lab";
import SpecialRequest from "@/Pages/Bookings/partials/SpecialRequest";
import { CabinType as CabinTypeType} from "@/types/cabin";
import { Customer } from "@/interfaces/Customer";
import { useAppSelector } from "@/store/hooks";
import { useBookingActions } from "@/Hooks/booking/useBookingActions";
import { BookingStepperStepOne } from "@/Pages/Bookings/partials/BookingStepper/step1";
import { BookingStepperStepZero } from "@/Pages/Bookings/partials/BookingStepper/step0";
import { BookingStepperStepThree } from "@/Pages/Bookings/partials/BookingStepper/step3";
import { BookingStepperStepFour } from "@/Pages/Bookings/partials/BookingStepper/step4";
import { selectBooking } from "@/store/slices/selectors";
import { useValidateGenders } from "@/Hooks/booking/useValidateGenders";

type BookingStepperProps = {
  cabinTypes: CabinTypeType[];
  cabinCategories: any[];
  close: () => void;
  setIsCreateCustomerVisible: (visible: boolean) => void;
  onBookingCreated: () => void;
  createdCustomer: Customer | null;
};

const BookingStepper: React.FC<BookingStepperProps> = ({
  cabinTypes,
  cabinCategories,
  close,
  setIsCreateCustomerVisible,
  onBookingCreated,
  createdCustomer,
}) => {
  const {
    setStep,
    nextStep,
    prevStep,
    setCabinTypes,
    setCabinCategories,
    resetBooking,
    setPassengerField,
    setCreatedCustomer,
  } = useBookingActions();

  useEffect(() => {
    if (!cabinCategories?.length) return;

    setCabinTypes(cabinTypes);
    setCabinCategories(cabinCategories);
    setCreatedCustomer(createdCustomer);
  }, [cabinTypes, cabinCategories]);

  const {
    step: activeStep,
    cabinCategory,
    cabinType,
    paymentPlan,
    cabinNumber,
    bedConfig,
    installments: numberOfInstallments,
    isSingleRoom,
    carbonOffset,
    youChooseYourCabin,
    passenger,
  } = useAppSelector(selectBooking);

  const [selectedUser, setSelectedUser] = useState(null);
  const validateGenders = useValidateGenders({ cabinType, selectedUser });

  const validateStep = () => {
    switch (activeStep) {
      case 0:
        let rule = cabinType && cabinCategory && cabinNumber && paymentPlan && cabinNumber && bedConfig;
        if (paymentPlan?.value === "INSTALLMENTS") {
          rule = rule && numberOfInstallments;
        }

        return !!rule;
      case 1:
        console.log(passenger.first_name ,
          passenger.last_name ,
          passenger.address_first ,
          passenger.city ,
          passenger.country ,
          passenger.email ,
          passenger.dob ,
          passenger.gender ,
          passenger.payment_method ,
          passenger.terms_n_cons ,
          (isSingleRoom ? passenger.single_t_agreement : true));
        const foo = (
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
            (isSingleRoom ? passenger.single_t_agreement : true)
          ) && validateGenders(true)
        );
        return foo;
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
  ]);

  const [createLoader, setCreateLoader] = useState(false);

  const handleClose = () => {
    resetBooking();
    close();
  };

  useEffect(() => {
    setIsCreateCustomerVisible(activeStep === 1);
  }, [activeStep, setIsCreateCustomerVisible]);

  const { showSnackbar } = useSnackbar();

  const steps = ["Select Cabin", "Passenger Details", "Special Request", "Discounts/Addons", "Confirm & Submit"];

  const onChange = (field: string, value: any) => {
    setPassengerField(field, value);
  };

  const handleSubmit = () => {
    const { capacity, cabin_category_spec_id, id: cabinCategoryId } = cabinCategory;

    const payload = {
      cabin_number: cabinNumber,
      cabin_capacity: capacity,
      cabin_category_id: cabinCategoryId,
      cabin_category_spec_id: cabin_category_spec_id,
      payment_plan: paymentPlan.value,
      number_of_installments: numberOfInstallments?.value,
      bed_configuration: bedConfig?.value,
      carbon_offset: carbonOffset,
      you_choose_your_cabin: youChooseYourCabin,
      passenger: {
        id: passenger.id,
        first_name: passenger.first_name,
        middle_name: passenger.middle_name,
        last_name: passenger.last_name,
        dob: passenger.dob,
        gender: passenger.gender,
        citizenship: passenger.citizenship,
        survivor_number: passenger.survivor_number,
        email: passenger.email,
        phone: passenger.phone,
        address_first: passenger.address_first,
        address_second: passenger.address_second,
        city: passenger.city,
        state: passenger.state,
        postal_code: passenger.postal_code,
        country: passenger.country,
        emergency_c_name: passenger.emergency_c_name,
        emergency_c_phone: passenger.emergency_c_phone,
        payment_method: passenger.payment_method,
        special_request: passenger.special_request,
        special_options: passenger.special_options,
        dietary_preferences: passenger.dietary_preferences,
        lead_passenger: passenger.lead_passenger,
        travel_info: passenger.travel_info,
        terms_n_cons: true,
        single_t_agreement: passenger.single_t_agreement,
        newsletter: passenger.newsletter,
        passenger_allocated_cost: passenger.passenger_allocated_cost,
        passenger_balance: passenger.passenger_balance,
      },
    };

    if (!payload.cabin_number || !payload.passenger.first_name || !payload.passenger.email) {
      showSnackbar("Please fill all required fields!", "error");
      return;
    }

    setCreateLoader(true);

    router.post(route("bookings.createManual", { id: 1 }), payload, {
      onSuccess: () => {
        showSnackbar("Booking created successfully!", "success");
        setStep(0);
        if (onBookingCreated) onBookingCreated();
        handleClose();
      },
      onError: (errors) => {
        console.error("Error creating booking:", errors);

        // Extract meaningful error messages
        const errorMessages = Object.values(errors)
          .flat()
          .filter((msg) => msg?.trim()); // Remove empty values
        const errorMessage = errorMessages.length ? errorMessages[0] : "";

        // Conditionally add line breaks only if there's a meaningful error
        const message = errorMessage
          ? `Failed to create booking. Please try again.\n\n${errorMessage}`
          : "Failed to create booking. Please try again.";

        showSnackbar(message, "error");
      },
      onFinish: () => {
        setCreateLoader(false);
      },
    });
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
              loading={createLoader}
              loadingPosition="start"
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
      <LoadingOverlay open={createLoader} />
    </Box>
  );
};

export default BookingStepper;
