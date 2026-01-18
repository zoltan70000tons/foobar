import { createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { PriceCalc } from "@/types/booking";

type FetchBookingFinalPriceParams = {
  event_id: number;

  cabin_number: string | null;
  cabin_capacity: number;
  cabin_category_id: number;
  cabin_type_id: number;
  cabin_category_spec_id: number;

  payment_plan: string;
  number_of_installments?: number | null;

  carbon_offset: boolean;
  you_choose_your_cabin: boolean;

  passenger: {
    id: number | null;
    gender: string | null;
    survivor_number: string | null;
    payment_method: string | null;
  };
}

interface BookingFinalPriceResponse {
  priceCalc?: PriceCalc;
  error?: string;
}

export const fetchBookingFinalPrice = createAsyncThunk<
  BookingFinalPriceResponse,
  FetchBookingFinalPriceParams,
  { rejectValue: string }
  >(
  "booking/fetchBookingFinalPrice",
  async (params, { rejectWithValue }) => {
    try {
      const { event_id, ...queryParams } = params;

      const response = await axios.get(
        route("bookings.getBookingFinalCost", { event: event_id }),
        { params: queryParams }
      );

      return response.data;
    } catch (error: unknown) {
      return rejectWithValue(
        error.response?.data?.error || "Couldn't get booking final price"
      );
    }
  }
);
