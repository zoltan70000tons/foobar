import { useEffect, useState } from "react";
import axios from "axios";

export function usePaymentPlans(cabinCategoryId?: number) {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!cabinCategoryId) return;

    setLoading(true);
    axios
    .get(route("payment.plans"), { params: { cabin_category_id: cabinCategoryId } })
    .then((res) => setPlans(res.data.plans || []))
    .finally(() => setLoading(false));
  }, [cabinCategoryId]);

  return { plans, loading };
}
