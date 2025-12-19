import { useAppDispatch } from "@/store/hooks";
import {
  setStep,
  setCabin,
  setPassengerField,
  toggleAddon,
  resetBooking,
} from "@/store/slices/bookingSlice";

export function useBookingActions() {
  const dispatch = useAppDispatch();

  return {
    setStep: (step: number) => dispatch(setStep(step)),
    setCabin: (payload: any) => dispatch(setCabin(payload)),
    setPassengerField: (field: string, value: any) =>
      dispatch(setPassengerField({ field, value })),
    toggleAddon: (addon: "carbonOffset" | "youChooseYourCabin") =>
      dispatch(toggleAddon(addon)),
    resetBooking: () => dispatch(resetBooking()),
  };
}
