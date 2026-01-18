import React from "react";
import { render, screen, fireEvent } from "@testing-library/react";
import { BookingStepperStepFour, PriceCalc } from "@/Pages/Bookings/partials/BookingStepper/step4";
import { renderWithProviders } from "@/testUtils/renderWithProviders";
import { fetchBookingFinalPrice } from "@/store/thunks/fetchBookingFinalPrice";

jest.mock("@/Helpers/CellValue", () => ({ children }: any) => <span>{children}</span>);

jest.mock("@/Hooks/booking/useBookingActions", () => ({
  useBookingActions: () => ({
    setTabValue: jest.fn(),
  }),
}));

const baseBookingState = {
  passenger: {
    id: 1,
    first_name: "John",
    last_name: "Doe",
    email: "john@example.com",
    phone: "1234567890",
    gender: "Male",
    address_first: "123 Street",
    city: "Cityville",
    country: "USA",
    payment_method: "Credit Card",
    survivor_number: null,
  },
  tabValue: 0,
  addons: { carbonOffset: true, youChooseYourCabin: false },
  cabinType: { id: 1, cabin_type: "Balcony" },
  cabinCategory: {
    id: 2,
    title: "Deluxe",
    price: 1000,
    capacity: 2,
    event_id: 123,
    cabin_category_spec_id: 5,
  },
  cabinNumber: "501",
  paymentPlan: { id: 1, value: "Full" },
  installments: { id: 1, value: 2 },
  loading: false,
  priceCalc: {
    extras: 50,
    save: "10",
    total: 2050,
    totalPassenger: 1025,
  },
};

describe("BookingStepperStepFour", () => {
  it("renders cabin details correctly", () => {
    renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(screen.getByText("Confirm Booking")).toBeInTheDocument();
    expect(screen.getByText("Balcony")).toBeInTheDocument();
    expect(screen.getByText("Deluxe")).toBeInTheDocument();
    expect(screen.getByText("501")).toBeInTheDocument();
    expect(
      screen.getByText("Total with Tax After Discounts and Add-Ons:")
    ).toBeInTheDocument();
  });

  it("dispatches fetchBookingFinalPrice on mount", () => {
    const { store } = renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: { booking: baseBookingState },
    });

    const actions = store.getState();

    expect(actions.booking.loading).toBe(true);
  });

  it("renders lead passenger info", () => {
    renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: {
        booking: { ...baseBookingState, tabValue: 1 },
      },
    });

    expect(screen.getByText("John Doe")).toBeInTheDocument();
    expect(screen.getByText("john@example.com")).toBeInTheDocument();
  });

  it("renders payment info", () => {
    renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: {
        booking: { ...baseBookingState, tabValue: 2 },
      },
    });

    expect(screen.getByText("Payment Plan:")).toBeInTheDocument();
    expect(screen.getByText("Full")).toBeInTheDocument();
  });

  it("shows error when cabin details are missing", () => {
    renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: {
        booking: { ...baseBookingState, cabinType: null, cabinCategory: null },
      },
    });

    expect(
      screen.getByText("Unable to load cabin details")
    ).toBeInTheDocument();
  });

  it("shows error when passenger is missing", () => {
    renderWithProviders(<BookingStepperStepFour />, {
      preloadedState: {
        booking: { ...baseBookingState, passenger: null, tabValue: 1 },
      },
    });

    expect(
      screen.getByText("No passenger selected")
    ).toBeInTheDocument();
  });
});
