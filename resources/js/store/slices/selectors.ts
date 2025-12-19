import { RootState } from "@/store";

export const selectBooking = (s: RootState) => s.booking;
export const selectPassenger = (s: RootState) => s.booking.passenger;
export const selectStep = (s: RootState) => s.booking.step;
