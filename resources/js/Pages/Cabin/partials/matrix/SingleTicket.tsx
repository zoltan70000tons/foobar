import { Divider } from "@mui/material";
import TableRow from "./TableRow";
import { handleTotalSingle } from "./utils/pricingUtils";
import { localNumberFormat } from "./utils/stringUtils";

export default function SingleTicket({
  stFeePrice,
  price,
  locale,
  taxPrice,
}: {
  stFeePrice: number;
  price: string;
  locale: string;
  taxPrice: number;
}) {
  const total = handleTotalSingle(price, stFeePrice, taxPrice);

  return (
    <>
      <TableRow firstCol="singleTicket" secondCol={localNumberFormat(stFeePrice)} />
      <Divider />
      <TableRow firstCol="subTotal" secondCol={localNumberFormat(total, locale)} />
    </>
  );
}
