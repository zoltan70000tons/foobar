import { createAsyncThunk } from "@reduxjs/toolkit";
import { RootState } from "@/store";

export const submitBooking = createAsyncThunk<
  void,
  void,
  { state: RootState; rejectValue: string }
  >(
  "booking/submit",
  async (_, { getState, rejectWithValue }) => {
    const { booking } = getState();
    const {
      cabinCategory,
      cabinNumber,
      paymentPlan,
      installments,
      bedConfig,
      addons,
      passenger,
    } = booking;

    if (!cabinNumber || !passenger.first_name || !passenger.email) {
      return rejectWithValue("Please fill all required fields!");
    }

    const {
      capacity,
      cabin_category_spec_id,
      id: cabinCategoryId,
    } = cabinCategory;

    const payload = {
      cabin_number: cabinNumber,
      cabin_capacity: capacity,
      cabin_category_id: cabinCategoryId,
      cabin_category_spec_id,
      payment_plan: paymentPlan?.value,
      number_of_installments: installments?.value,
      bed_configuration: bedConfig?.value,
      carbon_offset: addons.carbonOffset,
      you_choose_your_cabin: addons.youChooseYourCabin,
      passenger: {
        ...passenger,
        terms_n_cons: true,
      },
    };

    try {
      await axios.post(
        route("bookings.createManual", { id: 1 }),
        payload
      );
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        "Failed to create booking. Please try again.";

      return rejectWithValue(message);
    }
  }
);
