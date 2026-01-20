import { createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { BookingCabin, Cabin } from "@/interfaces/Cabin";
import { ApiErrorResponse } from "@/types/axios";

interface FetchAvailableCabinsParams {
  type_id: number | null;
  category_id: number | null;
  deck: number | null;
  location: number | null;
  accessible: boolean;
}

interface FetchAvailableCabinsResponse {
  cabins: BookingCabin[];
}

export const fetchAvailableCabins = createAsyncThunk<
  FetchAvailableCabinsResponse,
  FetchAvailableCabinsParams,
  { rejectValue: string }
  >(
  "booking/fetchAvailableCabins",
  async (params, { rejectWithValue }) => {
    try {
      const response = await axios.get<FetchAvailableCabinsResponse>(
        route("cabins.available"),
        { params }
      );

      return response.data;
    } catch (err: unknown) {
      let message = "Something went wrong.";

      if (axios.isAxiosError<ApiErrorResponse>(err)) {
        message = err.response?.data?.message ?? message;
      }

      return rejectWithValue(message);
    }
  }
);
