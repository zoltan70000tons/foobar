import { createSlice, Draft, PayloadAction } from "@reduxjs/toolkit";
import { Passenger } from "@/interfaces/Passenger";
import { Customer } from "@/interfaces/Customer";
import { CabinCategory, CabinType } from "@/types/cabin";
import { Nullable } from "@/interfaces/utils";
import { fetchAvailableCabins } from "@/store/thunks/fetchAvailableCabins";
import { fetchBookingFinalPrice } from "@/store/thunks/fetchBookingFinalPrice";
import { submitBooking } from "@/store/thunks/submitBooking";
import { BookingUser } from "@/interfaces/User";
import { BookingCabin, Cabin } from "@/interfaces/Cabin";
import { PriceCalc } from "@/types/booking";

type NullableObj<T> = {
  [K in keyof T]: T[K] | null;
};

type InitPassenger = NullableObj<Passenger>;

type PassengerFieldUpdate<K extends keyof InitPassenger> = {
  field: K;
  value: InitPassenger[K];
};

export type BookingState = {
  step: number;

  cabinType: Nullable<CabinType>;
  cabinCategory: Nullable<CabinCategory>;
  cabinNumber: Nullable<string>;
  isSingleRoom: boolean;
  availableDecks: Nullable<number[]>;
  onlyAccessible: boolean;
  advancedFilters: boolean;
  eventId: number | null;

  paymentPlan: Nullable<{ id: string; value: string }>;
  installments: { id: number; value: number } | null;
  bedConfig: { id: string; value: string } | null;
  deck: number | null;
  location: string | null;

  passenger: InitPassenger;
  createdCustomer: Draft<Customer> | null;
  selectedUser: Draft<BookingUser> | null;

  addons: {
    carbonOffset: boolean;
    youChooseYourCabin: boolean;
  };

  cabinTypes: CabinType[] | null;
  cabinCategories: CabinCategory[] | null;
  availableCabins: BookingCabin[] | null,

  loading: boolean;
  error: Error | string | null;
  tabValue: number;

  priceCalc: Nullable<PriceCalc>;
};

const initialPassenger: InitPassenger = {
  id: null,
  lead_passenger: true,
  passenger_order: null,
  booking_id: null,
  full_name: null,
  email: null,
  passenger_balance: null,
  passenger_allocated_cost: null,
  installment_status: null,
  first_name: "",
  middle_name: null,
  last_name: "",
  dob: null,
  gender: null,
  citizenship: null,
  survivor_number: null,
  phone: null,
  address_first: null,
  address_second: null,
  city: null,
  state: null,
  postal_code: null,
  country: null,
  emergency_c_name: null,
  emergency_c_phone: null,
  payment_method: null,
  special_request: null,
  travel_info: false,
  terms_n_cons: true,
  single_t_agreement: false,
  newsletter: false,
};

const initialState: BookingState = {
  step: 0,

  cabinType: null,
  cabinCategory: null,
  cabinNumber: null,
  isSingleRoom: false,
  availableDecks: null,
  onlyAccessible: false,
  advancedFilters: false,
  eventId: null,

  paymentPlan: null,
  installments: null,
  bedConfig: null,
  deck: null,
  location: null,

  passenger: initialPassenger,

  createdCustomer: null,
  selectedUser: null,

  addons: {
    carbonOffset: false,
    youChooseYourCabin: false,
  },

  cabinTypes: null,
  cabinCategories: null,
  availableCabins: null,

  loading: false,
  error: null,
  tabValue: 0,

  priceCalc: null,
};

export const bookingSlice = createSlice<BookingState>({
  name: "booking",
  initialState,
  reducers: {
    resetBooking: () => initialState,

    setStep(state, action: PayloadAction<number>) {
      state.step = action.payload;
    },

    nextStep(state) {
      state.step += 1;
    },

    prevStep(state) {
      state.step -= 1;
    },

    setPaymentPlan(state, action: PayloadAction<string>) {
      state.paymentPlan = action.payload;
    },

    setBedConfig(state, action: PayloadAction<{ id: string; value: string } | null>) {
      state.bedConfig = action.payload;
    },

    setSelectedDeck(state, action: PayloadAction<number | null>) {
      state.deck = action.payload;
    },

    setSelectedLocation(state, action: PayloadAction<string | null>) {
      state.location = action.payload;
    },

    setNumberOfInstallments(state, action: PayloadAction<{ id: number; value: number } | null>) {
      state.installments = action.payload;
    },

    setAdvancedFilters(state, action: PayloadAction<boolean>) {
      state.advancedFilters = action.payload;
    },

    setCabinTypes(state, action: PayloadAction<CabinType[] | null>) {
      state.cabinTypes = action.payload;
    },

    setCabinType(state, action: PayloadAction<CabinType | null>) {
      state.cabinType = action.payload;
    },

    setCabinCategories(state, action: PayloadAction<CabinCategory[] | null>) {
      state.cabinCategories = action.payload;
    },

    setCabinCategory(state, action: PayloadAction<CabinCategory | null>) {
      state.cabinCategory = action.payload;
    },

    setCabin(state, action: PayloadAction<Partial<BookingState>>) {
      Object.assign(state, action.payload);
    },

    setCabinNumber(state, action: PayloadAction<string>) {
      state.cabinNumber = action.payload;
    },

    setAvailableDecks(state, action: PayloadAction<number[]>) {
      state.availableDecks = action.payload;
    },

    setAvailableCabins(state, action: PayloadAction<BookingCabin[]>) {
      state.availableCabins = action.payload;
    },

    setOnlyAccessible(state, action: PayloadAction<boolean>) {
      state.onlyAccessible = action.payload;
    },

    resetCabinSelection(state) {
      state.cabinNumber = null;
      state.isSingleRoom = false;
      state.availableDecks = [];
    },

    setPassenger(state, action: PayloadAction<Nullable<Passenger>>) {
      state.passenger = action.payload;
    },

    setPassengerField<K extends keyof InitPassenger>(
      state: Draft<BookingState>,
      action: PayloadAction<PassengerFieldUpdate<K>>
    ) {
      const { field, value } = action.payload;
      state.passenger[field] = value;
    },

    setCreatedCustomer(state: Draft<BookingState>, action: PayloadAction<Customer | null>) {
      state.createdCustomer = action.payload;
    },

    setSelectedUser(state: Draft<BookingState>, action: PayloadAction<BookingUser | null>) {
      state.selectedUser = action.payload;
    },

    toggleAddon(
      state: Draft<BookingState>,
      action: PayloadAction<"carbonOffset" | "youChooseYourCabin">
    ) {
      const key = action.payload;
      state.addons[key] = !state.addons[key];
    },

    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },

    setTabValue(state, action: PayloadAction<number>) {
      state.tabValue = action.payload;
    },

    setPriceCalc(state, action: PayloadAction<Nullable<PriceCalc>>) {
      state.priceCalc = action.payload;
    },

    setEventId(state, action: PayloadAction<number>) {
      state.eventId = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
    .addCase(fetchAvailableCabins.pending, (state) => {
      state.loading = true;
      state.error = null;
    })
    .addCase(fetchAvailableCabins.fulfilled, (state, action) => {
      const cabins = action.payload.cabins;
      const decks = Array.isArray(cabins)
        ? [...new Set(cabins.map((cabin) => cabin.deck))].map(Number).sort((a, b) => a - b)
        : [];
      state.loading = false;
      state.availableCabins = cabins as unknown as Draft<BookingCabin>[];
      state.availableDecks = decks;
      state.cabinNumber = null;
    })
    .addCase(fetchAvailableCabins.rejected, (state, action) => {
      state.loading = false;
      state.error = action.payload instanceof Error ? action.payload : new Error(String(action.payload));
      state.availableCabins = null;
      state.cabinCategory = null;
    })
    .addCase(fetchBookingFinalPrice.pending, (state) => {
      state.loading = true;
      state.error = null;
    })
    .addCase(fetchBookingFinalPrice.fulfilled, (state, action) => {
      state.loading = false;
      state.priceCalc = action.payload.priceCalc ?? null;
    })
    .addCase(fetchBookingFinalPrice.rejected, (state, action) => {
      state.loading = false;
      state.error = action.payload instanceof Error ? action.payload : new Error(String(action.payload));
    })
    .addCase(submitBooking.pending, (state) => {
      state.loading = true;
      state.error = null;
    })
    .addCase(submitBooking.fulfilled, (state) => {
      state.loading = false;
    })
    .addCase(submitBooking.rejected, (state, action) => {
      state.loading = false;
      state.error = action.payload instanceof Error ? action.payload : new Error(String(action.payload));
    });
  },
});

export const {
  setLoading,
} = bookingSlice.actions;

export default bookingSlice.reducer;
