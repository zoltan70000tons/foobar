import { createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { Cabin } from "@/interfaces/Cabin";

interface FetchAvailableCabinsParams {
  type_id: number | null;
  category_id: number | null;
  deck: number | null;
  location: number | null;
  accessible: boolean;
}

export const fetchAvailableCabins = createAsyncThunk<
  Cabin[],
  FetchAvailableCabinsParams,
  { rejectValue: string }
  >(
  "booking/fetchAvailableCabins",
  async (params, { rejectWithValue }) => {
    try {
      const response = await axios.get(route("cabins.available"), { params });
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data || "Something went wrong");
    }
  }
);
