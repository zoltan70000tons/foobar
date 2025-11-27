import axios from "axios";
import PricingMatrix from "./PricingMatrix";
import { useEffect, useState } from "react";

export const TicketTypeData = ({ eventId, cabinType }) => {
  const [loading, setLoading] = useState(true);
  const [pricing, setPricing] = useState(null);

  useEffect(() => {
    axios
    .get(route("show.cabin-matrix", { eventId }))
    .then(res => setPricing(res.data))
    .finally(() => setLoading(false));
  }, [eventId, cabinType]);

  if (loading) return <div>Loading pricing...</div>;
  if (!pricing) return <div>No pricing data found.</div>;

  return (
    <PricingMatrix
      eventId={eventId}
      pricingData={pricing}
    />
  );
};
