import React from "react";
import { screen, fireEvent } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { BookingStepperStepZero } from "@/Pages/Bookings/partials/BookingStepper/step0";
import { renderWithProviders } from "@/testUtils/renderWithProviders";
import * as actions from "@/Hooks/booking/useBookingActions";

jest.mock("@/Pages/Bookings/partials/CabinSelector", () => () => (
  <div data-testid="cabin-selector" />
));

const actionMocks = {
  setCabinType: jest.fn(),
  setCabinCategory: jest.fn(),
  setAvailableDecks: jest.fn(),
  setAvailableCabins: jest.fn(),
  setPaymentPlan: jest.fn(),
  setBedConfig: jest.fn(),
  setOnlyAccessible: jest.fn(),
  setSelectedLocation: jest.fn(),
  setSelectedDeck: jest.fn(),
  setNumberOfInstallments: jest.fn(),
  setAdvancedFilters: jest.fn(),
};

jest.spyOn(actions, "useBookingActions").mockReturnValue(actionMocks);

const baseBookingState = {
  cabinType: null,
  cabinCategory: null,
  cabinTypes: [
    { id: 1, cabin_type: "Private Cabin" },
    { id: 2, cabin_type: "Single Male" },
  ],
  cabinCategories: [
    {
      id: 10,
      title: "Deluxe",
      price: 1000,
      capacity_description: "2 Guests",
      cabins: [
        { cabin_type: { id: 1 }, status: "AVAILABLE" },
      ],
    },
  ],
  availableDecks: [5, 6],
  deck: null,
  location: null,
  onlyAccessible: false,
  advancedFilters: false,
  paymentPlan: null,
  installments: null,
  bedConfig: null,
  loading: false,
};

describe("BookingStepperStepZero", () => {
  it("renders base fields", () => {
    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(screen.getByLabelText("Cabin Type")).toBeInTheDocument();
    expect(screen.getByLabelText("Cabin Category")).toBeInTheDocument();
    expect(screen.getByText("Advanced Filters")).toBeInTheDocument();
    expect(screen.getByTestId("cabin-selector")).toBeInTheDocument();
    expect(screen.getByLabelText("Payment Plan")).toBeInTheDocument();
    expect(screen.getByLabelText("Bed Configuration")).toBeInTheDocument();
  });

  it("calls setCabinType when selecting cabin type", async () => {
    const user = userEvent.setup();

    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: { booking: baseBookingState },
    });

    const input = screen.getByLabelText("Cabin Type");

    await user.click(input);

    await user.click(screen.getByText("Single Male"));

    expect(actionMocks.setCabinType).toHaveBeenCalledTimes(1);
  });

  it("filters cabin categories by selected cabin type", () => {
    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          cabinType: { id: 1, cabin_type: "Private Cabin" },
        },
      },
    });

    const cabinTypeInput = screen.getByLabelText("Cabin Type");

    expect(cabinTypeInput).toHaveValue("Private Cabin");
  });

  it("dispatches fetchAvailableCabins when cabinType and cabinCategory are set", () => {
    const { store } = renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          cabinType: { id: 1, cabin_type: "Balcony" },
          cabinCategory: { id: 10 },
        },
      },
    });

    expect(store.getState().booking.loading).toBe(true);
  });

  it("enables advanced filters", () => {
    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: { booking: baseBookingState },
    });

    fireEvent.click(screen.getByText("Advanced Filters"));

    expect(actionMocks.setAdvancedFilters).toHaveBeenCalledWith(true);
  });

  it("clears filters when advanced filters are disabled", () => {
    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          advancedFilters: true,
        },
      },
    });

    fireEvent.click(screen.getByText("Clear Filters"));

    expect(actionMocks.setAdvancedFilters).toHaveBeenCalledWith(false);
    expect(actionMocks.setSelectedDeck).toHaveBeenCalledWith(null);
    expect(actionMocks.setSelectedLocation).toHaveBeenCalledWith(null);
    expect(actionMocks.setOnlyAccessible).toHaveBeenCalledWith(false);
  });

  it("shows installments field when payment plan is INSTALLMENTS", () => {
    renderWithProviders(<BookingStepperStepZero />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          paymentPlan: { id: "INSTALLMENTS", value: "INSTALLMENTS" },
        },
      },
    });

    expect(
      screen.getByLabelText("Number of Installments")
    ).toBeInTheDocument();
  });
});
