import { useState } from "react";
import { Box, Typography } from "@mui/material";
import { alpha } from "@mui/material/styles";
import { grey } from "@mui/material/colors";

import SingleCell from "./SingleCell";
//import ModalCabin_bkp from "./ModalCabin";
import { CabinDetail, CabinPriceType } from "@/types/cabin";
import { fixedHeight } from "@/Pages/Cabin/partials/matrix/constants";

import { CabinCode } from "./CabinCode"; // optional if separated
import { extractCabinPrices } from "./utils/cabinUtils";

export type SelectedCabinDetail = CabinDetail &
  Partial<CabinPriceType> & {
    cabin_category_type: string;
  };

type Props = {
  rowDataLength: number;
  cabin: CabinDetail;
  cabinCategoryType: string;
  cabinTypeSlug: string;
  eventId: number;
  onOpenDetail: () => void;
};

export default function SinglePricingRow({
  rowDataLength,
  cabin,
  cabinCategoryType,
  cabinTypeSlug,
  eventId,
  onOpenDetail,
}: Props) {
  const [openModal, setOpenModal] = useState(false);
  const [selectedCabinDetail, setSelectedCabinDetail] = useState<SelectedCabinDetail | null>(null);

  const cabinCode = cabin.code;
  const cabinDecks = cabin.decks_static;

  const cabinPrices: CabinPriceType[] = extractCabinPrices(cabin.price_and_availability, cabinCode);

  const handleModalOpen = (_fullTitle: string, _price: string, _cabinCode: string, singlePrice: CabinPriceType) => {
    setSelectedCabinDetail({
      ...cabin,
      ...singlePrice,
      cabin_category_type: cabinCategoryType,
    });
    alert("OPENING");
    setOpenModal(true);
  };

  const cellProps = {
    eventId,
    cabinCategoryType,
    onOpenModal: handleModalOpen,
  };

  return (
    <Box
      sx={{
        display: "grid",
        gridTemplateColumns: `75px 75px repeat(${rowDataLength - 1}, minmax(70px, 1fr))`,
        alignItems: "center",
        "&:not(:last-child)": {
          borderBottom: `1px solid ${alpha(grey[700], 0.5)}`,
        },
      }}
    >
      <CabinCode cabinCode={cabinCode} onOpenDetail={onOpenDetail} />

      <Box
        sx={{
          height: fixedHeight,
          width: "100%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          backgroundColor: alpha(grey[800], 0.3),
          borderColor: grey[700],
          padding: "0 10px",
        }}
      >
        <Typography sx={{ fontSize: "13px" }}>{cabinDecks}</Typography>
      </Box>

      {cabinPrices.map((price, idx) => {
        if (idx + 2 > rowDataLength) return null;

        return <SingleCell key={idx} singlePrice={price} {...cellProps} />;
      })}
    </Box>
  );
}
