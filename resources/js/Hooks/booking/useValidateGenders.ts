import { useCallback } from "react";
import { CabinType, CabinTypeIds } from "@/enums/CabinType";
import { Customer } from "@/interfaces/Customer";
import { useSnackbar } from "@/Providers/SnackBarAlertProvider";

interface UseValidateGendersParams {
  cabinType: { id: number } | null;
  selectedUser: Customer | null;
}

export const useValidateGenders = ({ cabinType, selectedUser }: UseValidateGendersParams) => {
  const { showSnackbar } = useSnackbar();

  return useCallback(
    (silent = false): boolean => {
      if (!cabinType || !selectedUser) return true;

      const { id } = cabinType;
      const { gender } = selectedUser;

      const invalid =
        (id === CabinTypeIds[CabinType.SINGLE_TICKET_MALE] && gender === "F") ||
        (id === CabinTypeIds[CabinType.SINGLE_TICKET_FEMALE] && gender === "M");

      if (invalid) {
        if (!silent) {
          console.warn("This cabin is gender-restricted and cannot be assigned to this customer.");
          showSnackbar(
            "This cabin is gender-restricted and cannot be assigned to this customer.",
            "error"
          );
        }
        return false;
      }

      return true;
    },
    [cabinType, selectedUser, showSnackbar]
  );
};
