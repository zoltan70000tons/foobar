import React from "react";
import { render, screen, fireEvent } from "@testing-library/react";
import { BookingStepperStepThree } from "@/Pages/Bookings/partials/BookingStepper/step3";
import * as hooks from "@/Hooks/booking/useBookingActions";
import * as redux from "@/store/hooks";

import "@testing-library/jest-dom";

const toggleAddonMock = jest.fn();

jest.spyOn(hooks, "useBookingActions").mockReturnValue({
  toggleAddon: toggleAddonMock,
});

jest.spyOn(redux, "useAppSelector").mockImplementation((selector: any) =>
  selector({
    booking: {
      addons: {
        carbonOffset: false,
        youChooseYourCabin: true,
      },
    },
  })
);

describe("BookingStepperStepThree", () => {
  beforeEach(() => {
    toggleAddonMock.mockClear();
  });

  it("renders both addon checkboxes", () => {
    render(<BookingStepperStepThree />);

    expect(screen.getByLabelText("Carbon Offset")).toBeInTheDocument();
    expect(screen.getByLabelText("You Choose Your Cabin")).toBeInTheDocument();

    const carbonCheckbox = screen.getByLabelText("Carbon Offset") as HTMLInputElement;
    const cabinCheckbox = screen.getByLabelText("You Choose Your Cabin") as HTMLInputElement;

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
