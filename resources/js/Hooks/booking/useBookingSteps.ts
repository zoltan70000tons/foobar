import { useState } from "react";
import { useAppSelector, useAppDispatch } from "@/store/hooks";
import { setStep } from "@/store/slices/bookingSlice";

export function useBookingSteps() {
  const dispatch = useAppDispatch();
  const step = useAppSelector((s) => s.booking.step);
  const [tabValue, setTabValue] = useState(step);

  const handleTabChange = (_: any, newValue: number) => {
    setTabValue(newValue);
    dispatch(setStep(newValue));
  };

  return { tabValue, handleTabChange };
}
