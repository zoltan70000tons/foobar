import React from "react";
import { render, screen, fireEvent } from "@testing-library/react";
import { BookingStepperStepThree } from "@/Pages/Bookings/partials/BookingStepper/step3";
import * as hooks from "@/Hooks/booking/useBookingActions";
import * as redux from "@/store/hooks";

import "@testing-library/jest-dom";
import { RootState } from "@/store";

const toggleAddonMock = jest.fn();

jest.spyOn(hooks, "useBookingActions").mockReturnValue({
  toggleAddon: toggleAddonMock,
});

jest.spyOn(redux, "useAppSelector").mockImplementation(
  (selector: (state: RootState) => unknown) =>
    selector({
      booking: {
        addons: {
          carbonOffset: false,
          youChooseYourCabin: true,
        },
      },
    } as RootState)
);

describe("BookingStepperStepThree", () => {
  beforeEach(() => {
    toggleAddonMock.mockClear();
  });

  it("renders both addon checkboxes", () => {
    render(<BookingStepperStepThree />);

    expect(screen.getByLabelText("Carbon Offset")).toBeInTheDocument();
    expect(screen.getByLabelText("You Choose Your Cabin")).toBeInTheDocument();

    const carbonCheckbox = screen.getByLabelText("Carbon Offset");
    const cabinCheckbox = screen.getByLabelText("You Choose Your Cabin");

    expect(carbonCheckbox.checked).toBe(false);
    expect(cabinCheckbox.checked).toBe(true);
  });

  it("calls toggleAddon when Carbon Offset checkbox is clicked", () => {
    render(<BookingStepperStepThree />);

    const carbonCheckbox = screen.getByLabelText("Carbon Offset");

    fireEvent.click(carbonCheckbox);

    expect(toggleAddonMock).toHaveBeenCalledWith("carbonOffset");
    expect(toggleAddonMock).toHaveBeenCalledTimes(1);
  });

  it("calls toggleAddon when You Choose Your Cabin checkbox is clicked", () => {
    render(<BookingStepperStepThree />);

    const cabinCheckbox = screen.getByLabelText("You Choose Your Cabin");

    fireEvent.click(cabinCheckbox);

    expect(toggleAddonMock).toHaveBeenCalledWith("youChooseYourCabin");
    expect(toggleAddonMock).toHaveBeenCalledTimes(1);
  });
});
