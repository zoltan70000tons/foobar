import React, { useState } from "react";
import {
  Typography,
  IconButton,
  Box,
  Dialog,
  DialogTitle,
  DialogContent,
  alpha,
  Divider,
} from "@mui/material";
import SinglePricingRow from "./SinglePricingRow";
import { grey } from "@mui/material/colors";
import CloseIcon from "@mui/icons-material/Close";
import CabinDetailedView from "./CabinDetailedView";
import { CabinData, CabinDetail, MainCategory } from "@/types/cabin";

type Props = {
  data: { main_category: MainCategory };
  eventId: number;
};

export default function PricingRows({
  data: allData,
  eventId,
}: Props) {
  const data = allData.main_category.categories;
  const maxCapacity = allData.main_category.max_capacity;

  // State for the cabin detail modal
  const [isCabinDetailOpen, setCabinDetailOpen] = useState<boolean>(false);
  const [selectedCabinDetail, setSelectedCabinDetail] = useState<CabinDetail | null>(null);

  // Handle cabin detail open with data
  const handleCabinDetailOpen = (cabinDetail: CabinDetail) => {
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

  return (
    <>
      <Box
        sx={{
          display: "flex",
          flexDirection: "column",
          width: "100%",
        }}
      >
        {sortedData.map((rowData: CabinData, index: number) => (
          <Box key={index}>
            <Typography
              sx={{
                flex: 1,
                height: "50px",
                backgroundColor: alpha(grey[800], 0.3),
                width: "100%",
                display: "flex",
                alignItems: "center",
                pl: "20px",
                textTransform: "uppercase",
                fontWeight: "bold",
                boxSizing: "border-box",
                borderColor: grey[800],
              }}
            >
              {rowData.name}
            </Typography>
            <Box
              sx={{
                display: "flex",
                flexDirection: "column",
              }}
            >
              {rowData.cabins.map((cabin: CabinDetail, index: number) => (
                <SinglePricingRow
                  key={index}
                  rowDataLength={maxCapacity}
                  index={index}
                  cabin={cabin}
                  cabinCategoryType={allData.main_category.name}
                  eventId={eventId}
                  isLast={index === rowData.cabins.length - 1}
                  onOpenDetail={() => handleCabinDetailOpen(cabin)}
                />
              ))}
            </Box>
          </Box>
        ))}
      </Box>
      <Dialog
        open={isCabinDetailOpen}
        onClose={handleCabinDetailClose}
        maxWidth="lg"
        slotProps={{
          paper: {
            sx: {
              width: "100%",
              borderRadius: "10px",
              background: grey[900],
              border: `1px solid ${grey[700]}`
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
          {selectedCabinDetail && <CabinDetailedView cabinDetail={selectedCabinDetail} />}
        </DialogContent>
      </Dialog>
    </>
  );
}
