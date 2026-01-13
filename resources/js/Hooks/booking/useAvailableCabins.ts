import { useEffect, useState } from "react";
import axios from "axios";
import { useAppSelector } from "@/store/hooks";
import { Cabin } from "@/interfaces/Cabin";

export function useAvailableCabins() {
  const { cabinType, cabinCategory } = useAppSelector(s => s.booking);
  const [cabins, setCabins] = useState<Cabin[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!cabinType || !cabinCategory) return;

    const controller = new AbortController();
    setLoading(true);
    setError(null);

    axios
      .get(route("cabins.available"), {
        params: { type_id: cabinType.id, category_id: cabinCategory.id },
        signal: controller.signal,
      })
      .then((res) => setCabins(res.data.cabins ?? []))
      .catch((err) => {
        if (!axios.isCancel(err)) {
          setError(err.response?.data?.message ?? "Failed to fetch cabins");
          setCabins([]);
        }
      })
      .finally(() => setLoading(false));

      return () => controller.abort();
  }, [cabinType, cabinCategory]);

  return { cabins, loading, error };
}
