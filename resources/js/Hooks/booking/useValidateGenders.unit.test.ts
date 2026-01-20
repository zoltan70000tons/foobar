import { renderHook, act } from "@testing-library/react";
import { useValidateGenders } from "./useValidateGenders";
import { CabinType, CabinTypeIds } from "@/enums/CabinType";
import { Customer } from "@/interfaces/Customer";
import * as SnackbarProvider from "@/Providers/SnackBarAlertProvider";

const showSnackbarMock = jest.fn();

jest.spyOn(SnackbarProvider, "useSnackbar").mockReturnValue({
  showSnackbar: showSnackbarMock,
});

describe("useValidateGenders", () => {
  beforeEach(() => {
    showSnackbarMock.mockClear();
  });

  it("returns true when cabinType is null", () => {
    const { result } = renderHook(() =>
      useValidateGenders({ cabinType: null, selectedUser: null })
    );

    const isValid = result.current();

    expect(isValid).toBe(true);
    expect(showSnackbarMock).not.toHaveBeenCalled();
  });

  it("returns true when selectedUser is null", () => {
    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_MALE] },
        selectedUser: null,
      })
    );

    const isValid = result.current();

    expect(isValid).toBe(true);
    expect(showSnackbarMock).not.toHaveBeenCalled();
  });

  it("returns true for valid male cabin + male user", () => {
    const user = { gender: "M" } as Customer;

    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_MALE] },
        selectedUser: user,
      })
    );

    const isValid = result.current();

    expect(isValid).toBe(true);
    expect(showSnackbarMock).not.toHaveBeenCalled();
  });

  it("returns true for valid female cabin + female user", () => {
    const user = { gender: "F" } as Customer;

    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_FEMALE] },
        selectedUser: user,
      })
    );

    const isValid = result.current();

    expect(isValid).toBe(true);
    expect(showSnackbarMock).not.toHaveBeenCalled();
  });

  it("returns false and shows snackbar for invalid male cabin + female user", () => {
    const user = { gender: "F" } as Customer;

    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_MALE] },
        selectedUser: user,
      })
    );

    let isValid: boolean;

    act(() => {
      isValid = result.current();
    });

    expect(isValid!).toBe(false);
    expect(showSnackbarMock).toHaveBeenCalledWith(
      "This cabin is gender-restricted and cannot be assigned to this customer.",
      "error"
    );
  });

  it("returns false and shows snackbar for invalid female cabin + male user", () => {
    const user = { gender: "M" } as Customer;

    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_FEMALE] },
        selectedUser: user,
      })
    );

    const isValid = result.current();

    expect(isValid).toBe(false);
    expect(showSnackbarMock).toHaveBeenCalledTimes(1);
  });

  it("returns false but does NOT show snackbar when silent=true", () => {
    const user = { gender: "F" } as Customer;

    const { result } = renderHook(() =>
      useValidateGenders({
        cabinType: { id: CabinTypeIds[CabinType.SINGLE_TICKET_MALE] },
        selectedUser: user,
      })
    );

    const isValid = result.current(true);

    expect(isValid).toBe(false);
    expect(showSnackbarMock).not.toHaveBeenCalled();
  });
});
