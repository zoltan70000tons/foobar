import React from "react";
import { Box } from "@mui/material";
import PricingTable from "@/Pages/Cabin/partials/matrix/PricingTable";
import { MainCategory } from "@/types/cabin";
import cabinInterior from "/public/images/interior-400.jpg";
import oceanView from "/public/images/oceanview-400.jpg";
import balcony from "/public/images/balcony-400.jpg";
import suite from "/public/images/suite-400.jpg";

const categoryImages: Record<string, string> = {
  Interior: cabinInterior,
  "Ocean View": oceanView,
  Balcony: balcony,
  Suite: suite,
  default: cabinInterior,
};

export default function PricingMatrix({
  eventId,
  pricingData,
  cabinTypeSlug,
}: {
  eventId: number;
  pricingData: { main_category: MainCategory }[];
}) {
  const mainCat = pricingData?.[0]?.main_category;
  if (!mainCat) {
    return <Box>Pricing Matrix Error</Box>;
  }

  return (
    <Box
      sx={{
        pb: "20px",
        margin: "0 auto",
        overflowX: "auto",
      }}
    >
      {pricingData.map((item) => {
        const { main_category } = item;
        const { name, categories } = main_category;

        const empty = categories.every(
          (cat) => cat.cabins.length === 0
        );
        if (empty) return null;

        const image = categoryImages[name] ?? categoryImages.default;

        return (
          <Box
            key={name}
            sx={{
              overflow: "hidden",
              borderRadius: "20px",
              mb: 8,
              borderColor: "#000",
              borderWidth: "1px",
              borderStyle: "solid",
            }}
          >
            <PricingTable
              imgByCat={image}
              categoryName={name}
              data={item}
              eventId={eventId}
              cabinTypeSlug={cabinTypeSlug}
            />
          </Box>
        );
      })}
    </Box>
  );
}
