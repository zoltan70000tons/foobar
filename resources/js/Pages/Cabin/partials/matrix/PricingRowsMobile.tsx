import React, { useState } from "react";
import {
  IconButton,
  Box,
  Dialog,
  DialogTitle,
  DialogContent,
  Divider,
  Typography,
} from "@mui/material";
import { grey } from "@mui/material/colors";
import CloseIcon from "@mui/icons-material/Close";
import SinglePricingRowMobile from "./SinglePricingRowMobile";
import IframeComponent from "./IframeComponent";
import ImageComponent from "./ImageComponent";
import { MainCategory, MobileCabinDetail, MobileCabinRow, PriceAndCapacity } from "@/types/cabin";

type Props = {
  data: { main_category: MainCategory };
  eventId: number;
};

export default function PricingRowsMobile({
  data: allData,
  eventId,
}: Props) {
  const data = allData.main_category.categories;

  // State for the cabin detail modal
  const [isCabinDetailOpen, setCabinDetailOpen] = useState<boolean>(false);
  const [selectedCabinDetail, setSelectedCabinDetail] = useState<MobileCabinRow | null>(null);

  // Handle cabin detail open with data
  const handleCabinDetailOpen = (cabinDetail: MobileCabinRow) => {
    setSelectedCabinDetail(cabinDetail);
    setCabinDetailOpen(true);
  };

  // Handle cabin detail close
  const handleCabinDetailClose = () => {
    setCabinDetailOpen(false);
    setSelectedCabinDetail(null);
  };

  // Sorting and mapping
  const sortedData = [...data]
  .sort((a, b) => a.display_order - b.display_order)
  .map((rowData) => ({
    ...rowData,
    cabins: [...rowData.cabins].sort(
      (a, b) => a.display_order - b.display_order
    ),
  }))
  .filter((rowData) => rowData.cabins.length > 0);

  // recursive function to group data
  function groupData (
    code: string,
    decksStatic: string,
    rowDataName: string,
    availablePrices: PriceAndCapacity[],
    cabinCategoryId: number,
    fullTitle: string,

  ): MobileCabinRow[]
  {
    const singleRowData: MobileCabinRow[] = [];

    availablePrices.forEach((price: PriceAndCapacity) => {

      singleRowData.push({
        code,
        decks_static: decksStatic,
        row_data_name: rowDataName,
        capacity: price.capacity,
        price_and_availability: price,
        cabin_category_id: cabinCategoryId,
        full_title: fullTitle,
        images: price.images || undefined,
        iframe: price.iframe || undefined,
      });
    });

    return singleRowData;
  };

  // data structure
  const dataStructure = (): MobileCabinRow[][] => {
    let result: MobileCabinRow[][] = [];

    sortedData.forEach((rowData) => {
      const name = rowData.name;
      const cabins = rowData.cabins;

      cabins.map((cabin: MobileCabinDetail) => {

        const code = cabin.code;
        const decksStatic = cabin.decks_static;
        const priceAndAvailability = cabin.price_and_availability;
        const cabinCategoryId = cabin.cabin_category_id;
        const fullTitle = cabin.full_title;

        // if obj
        const availablePrices = (Object.values(priceAndAvailability) as PriceAndCapacity[]).filter(
          (item: PriceAndCapacity) => item.price !== null
        );

        const groupedData = groupData(
          code,
          decksStatic,
          name,
          availablePrices,
          cabinCategoryId,
          fullTitle
        );

        result.push(groupedData);
      });
    });

    return result;
  };

  const groupedCabins: MobileCabinRow[][] = dataStructure();
  const flatGroupedCabins: MobileCabinRow[] = groupedCabins.flat();

  return (
    <>
      <Box
        sx={{
          display: "flex",
          flexDirection: "column",
          width: "100%",
        }}
      >
        {flatGroupedCabins.map((rowData: MobileCabinRow, index: number) => (
          <Box
            key={index}
            sx={{
              display: "flex",
              flexDirection: "column",
              mb: 0.5,
            }}
          >
            <SinglePricingRowMobile
              cabin={rowData}
              eventId={eventId}
              cabinCategoryType={allData.main_category.name}
              onOpenDetail={() => handleCabinDetailOpen(rowData)}
            />
          </Box>
        ))}
      </Box>
      <Dialog
        open={isCabinDetailOpen}
        onClose={handleCabinDetailClose}
        fullScreen
        slotProps={{
          paper: {
            sx: {
              background: grey[900],
            },
          },
        }}
      >
        <DialogTitle
          align="center"
          id="Cabin Title"
          sx={{ maxWidth: "400px", margin: "auto" }}
        >
          {selectedCabinDetail?.full_title}
        </DialogTitle>
        <IconButton
          aria-label="close"
          onClick={handleCabinDetailClose}
          sx={{
            position: "absolute",
            right: 8,
            top: 8,
          }}
        >
          <CloseIcon />
        </IconButton>
        <Divider />
        <DialogContent>
          {/**** CABIN DESCRIPTION ****/}
          <Typography
            align="center"
            variant="body2"
            dangerouslySetInnerHTML={{
              __html:
                selectedCabinDetail?.price_and_availability?.description?.[
                  selectedCabinDetail.price_and_availability.description
                  ] ?? "",
            }}
            sx={{
              maxWidth: "400px",
              margin: "20px auto",
            }}
          />
          <Box
            sx={{
              display: "flex",
              alignItems: "center",
              flexDirection: {xs: "column", md: "row"},
              gap: 2,
            }}
          >
            <IframeComponent iframe={selectedCabinDetail?.price_and_availability?.iframe} />
            <ImageComponent images={selectedCabinDetail?.price_and_availability?.images} />
          </Box>
          {/**** IMAGE DISCLAIMER ****/}
          <Typography
            component="small"
            align="center"
            variant="caption"
            sx={{
              display: "block",
              width: "100%",
              maxWidth: "400px",
              margin: "10px auto",
            }}
          >
            imageDisclaimer
          </Typography>
        </DialogContent>
      </Dialog>
    </>
  );
}
