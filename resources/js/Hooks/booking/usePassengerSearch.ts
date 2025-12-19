import { useState, useEffect } from "react";
import axios from "axios";

export function usePassengerSearch(email: string) {
  const [passenger, setPassenger] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!email) return;

    setLoading(true);
    axios
    .get(route("passengers.search"), { params: { email } })
    .then((res) => setPassenger(res.data.passenger ?? null))
    .finally(() => setLoading(false));
  }, [email]);

  return { passenger, loading };
}
