import { Box, Typography } from "@mui/material";
import { fixedHeight } from "@/Pages/Cabin/partials/matrix/constants";

export default function PricingHeader({
  maxCapacity,
}: {
  maxCapacity: number;
}) {
  // head data
  let headData: string[] = [
    "Category",
    "Deck",
    "Double/Twin 2 Passengers",
    "Triple 3 Passengers",
    "Quad 4 Passengers",
    "Quint 5 Passengers",
    "Sextuple 6 Passengers",
    "Septuple 7 Passengers",
    "Octuple 8 Passengers",
  ];

  // trim the head data based on the max capacity
  headData = headData
  .slice(0, maxCapacity + 1)
  .map((key) => key);

  return (
    <Box
      sx={{
        display: "grid",
        gridTemplateColumns: {
          xs: `75px 75px repeat(${maxCapacity - 1}, minmax(70px, 1fr))`,
        },
        height: fixedHeight,
        top: 0,
        zIndex: 1000,
      }}
    >
      {headData.map((headItem, index) => (
        <Box
          key={index}
          sx={{
            color: "white",
            padding: "5px",
            textAlign: "center",
            fontSize: "0.875em",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
          }}
        >
          <Typography
            sx={{
              fontSize: "12px",
              fontWeight: "700",
            }}
          >
            {headItem}
          </Typography>
        </Box>
      ))}
    </Box>
  );
}
