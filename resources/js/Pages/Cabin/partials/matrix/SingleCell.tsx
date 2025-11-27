import React from "react";
import { Box, Typography } from "@mui/material";
import { blue, red } from "@mui/material/colors";
import { CabinPriceType } from "@/types/cabin";

// table sizes
export const fixedHeight: string = "65px";

type Props = {
  isMobile?: boolean;
  eventId: number
  singlePrice: CabinPriceType
  cabinCategoryType: string
  onOpenModal: (
    fullTitle: string,
    price: string,
    cabinCode: string,
    singlePrice: CabinPriceType
  ) => void;
};

export default function SingleCell({
  isMobile = false,
  singlePrice,
}: Props) {
  const {
    price,
    is_available: available,
    inventory,
  } = singlePrice as CabinPriceType;

  return (
    <Box
      sx={{
        height: isMobile ? "100%" : fixedHeight,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: 0,
        width: "100%",
        maxWidth: { xs: "100%", md: "190px" },
      }}
    >
      {price && (price !== "-" && Number(price) > 0) ? (
        <>
          {available ? (
            <Box
              variant="contained"
              sx={{
                height: isMobile ? fixedHeight : "auto",
                display: "flex",
                flexDirection: "column",
                color: blue[300],
                backgroundColor: "transparent",
                width: "100%",
                padding: "5px",
                borderRadius: isMobile ? "0" : "auto",
              }}
            >
              <Typography sx={{ zIndex: 1, fontSize: "12px", lineHeight: "16px" }}>
                <Box sx={{ color: "green", textAlign: "center" }}>Available: {inventory.AVAILABLE}</Box>
                <Box sx={{ color: "yellow", textAlign: "center" }}>Internally Available: {inventory.RESERVED}</Box>
                <Box sx={{ color: "red", textAlign: "center" }}>In progress: {inventory.BOOKED}</Box>
              </Typography>
            </Box>
          ) : (
            <Typography
              sx={{
                fontSize: "14px",
                color: red[600],
                textDecoration: "line-through",
              }}
            >
              TODO not available
            </Typography>
          )}
        </>
      ) : (
        <Typography sx={{ fontSize: "14px", color: red[600] }}>-</Typography>
      )}
    </Box>
  );
}
