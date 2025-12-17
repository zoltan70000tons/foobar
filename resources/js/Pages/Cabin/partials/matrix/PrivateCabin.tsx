import { Box, Divider, Typography } from "@mui/material";
import TableRow from "./TableRow";
import { handleTotal } from "./utils/pricingUtils";
import { localNumberFormat } from "./utils/stringUtils";

export default function PrivateCabin({
  price,
  taxPrice,
  capacity,
}: {
  price: string;
  taxPrice: number;
  capacity: number;
}) {
  const locale = "en";

  const singleTotal = Number(price) + Number(taxPrice);
  const total = handleTotal(price, capacity, taxPrice);

  return (
    <>
      <TableRow
        firstCol="single_after_tax"
        secondCol={localNumberFormat(singleTotal)}
      />
      <Divider />
      <TableRow firstCol="capacity" secondCol={String(capacity)} />
      <Divider />

      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          py: 1.5,
        }}
      >
        <Typography sx={{ fontWeight: "bold", textDecoration: "underline" }}>
          subTotal
        </Typography>

        <Typography sx={{ fontWeight: "bold", textAlign: "right" }}>
          {localNumberFormat(total, locale)}
          <Box component="span" sx={{ fontSize: "10px", display: "block" }}>
            ({localNumberFormat(singleTotal, locale)} × {capacity})
          </Box>
        </Typography>
      </Box>
    </>
  );
}
