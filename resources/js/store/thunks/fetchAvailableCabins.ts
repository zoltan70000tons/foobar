import { createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";

// Define the payload you need to pass
interface FetchAvailableCabinsParams {
  type_id: number | null;
  category_id: number | null;
  deck: number | null;
  location: number | null;
  accessible: boolean;
}

// Async thunk
export const fetchAvailableCabins = createAsyncThunk<
  any[], // Return type (fulfilled payload)
  FetchAvailableCabinsParams, // Argument type
  { rejectValue: string } // rejectWithValue type
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
