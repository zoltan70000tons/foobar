import React from "react";
import {
  Box,
} from "@mui/material";
// media
import cabinInterior from "/public/images/interior-400.jpg";
import oceanView from "/public/images/oceanview-400.jpg";
import balcony from "/public/images/balcony-400.jpg";
import suite from "/public/images/suite-400.jpg";
import PricingTable from "@/Pages/Cabin/partials/matrix/PricingTable";
import { MainCategory } from "@/types/cabin";

export default function PricingMatrix({
  eventId,
  pricingData,
}: {
  eventId: number;
  pricingData: {
    main_category: MainCategory;
  }[];
}) {
  if (!pricingData[0]?.main_category) {
    //return <Box>{tPricingMatrix("Error.pricingData")}</Box>;
    return <Box>Some error</Box>;
  }

  return (
    <>
      <Box
        sx={{
          pb: "20px",
          margin: "0 auto",
          overflowX: "auto",
        }}
      >
        {pricingData.map((mainCat: any, index: number) => {
          let categories = mainCat.main_category.categories;
          const allCabinsEmpty = categories.every(
            (category: any) => category.cabins.length === 0
          );

          // If all cabins arrays are empty, return null
          if (allCabinsEmpty) {
            return null;
          }

          let imgByCat = "";
          let categoryName = "";

          switch (mainCat.main_category.name) {
            case "Interior":
              imgByCat = cabinInterior;
              categoryName = "Interior";
              break;
            case "Ocean View":
              imgByCat = oceanView;
              categoryName = "Ocean View";
              break;
            case "Balcony":
              imgByCat = balcony;
              categoryName = "Balcony";
              break;
            case "Suite":
              imgByCat = suite;
              categoryName = "Suite";
              break;
            default:
              imgByCat = cabinInterior;
              categoryName = "defaultCategory";
              break;
          }

          return (
            <Box
              key={mainCat.main_category.name}
              component={"div"}
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
                imgByCat={imgByCat}
                categoryName={categoryName}
                data={mainCat}
                eventId={eventId}
              />
            </Box>
          );
        })}
      </Box>
    </>
  );
}
