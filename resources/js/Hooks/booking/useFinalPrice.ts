import { useMemo } from "react";
import { useAppSelector } from "@/store/hooks";

export function useFinalPrice() {
  const { cabinCategory, addons, paymentPlan } = useAppSelector(
    (s) => s.booking
  );

  return useMemo(() => {
    let base = cabinCategory?.price || 0;

    if (addons.carbonOffset) base += 20;
    if (addons.youChooseYourCabin) base += 50;

    if (paymentPlan?.discount) base -= paymentPlan.discount;

    return base;
  }, [cabinCategory, addons, paymentPlan]);
}
