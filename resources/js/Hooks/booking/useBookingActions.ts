import { useAppDispatch } from "@/store/hooks";
import { bookingSlice } from "@/store/slices/bookingSlice";
import { CabinCategory, CabinType } from "@/types/cabin";
import { Passenger } from "@/interfaces/Passenger";
import { Nullable } from "@/interfaces/utils";
import { PriceCalc } from "@/types/booking";
import { BookingUser } from "@/interfaces/User";
import { Customer } from "@/interfaces/Customer";
import { BookingCabin } from "@/interfaces/Cabin";

export function useBookingActions() {
  const dispatch = useAppDispatch();
  const actions = bookingSlice.actions;

  return {
    setStep: (step: number) => dispatch(actions.setStep(step)),
    nextStep: () => dispatch(actions.nextStep()),
    prevStep: () => dispatch(actions.prevStep()),
    setPaymentPlan: (paymentPlan: string) => dispatch(actions.setPaymentPlan(paymentPlan)),
    setBedConfig: (bedConfig: { id: string; value: string }) => dispatch(actions.setBedConfig(bedConfig)),
    setSelectedLocation: (location: string | null) => dispatch(actions.setSelectedLocation(location)),
    setSelectedDeck: (deck: number | null) => dispatch(actions.setSelectedDeck(deck)),
    setNumberOfInstallments: (payload: { id: number; value: number }) => dispatch(actions.setNumberOfInstallments(payload)),
    setCabin: (payload: BookingCabin) => dispatch(actions.setCabin(payload)),
    setCabinTypes: (payload: CabinType[]) => dispatch(actions.setCabinTypes(payload)),
    setCabinType: (payload: CabinType | null) => dispatch(actions.setCabinType(payload)),
    setCabinCategories: (payload: CabinCategory[] | null) => dispatch(actions.setCabinCategories(payload)),
    setCabinCategory: (payload: CabinCategory | null) => dispatch(actions.setCabinCategory(payload)),
    setCabinNumber: (payload: string | undefined) => dispatch(actions.setCabinNumber(payload)),
    setOnlyAccessible: (payload: boolean) => dispatch(actions.setOnlyAccessible(payload)),
    setAdvancedFilters: (payload: boolean) => dispatch(actions.setAdvancedFilters(payload)),
    setAvailableDecks: (payload: number[] | undefined) => dispatch(actions.setAvailableDecks(payload)),
    setAvailableCabins: (payload: BookingCabin[] | undefined) => dispatch(actions.setAvailableCabins(payload)),
    setPassenger: (payload: Nullable<Passenger>) => dispatch(actions.setPassenger(payload)),
    setPassengerField: <K extends keyof Passenger>(field: K, value: Passenger[K]) =>
      dispatch(actions.setPassengerField({ field, value })),
    toggleAddon: (addon: "carbonOffset" | "youChooseYourCabin") =>
      dispatch(actions.toggleAddon(addon)),
    resetBooking: () => dispatch(actions.resetBooking()),
    setLoading: (payload: boolean) => dispatch(actions.setLoading(payload)),
    setCreatedCustomer: (payload: Customer | null) => dispatch(actions.setCreatedCustomer(payload)),
    setEventId: (payload: number) => dispatch(actions.setEventId(payload)),
    setSelectedUser: (payload: BookingUser) => dispatch(actions.setSelectedUser(payload)),
    setTabValue: (payload: number) => dispatch(actions.setTabValue(payload)),
    setPriceCalc: (payload: Nullable<PriceCalc>) => dispatch(actions.setPriceCalc(payload)),
  };
}
