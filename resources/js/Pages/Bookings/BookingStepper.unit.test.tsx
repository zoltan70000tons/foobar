import React from "react";
import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import BookingStepper from "./BookingStepper";
import { renderWithProviders } from "@/testUtils/renderWithProviders";
import * as actions from "@/Hooks/booking/useBookingActions";
import * as snackbar from "@/Providers/SnackBarAlertProvider";
import * as genderHook from "@/Hooks/booking/useValidateGenders";
import { submitBooking } from "@/store/thunks/submitBooking";
import { AnyAction } from "@reduxjs/toolkit";

jest.mock("@/Pages/Bookings/partials/BookingStepper/step0", () => ({
  BookingStepperStepZero: () => <div>STEP 0</div>,
}));

jest.mock("@/Pages/Bookings/partials/BookingStepper/step1", () => ({
  BookingStepperStepOne: () => <div>STEP 1</div>,
}));

jest.mock("@/Pages/Bookings/partials/BookingStepper/step3", () => ({
  BookingStepperStepThree: () => <div>STEP 3</div>,
}));

jest.mock("@/Pages/Bookings/partials/BookingStepper/step4", () => ({
  BookingStepperStepFour: () => <div>STEP 4</div>,
}));

jest.mock("@/Pages/Bookings/partials/SpecialRequest", () => () => (
  <div>SPECIAL REQUEST</div>
));

jest.mock("@/Components/LoadingOverlay", () => {
  type LoadingOverlayProps = { open: boolean };

  return ({ open }: LoadingOverlayProps) => (open ? <div>LOADING</div> : null);
});

const actionMocks = {
  nextStep: jest.fn(),
  prevStep: jest.fn(),
  setCabinTypes: jest.fn(),
  setCabinCategories: jest.fn(),
  resetBooking: jest.fn(),
  setPassengerField: jest.fn(),
  setCreatedCustomer: jest.fn(),
  setEventId: jest.fn(),
};

jest.spyOn(actions, "useBookingActions").mockReturnValue(actionMocks);

const showSnackbar = jest.fn();

jest.spyOn(snackbar, "useSnackbar").mockReturnValue({ showSnackbar });

jest.spyOn(genderHook, "useValidateGenders").mockReturnValue(jest.fn(() => true));

jest.mock("@/store/thunks/submitBooking", () => {
  const fulfilled: AnyAction & { match: (action: unknown) => boolean } = {
    type: "booking/submit/fulfilled",
    match: (action: unknown) =>
      typeof action === "object" &&
      action !== null &&
      "type" in action &&
      (action as { type: unknown }).type === "booking/submit/fulfilled",
  };

  const rejected: AnyAction & { match: (action: unknown) => boolean } = {
    type: "booking/submit/rejected",
    match: (action: unknown) =>
      typeof action === "object" &&
      action !== null &&
      "type" in action &&
      (action as { type: unknown }).type === "booking/submit/rejected",
  };

  const pending: AnyAction & { match: (action: unknown) => boolean } = {
    type: "booking/submit/pending",
    match: (action: unknown) =>
      typeof action === "object" &&
      action !== null &&
      "type" in action &&
      (action as { type: unknown }).type === "booking/submit/pending",
  };

  const submitBooking: jest.Mock & {
    pending: typeof pending;
    fulfilled: typeof fulfilled;
    rejected: typeof rejected;
  } = Object.assign(
    jest.fn(() => fulfilled),
    { pending, fulfilled, rejected }
  );

  return { submitBooking };
});


const baseProps = {
  cabinTypes: [{ id: 1, cabin_type: "Private Cabin" }],
  cabinCategories: [{ id: 1, title: "Deluxe", event_id: 1 }],
  close: jest.fn(),
  setIsCreateCustomerVisible: jest.fn(),
  onBookingCreated: jest.fn(),
  createdCustomer: null,
  eventId: 1,
};

const baseBookingState = {
  step: 0,
  cabinCategory: null,
  cabinType: null,
  paymentPlan: null,
  cabinNumber: null,
  bedConfig: null,
  installments: null,
  isSingleRoom: false,
  passenger: {},
  selectedUser: null,
  loading: false,
};

describe("BookingStepper", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("initializes booking data on mount", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(actionMocks.setCabinTypes).toHaveBeenCalledWith(baseProps.cabinTypes);
    expect(actionMocks.setCabinCategories).toHaveBeenCalledWith(baseProps.cabinCategories);
    expect(actionMocks.setEventId).toHaveBeenCalledWith(1);
  });

  it("renders correct step component", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: { ...baseBookingState, step: 1 },
      },
    });

    expect(screen.getByText("STEP 1")).toBeInTheDocument();
  });

  it("disables back button on first step", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(screen.getByText("Back")).toBeDisabled();
  });

  it("disables Next button when step is invalid", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(screen.getByText("Next")).toBeDisabled();
  });

  it("calls nextStep when clicking Next", async () => {
    const user = userEvent.setup();

    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          cabinType: {},
          cabinCategory: {},
          paymentPlan: {},
          cabinNumber: "101",
          bedConfig: {},
        },
      },
    });

    await user.click(screen.getByText("Next"));
    expect(actionMocks.nextStep).toHaveBeenCalled();
  });

  it("toggles create customer visibility on step 1", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: { ...baseBookingState, step: 1 },
      },
    });

    expect(baseProps.setIsCreateCustomerVisible).toHaveBeenCalledWith(true);
  });

  it("submits booking successfully", async () => {
    const user = userEvent.setup();

    (submitBooking as jest.Mock).mockReturnValue({
      type: "booking/submit/fulfilled",
    });

    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: { ...baseBookingState, step: 4 },
      },
    });

    await user.click(screen.getByText("Create Booking"));

    expect(showSnackbar).toHaveBeenCalledWith(
      "Booking created successfully!",
      "success"
    );
    expect(actionMocks.resetBooking).toHaveBeenCalled();
    expect(baseProps.onBookingCreated).toHaveBeenCalled();
  });

  it("shows error snackbar on submit failure", async () => {
    const user = userEvent.setup();

    (submitBooking as jest.Mock).mockReturnValue({
      type: "booking/submit/rejected",
      payload: "Failed",
    });

    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: { ...baseBookingState, step: 4 },
      },
    });

    await user.click(screen.getByText("Create Booking"));

    expect(showSnackbar).toHaveBeenCalledWith("Failed", "error");
  });

  it("shows loading overlay when loading", () => {
    renderWithProviders(<BookingStepper {...baseProps} />, {
      preloadedState: {
        booking: { ...baseBookingState, loading: true },
      },
    });

    expect(screen.getByText("LOADING")).toBeInTheDocument();
  });
});
