import { RootState } from "@/store";

export const selectBooking = (state: RootState) => state.booking;

export const selectStep = (state: RootState) => state.booking.step;
export const selectCabinType = (state: RootState) => state.booking.cabinType;
export const selectCabinCategory = (state: RootState) => state.booking.cabinCategory;
export const selectPaymentPlan = (state: RootState) => state.booking.paymentPlan;
export const selectCabinNumber = (state: RootState) => state.booking.cabinNumber;
export const selectBedConfig = (state: RootState) => state.booking.bedConfig;
export const selectInstallments = (state: RootState) => state.booking.installments;
export const selectPassenger = (state: RootState) => state.booking.passenger;
export const selectAddons = (state: RootState) => state.booking.addons;
