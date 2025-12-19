import { useEffect, useState } from "react";
import axios from "axios";
import { useAppSelector } from "@/store/hooks";

export function useAvailableCabins() {
  const { cabinType, cabinCategory } = useAppSelector(s => s.booking);
  const [cabins, setCabins] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!cabinType || !cabinCategory) return;

    setLoading(true);
    axios
    .get(route("cabins.available"), {
      params: { type_id: cabinType.id, category_id: cabinCategory.id },
    })
    .then((res) => setCabins(res.data.cabins ?? []))
    .finally(() => setLoading(false));
  }, [cabinType, cabinCategory]);

  return { cabins, loading };
}
