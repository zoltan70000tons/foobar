import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { Passenger } from "@/interfaces/Passenger";
import { Customer } from "@/interfaces/Customer";

type BookingState = {
  step: number;

  cabinType: any | null;
  cabinCategory: any | null;
  cabinNumber: string | null;
  isSingleRoom: boolean;
  availableDecks: string[];

  paymentPlan: any | null;
  installments: number | null;
  bedConfig: any | null;

  passenger: Passenger;
  createdCustomer: Customer | null;

  addons: {
    carbonOffset: boolean;
    youChooseYourCabin: boolean;
  };
};

const initialState: BookingState = {
  step: 0,

  cabinType: null,
  cabinCategory: null,
  cabinNumber: null,
  isSingleRoom: false;
  availableDecks: [];

  paymentPlan: null,
  installments: null,
  bedConfig: null,

  passenger: {
    id: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    dob: "",
    gender: "",
    citizenship: "",
    survivor_number: "",
    email: "",
    phone: "",
    address_first: "",
    address_second: "",
    city: "",
    state: "",
    postal_code: "",
    country: "",
    emergency_c_name: "",
    emergency_c_phone: "",
    payment_method: "",
    special_request: "",
    lead_passenger: true,
    travel_info: false,
    terms_n_cons: true,
    single_t_agreement: false,
    newsletter: false,
    passenger_allocated_cost: "",
    passenger_balance: "",
  },

  createdCustomer: null,

  addons: {
    carbonOffset: false,
    youChooseYourCabin: false,
  },
};

const bookingSlice = createSlice({
  name: "booking",
  initialState,
  reducers: {
    resetBooking: () => initialState,

    setStep(state, action: PayloadAction<number>) {
      state.step = action.payload;
    },

    setCabin(state, action: PayloadAction<Partial<BookingState>>) {
      Object.assign(state, action.payload);
    },

    setAvailableDecks(state, action: PayloadAction<string[]>) {
      state.availableDecks = action.payload;
    },

    resetCabinSelection(state) {
      state.cabinNumber = null;
      state.isSingleRoom = false;
      state.availableDecks = [];
    },

    setPassengerField(
      state,
      action: PayloadAction<{ field: string; value: any }>
    ) {
      (state.passenger as any)[action.payload.field] = action.payload.value;
    },

    setCreatedCustomer(state, action) {
      state.createdCustomer = action.payload;
    },

    toggleAddon(state, action: PayloadAction<"carbonOffset" | "youChooseYourCabin">) {
      state.addons[action.payload] = !state.addons[action.payload];
    },
  },
});

export const {
  resetBooking,
  setStep,
  setCabin,
  setPassengerField,
  setCreatedCustomer,
  toggleAddon,
  setAvailableDecks,
  resetCabinSelection,
} = bookingSlice.actions;

export default bookingSlice.reducer;
