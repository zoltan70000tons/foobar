import { renderHook, act } from "@testing-library/react";
import { useBookingActions } from "./useBookingActions";
import { bookingSlice } from "@/store/slices/bookingSlice";
import * as hooks from "@/store/hooks";
import { CabinType, CabinCategory } from "@/types/cabin";
import { Passenger } from "@/interfaces/Passenger";
import { BookingUser } from "@/interfaces/User";
import { Customer } from "@/interfaces/Customer";
import { PriceCalc } from "@/Pages/Bookings/partials/BookingStepper/step4";

const mockDispatch = jest.fn();

jest.spyOn(hooks, "useAppDispatch").mockReturnValue(mockDispatch);

describe("useBookingActions", () => {
  beforeEach(() => {
    mockDispatch.mockClear();
  });

  it("dispatches step actions", () => {
    const { result } = renderHook(() => useBookingActions());

    act(() => result.current.setStep(2));
    act(() => result.current.nextStep());
    act(() => result.current.prevStep());

    expect(mockDispatch).toHaveBeenNthCalledWith(
      1,
      bookingSlice.actions.setStep(2)
    );
    expect(mockDispatch).toHaveBeenNthCalledWith(
      2,
      bookingSlice.actions.nextStep()
    );
    expect(mockDispatch).toHaveBeenNthCalledWith(
      3,
      bookingSlice.actions.prevStep()
    );
  });

  it("dispatches payment & installment actions", () => {
    const { result } = renderHook(() => useBookingActions());

    act(() => result.current.setPaymentPlan("monthly"));
    act(() =>
      result.current.setNumberOfInstallments({ id: 1, value: 6 })
    );

    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setPaymentPlan("monthly")
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setNumberOfInstallments({ id: 1, value: 6 })
    );
  });

  it("dispatches cabin-related actions", () => {
    const { result } = renderHook(() => useBookingActions());

    const cabinTypes = [] as CabinType[];
    const cabinCategories = [] as CabinCategory[];

    act(() => result.current.setCabin({ id: "c1" }));
    act(() => result.current.setCabinTypes(cabinTypes));
    act(() => result.current.setCabinType(null));
    act(() => result.current.setCabinCategories(cabinCategories));
    act(() => result.current.setCabinCategory(null));
    act(() => result.current.setCabinNumber("A101"));
    act(() => result.current.setOnlyAccessible(true));
    act(() => result.current.setAdvancedFilters(true));
    act(() => result.current.setAvailableDecks([1, 2]));
    act(() => result.current.setAvailableCabins([]));

    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabin({ id: "c1" })
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabinTypes(cabinTypes)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabinType(null)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabinCategories(cabinCategories)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabinCategory(null)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCabinNumber("A101")
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setOnlyAccessible(true)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setAdvancedFilters(true)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setAvailableDecks([1, 2])
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setAvailableCabins([])
    );
  });

  it("dispatches passenger actions", () => {
    const { result } = renderHook(() => useBookingActions());

    const passenger = { firstName: "John" } as Passenger;

    act(() => result.current.setPassenger(passenger));
    act(() =>
      result.current.setPassengerField("lastName", "Doe")
    );

    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setPassenger(passenger)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setPassengerField({
        field: "lastName",
        value: "Doe",
      })
    );
  });

  it("dispatches addon toggles", () => {
    const { result } = renderHook(() => useBookingActions());

    act(() => result.current.toggleAddon("carbonOffset"));
    act(() => result.current.toggleAddon("youChooseYourCabin"));

    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.toggleAddon("carbonOffset")
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.toggleAddon("youChooseYourCabin")
    );
  });

  it("dispatches user, customer & misc actions", () => {
    const { result } = renderHook(() => useBookingActions());

    const user = {} as BookingUser;
    const customer = {} as Customer;
    const priceCalc = {} as PriceCalc;

    act(() => result.current.setCreatedCustomer(customer));
    act(() => result.current.setEventId(99));
    act(() => result.current.setSelectedUser(user));
    act(() => result.current.setTabValue(1));
    act(() => result.current.setPriceCalc(priceCalc));
    act(() => result.current.setLoading(true));
    act(() => result.current.resetBooking());

    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setCreatedCustomer(customer)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setEventId(99)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setSelectedUser(user)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setTabValue(1)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setPriceCalc(priceCalc)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.setLoading(true)
    );
    expect(mockDispatch).toHaveBeenCalledWith(
      bookingSlice.actions.resetBooking()
    );
  });
});
