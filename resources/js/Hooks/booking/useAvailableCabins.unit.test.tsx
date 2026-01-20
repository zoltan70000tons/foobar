/* eslint-disable @typescript-eslint/no-unsafe-assignment */
/* eslint-disable @typescript-eslint/no-unsafe-call */
/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/unbound-method */
/* eslint-disable @typescript-eslint/no-empty-object-type */
import { renderHook, waitFor } from "@testing-library/react";
import axios, { AxiosError } from "axios";
import { useAvailableCabins } from "./useAvailableCabins";
import { Provider } from "react-redux";
import configureStore, { MockStoreEnhanced } from "redux-mock-store";

jest.mock("axios");
const mockedAxios = axios as jest.Mocked<typeof axios>;

declare global {
  var route: (name: string) => string;
}
global.route = (name: string) => `/api/${name}`;

const mockStore = configureStore([]);

interface BookingStateMock {
  booking: {
    cabinType: { id: number; name: string } | null;
    cabinCategory: { id: number; name: string } | null;
  };
}

describe("useAvailableCabins", () => {
  let store: MockStoreEnhanced<BookingStateMock, {}>;

  beforeEach(() => {
    store = mockStore({
      booking: {
        cabinType: { id: 1, name: "Deluxe" },
        cabinCategory: { id: 2, name: "Balcony" },
      },
    });
    jest.clearAllMocks();
  });

  const wrapper: React.FC<{ children: React.ReactNode }> = ({ children }) => (
    <Provider store={store}>{children}</Provider>
  );

  it("fetches cabins successfully", async () => {
    const cabins = [
      { id: 1, deck: 5, number: "501" },
      { id: 2, deck: 6, number: "601" },
    ];

    mockedAxios.get.mockResolvedValueOnce({ data: { cabins } });

    const { result } = renderHook(() => useAvailableCabins(), { wrapper });

    expect(result.current.loading).toBe(true);
    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe(null);

    await waitFor(() => expect(result.current.loading).toBe(false));

    expect(result.current.loading).toBe(false);
    expect(result.current.cabins).toEqual(cabins);
    expect(result.current.error).toBe(null);

    expect(mockedAxios.get).toHaveBeenCalledWith("/api/cabins.available", {
      params: { type_id: 1, category_id: 2 },
      signal: expect.any(AbortSignal),
    });
  });

  it("handles API errors", async () => {
    mockedAxios.get.mockRejectedValueOnce({
      response: { data: { message: "Server error" } },
      isAxiosError: true,
    });

    const { result } = renderHook(() => useAvailableCabins(), { wrapper });

    expect(result.current.loading).toBe(true);
    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe(null);

    await waitFor(() => expect(result.current.loading).toBe(false));

    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe("Server error");
  });

  it("does not fetch if cabinType or cabinCategory is null", () => {
    store = mockStore({
      booking: { cabinType: null, cabinCategory: { id: 2, name: "Balcony" } },
    });

    const wrapperNull: React.FC<{ children: React.ReactNode }> = ({ children }) => (
      <Provider store={store}>{children}</Provider>
    );

    renderHook(() => useAvailableCabins(), { wrapper: wrapperNull });

    expect(mockedAxios.get).not.toHaveBeenCalled();
  });

  it("aborts request on unmount", () => {
    const abortMock = jest.fn();
    class MockAbortController {
      signal = {} as AbortSignal;
      abort = abortMock;
    }
    const originalAbortController = global.AbortController;
    global.AbortController = MockAbortController as unknown as typeof AbortController;

    mockedAxios.get.mockResolvedValue({ data: { cabins: [] } });

    const { unmount } = renderHook(() => useAvailableCabins(), { wrapper });

    unmount();

    expect(abortMock).toHaveBeenCalled();

    global.AbortController = originalAbortController;
  });
});
