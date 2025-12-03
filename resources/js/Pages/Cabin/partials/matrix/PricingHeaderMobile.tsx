import { Box, Typography } from "@mui/material";

export default function PricingHeaderMobile({
  maxCapacity,
}: {
  maxCapacity: number;
}) {
  // head data
  let headData: string[] = ["category", "info", "price"];

  // trim the head data based on the max capacity
  headData = headData
  .slice(0, maxCapacity + 1)
  .map((key) => key);

  return (
    <>
      <Box
        sx={{
          display: "grid",
          // 3 columns
          gridTemplateColumns: "1fr 1fr 1fr",

          top: 0,
          zIndex: 1000,
        }}
      >
        {headData.map((headItem, index) => (
          <Box
            key={index}
            sx={{
              color: "white",
              py: "10px",
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
    </>
  );
}
