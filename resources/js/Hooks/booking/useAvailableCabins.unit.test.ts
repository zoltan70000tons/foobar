import { renderHook, waitFor } from "@testing-library/react";
import axios from "axios";
import { useAvailableCabins } from "./useAvailableCabins";
import * as hooks from "@/store/hooks";
import { Cabin } from "@/interfaces/Cabin";

jest.mock("axios");
const mockedAxios = axios as jest.Mocked<typeof axios>;

// Mock Laravel Ziggy route helper (or global route)
(global as any).route = jest.fn(() => "/cabins/available");

// Mock selector
const mockUseAppSelector = jest.spyOn(hooks, "useAppSelector");

describe("useAvailableCabins", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("does not fetch when cabinType or cabinCategory is missing", () => {
    mockUseAppSelector.mockImplementation((selector: any) =>
      selector({
        booking: { cabinType: null, cabinCategory: null },
      })
    );

    const { result } = renderHook(() => useAvailableCabins());

    expect(result.current.cabins).toEqual([]);
    expect(result.current.loading).toBe(false);
    expect(result.current.error).toBeNull();
    expect(mockedAxios.get).not.toHaveBeenCalled();
  });

  it("fetches cabins successfully", async () => {
    mockUseAppSelector.mockImplementation((selector: any) =>
      selector({
        booking: {
          cabinType: { id: 1 },
          cabinCategory: { id: 2 },
        },
      })
    );

    const cabins: Cabin[] = [{ id: 101 } as Cabin];

    mockedAxios.get.mockResolvedValueOnce({
      data: { cabins },
    } as any);

    const { result } = renderHook(() => useAvailableCabins());

    // loading should become true immediately
    expect(result.current.loading).toBe(true);

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(mockedAxios.get).toHaveBeenCalledWith("/cabins/available", {
      params: { type_id: 1, category_id: 2 },
      signal: expect.any(AbortSignal),
    });

    expect(result.current.cabins).toEqual(cabins);
    expect(result.current.error).toBeNull();
  });

  it("sets error when request fails", async () => {
    mockUseAppSelector.mockImplementation((selector: any) =>
      selector({
        booking: {
          cabinType: { id: 1 },
          cabinCategory: { id: 2 },
        },
      })
    );

    mockedAxios.get.mockRejectedValueOnce({
      response: {
        data: { message: "API error" },
      },
    } as any);

    const { result } = renderHook(() => useAvailableCabins());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.cabins).toEqual([]);
    expect(result.current.error).toBe("API error");
  });

  it("uses fallback error message when API provides none", async () => {
    mockUseAppSelector.mockImplementation((selector: any) =>
      selector({
        booking: {
          cabinType: { id: 1 },
          cabinCategory: { id: 2 },
        },
      })
    );

    mockedAxios.get.mockRejectedValueOnce({});

    const { result } = renderHook(() => useAvailableCabins());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.error).toBe("Failed to fetch cabins");
    expect(result.current.cabins).toEqual([]);
  });

  it("does not set error when request is cancelled", async () => {
    mockUseAppSelector.mockImplementation((selector: any) =>
      selector({
        booking: {
          cabinType: { id: 1 },
          cabinCategory: { id: 2 },
        },
      })
    );

    mockedAxios.get.mockRejectedValueOnce(
      new axios.Cancel("Request cancelled")
    );

    const { result } = renderHook(() => useAvailableCabins());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.error).toBeNull();
    expect(result.current.cabins).toEqual([]);
  });
});
