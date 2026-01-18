import { renderHook } from "@testing-library/react";
import axios from "axios";
import { useAvailableCabins } from "./useAvailableCabins";
import { Provider } from "react-redux";
import configureStore from "redux-mock-store";

jest.mock("axios");
const mockedAxios = axios as jest.Mocked<typeof axios>;

(global as any).route = (name: string) => `/api/${name}`;

const mockStore = configureStore([]);

describe("useAvailableCabins", () => {
  let store: any;

  beforeEach(() => {
    store = mockStore({
      booking: {
        cabinType: { id: 1, name: "Deluxe" },
        cabinCategory: { id: 2, name: "Balcony" },
      },
    });
    jest.clearAllMocks();
  });

  const wrapper = ({ children }: any) => (
    <Provider store={store}>{children}</Provider>
  );

  it("fetches cabins successfully", async () => {
    const cabins = [
      { id: 1, deck: 5, number: "501" },
      { id: 2, deck: 6, number: "601" },
    ];

    mockedAxios.get.mockResolvedValueOnce({ data: { cabins } });

    const { result, waitForNextUpdate } = renderHook(() => useAvailableCabins(), { wrapper });

    expect(result.current.loading).toBe(true);
    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe(null);

    await waitForNextUpdate();

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

    const { result, waitForNextUpdate } = renderHook(() => useAvailableCabins(), { wrapper });

    expect(result.current.loading).toBe(true);
    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe(null);

    await waitForNextUpdate();

    expect(result.current.loading).toBe(false);
    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe("Server error");
  });

  it("does not fetch if cabinType or cabinCategory is null", async () => {
    store = mockStore({
      booking: { cabinType: null, cabinCategory: { id: 2, name: "Balcony" } },
    });

    const wrapperNull = ({ children }: any) => (
      <Provider store={store}>{children}</Provider>
    );

    renderHook(() => useAvailableCabins(), { wrapper: wrapperNull });

    expect(mockedAxios.get).not.toHaveBeenCalled();
  });

  it("aborts request on unmount", async () => {
    const cabins = [{ id: 1, deck: 5, number: "501" }];

    const abortMock = jest.fn();
    const originalAbortController = global.AbortController;

    global.AbortController = jest.fn(() => ({
      signal: {},
      abort: abortMock,
    })) as any;

    mockedAxios.get.mockResolvedValue({ data: { cabins } });

    const { unmount } = renderHook(() => useAvailableCabins(), { wrapper });

    unmount();

    expect(abortMock).toHaveBeenCalled();

    global.AbortController = originalAbortController;
  });

});
