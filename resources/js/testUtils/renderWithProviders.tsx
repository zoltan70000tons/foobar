import React, { PropsWithChildren } from "react";
import { render } from "@testing-library/react";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import bookingReducer from "@/store/slices/bookingSlice";

export function renderWithProviders(
  ui: React.ReactElement,
  {
    preloadedState,
    store = configureStore({
      reducer: {
        booking: bookingReducer,
      },
      preloadedState,
    }),
  }: {
    preloadedState?: any;
    store?: any;
  } = {}
) {
  return {
    store,
    ...render(<Provider store={store}>{ui}</Provider>),
  };
}
