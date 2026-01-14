import { Box, Typography } from "@mui/material";
import { blue, red } from "@mui/material/colors";
import { CabinPriceType } from "@/types/cabin";
import { fixedHeight } from "@/Pages/Cabin/partials/matrix/constants";
import { renderInventory } from "./utils/inventoryUtils";

type Props = {
  isMobile?: boolean;
  eventId: number;
  singlePrice: CabinPriceType;
  cabinCategoryType: string;
  onOpenModal?: (fullTitle: string, price: string, cabinCode: string, singlePrice: CabinPriceType) => void;
};

export default function SingleCell({ isMobile = false, singlePrice }: Props) {
  const { price, inventory } = singlePrice;

  const numericPrice = Number(price);
  const isValidPrice = price && price !== "-" && numericPrice > 0;

  const baseStyles = {
    height: isMobile ? "100%" : fixedHeight,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    width: "100%",
    padding: 0,
    maxWidth: { xs: "100%", md: "190px" },
  };

  if (!isValidPrice) {
    return (
      <Box sx={baseStyles}>
        <Typography sx={{ fontSize: "14px", color: red[600] }}>-</Typography>
      </Box>
    );
  }

  if (inventory.AVAILABLE + inventory.PARTIALLY_BOOKED === 0 && inventory.RESERVED === 0 && inventory.IP === 0) {
    return (
      <Box sx={baseStyles}>
        <Typography
          sx={{
            fontSize: "14px",
            color: "white",
            backgroundColor: red[400],
            width: "100%",
            height: "100%",
            textAlign: "center",
            lineHeight: `${fixedHeight}`,
          }}
        >
          SOLD OUT
        </Typography>
      </Box>
    );
  }

  return (
    <Box sx={baseStyles}>
      <Box
        sx={{
          height: isMobile ? fixedHeight : "auto",
          display: "flex",
          flexDirection: "column",
          color: blue[300],
          width: "100%",
          padding: "5px",
        }}
      >
        <Box sx={{ fontSize: "12px", lineHeight: "16px" }}>{renderInventory(inventory)}</Box>
      </Box>
    </Box>
  );
}
