import React from "react";
import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import axios from "axios";
import { BookingStepperStepOne } from "@/Pages/Bookings/partials/BookingStepper/step1";
import { renderWithProviders } from "@/testUtils/renderWithProviders";
import * as actions from "@/Hooks/booking/useBookingActions";
import * as snackbar from "@/Providers/SnackBarAlertProvider";
import * as genderHook from "@/Hooks/booking/useValidateGenders";

jest.mock("axios");
const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock("@/Components/Country", () => (props: any) => (
  <input data-testid={`country-${props.name}`} />
));

jest.mock("@/Components/PhoneNumber", () => (props: any) => (
  <input data-testid={`phone-${props.name}`} />
));

jest.spyOn(snackbar, "useSnackbar").mockReturnValue({
  showSnackbar: jest.fn(),
});

jest.spyOn(genderHook, "useValidateGenders").mockReturnValue(jest.fn(() => true));

const actionMocks = {
  setLoading: jest.fn(),
  setPassenger: jest.fn(),
  setPassengerField: jest.fn(),
  setSelectedUser: jest.fn(),
};

jest.spyOn(actions, "useBookingActions").mockReturnValue(actionMocks);

const baseBookingState = {
  cabinCategory: { event_id: 99 },
  cabinType: { id: 1 },
  paymentPlan: { id: "PAY_IN_FULL" },
  isSingleRoom: false,
  passenger: null,
  loading: false,
  createdCustomer: null,
  selectedUser: null,
};

const mockUser = {
  id: 1,
  first_name: "John",
  last_name: "Doe",
  email: "john@example.com",
  survivor_number: "SN123",
  gender: "M",
  has_booking: false,
};

describe("BookingStepperStepOne", () => {
  it("renders lead passenger form", () => {
    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: { booking: baseBookingState },
    });

    expect(
      screen.getByText("Lead Passenger Details")
    ).toBeInTheDocument();

    expect(
      screen.getByLabelText("Search by Email, Name or Survivor Number")
    ).toBeInTheDocument();

    expect(screen.getByText("SELECT CUSTOMER")).toBeInTheDocument();
  });

  it("fetches passenger suggestions when typing", async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: [mockUser] });

    const user = userEvent.setup();

    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: { booking: baseBookingState },
    });

    const input = screen.getByLabelText(
      "Search by Email, Name or Survivor Number"
    );

    await user.type(input, "Joh");

    expect(mockedAxios.get).toHaveBeenCalledWith(
      "/passengers/search",
      expect.objectContaining({
        params: expect.objectContaining({
          query: "Joh",
          eventId: 99,
        }),
      })
    );
  });

  it("selects user from autocomplete suggestions", async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: [mockUser] });

    const user = userEvent.setup();

    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: { booking: baseBookingState },
    });

    const input = screen.getByLabelText(
      "Search by Email, Name or Survivor Number"
    );

    await user.type(input, "Joh");
    await user.click(
      screen.getByText(/John Doe \(john@example.com\)/)
    );

    expect(actionMocks.setSelectedUser).toHaveBeenCalledWith(mockUser);
  });

  it("fills passenger when SELECT CUSTOMER is clicked", async () => {
    const user = userEvent.setup();

    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          selectedUser: mockUser,
        },
      },
    });

    await user.click(screen.getByText("SELECT CUSTOMER"));

    expect(actionMocks.setPassenger).toHaveBeenCalled();
  });

  it("auto-fills passenger when createdCustomer is set", () => {
    const showSnackbar = jest.fn();

    jest.spyOn(snackbar, "useSnackbar").mockReturnValueOnce({
      showSnackbar,
    });

    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          createdCustomer: mockUser,
        },
      },
    });

    expect(actionMocks.setPassenger).toHaveBeenCalled();
    expect(showSnackbar).toHaveBeenCalledWith(
      "New customer created and selected.",
      "success"
    );
  });

  it("shows edit customer alert when passenger exists", () => {
    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          passenger: { id: 1 },
        },
      },
    });

    expect(
      screen.getByText(/If you need to update any passenger information/)
    ).toBeInTheDocument();
  });

  it("shows STA checkbox when single room", () => {
    renderWithProviders(<BookingStepperStepOne />, {
      preloadedState: {
        booking: {
          ...baseBookingState,
          isSingleRoom: true,
          passenger: { id: 1 },
        },
      },
    });

    expect(screen.getByText("STA")).toBeInTheDocument();
  });

});
