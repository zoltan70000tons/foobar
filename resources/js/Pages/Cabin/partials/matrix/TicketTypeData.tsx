import axios from "axios";
import PricingMatrix from "./PricingMatrix";
import React, { useEffect, useState } from "react";
import { Tab, Tabs } from "@mui/material";
import LoadingOverlay from "@/Components/LoadingOverlay";

const tabItems = [
  { label: "Private Cabin", value: 1 },
  { label: "Single Male", value: 2 },
  { label: "Single Female", value: 3 },
];

export const TicketTypeData = ({ eventId, cabinType }) => {
  const [loading, setLoading] = useState(true);
  const [pricing, setPricing] = useState(null);
  const [tabValue, setTabValue] = useState<number>(1);

  useEffect(() => {
    let cancelled = false;

    setLoading(true);

    axios
      .get(route("show.cabin-matrix", { eventId, cabinTypeId: tabValue }))
      .then((res) => {
        if (!cancelled) setPricing(res.data);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [eventId, tabValue]);

  const handleChange = (_event: React.SyntheticEvent, newValue: number) => {
    setTabValue(newValue);
  };

  return (
    <>
      <Tabs value={tabValue} onChange={handleChange} centered sx={{ minWidth: "300px" }}>
        {tabItems.map((item) => (
          <Tab
            key={item.value}
            label={item.label}
            value={item.value}
            sx={{
              flex: 1,
              textAlign: "center",
              width: "100%",
              mx: 0.5,
            }}
          />
        ))}
      </Tabs>

      {loading ? (
        <LoadingOverlay open={loading} />
      ) : pricing ? (
        <PricingMatrix eventId={eventId} pricingData={pricing} cabinTypeSlug={cabinType} />
      ) : (
        <div>No pricing data found.</div>
      )}
    </>
  );
};
