import { useAppDispatch } from "@/store/hooks";
import {
  setStep,
  nextStep,
  prevStep,
  setCabin,
  setCabinType,
  setCabinCategory,
  setPassengerField,
  toggleAddon,
  resetBooking,
  setCabinTypes,
  setCabinCategories,
  setCabinNumber,
  setAvailableDecks,
  setAvailableCabins,
  setPaymentPlan,
  setBedConfig,
  setPassenger,
  setOnlyAccessible,
  setSelectedLocation,
  setSelectedDeck,
  setNumberOfInstallments,
  setAdvancedFilters,
  setLoading,
  setCreatedCustomer,
  setSelectedUser,
  setTabValue,
  setPriceCalc,
} from "@/store/slices/bookingSlice";
import { CabinType } from "@/types/cabin";
import { Passenger } from "@/interfaces/Passenger";
import { Nullable } from "@/interfaces/utils";
import { PriceCalc } from "@/Pages/Bookings/partials/BookingStepper/step4";

export function useBookingActions() {
  const dispatch = useAppDispatch();

  return {
    setStep: (step: number) => dispatch(setStep(step)),
    nextStep: () => dispatch(nextStep()),
    prevStep: () => dispatch(prevStep()),
    setPaymentPlan: (paymentPlan: string) => dispatch(setPaymentPlan(paymentPlan)),
    setBedConfig: (bedConfig: any) => dispatch(setBedConfig(bedConfig)),
    setSelectedLocation: (location: any) => dispatch(setSelectedLocation(location)),
    setSelectedDeck: (deck: any) => dispatch(setSelectedDeck(deck)),
    setNumberOfInstallments: (payload: any) => dispatch(setNumberOfInstallments(payload)),
    setCabin: (payload: any) => dispatch(setCabin(payload)),
    setCabinTypes: (payload: CabinType[]) => dispatch(setCabinTypes(payload)),
    setCabinType: (payload: CabinType | null) => dispatch(setCabinType(payload)),
    setCabinCategories: (payload: any[] | null) => dispatch(setCabinCategories(payload)),
    setCabinCategory: (payload: any | null) => dispatch(setCabinCategory(payload)),
    setCabinNumber: (payload: string | null) => dispatch(setCabinNumber(payload)),
    setOnlyAccessible: (payload: boolean) => dispatch(setOnlyAccessible(payload)),
    setAdvancedFilters: (payload: boolean) => dispatch(setAdvancedFilters(payload)),
    setAvailableDecks: (payload: number[] | null) => dispatch(setAvailableDecks(payload)),
    setAvailableCabins: (payload: any[] | null) => dispatch(setAvailableCabins(payload)),
    setPassenger: (payload: Nullable<Passenger>) => dispatch(setPassenger(payload)),
    setPassengerField: (field: string, value: any) =>
      dispatch(setPassengerField({ field, value })),
    toggleAddon: (addon: "carbonOffset" | "youChooseYourCabin") =>
      dispatch(toggleAddon(addon)),
    resetBooking: () => dispatch(resetBooking()),
    setLoading: (payload: boolean) => dispatch(setLoading(payload)),
    setCreatedCustomer: (payload: any) => dispatch(setCreatedCustomer(payload)),
    setSelectedUser: (payload: any) => dispatch(setSelectedUser(payload)),
    setTabValue: (payload: number) => dispatch(setTabValue(payload)),
    setPriceCalc: (payload: Nullable<PriceCalc>) => dispatch(setPriceCalc(payload)),
  };
}
