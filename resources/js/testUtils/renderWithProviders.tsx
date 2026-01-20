import React from "react";
import { render } from "@testing-library/react";
import { Provider } from "react-redux";
import { configureStore, type EnhancedStore } from "@reduxjs/toolkit";
import bookingReducer from "@/store/slices/bookingSlice";
import type { RootState } from "@/store";

type TestStore = EnhancedStore<RootState>;

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
    preloadedState?: Partial<RootState>;
    store?: TestStore;
  } = {},
) {
  return {
    store,
    ...render(<Provider store={store}>{ui}</Provider>),
  };
}
