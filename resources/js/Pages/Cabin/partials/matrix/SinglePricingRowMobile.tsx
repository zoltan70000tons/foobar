import { useState } from "react";
import { Box, Typography, Grid } from "@mui/material";
import { blue, grey } from "@mui/material/colors";
import { alpha } from "@mui/material/styles";
import { PhotoCamera } from "@mui/icons-material";

import SingleCell from "./SingleCell";
//import ModalCabin_bkp from "./ModalCabin";

import { CabinPriceType } from "@/types/cabin";
import { fixedHeight } from "@/Pages/Cabin/partials/matrix/constants";

const CabinCode = ({
  cabinCode,
  rowDataName,
  onOpenDetail,
}: {
  cabinCode: string;
  rowDataName: string;
  onOpenDetail: () => void;
}) => (
  <Box
    component="button"
    onClick={onOpenDetail}
    sx={{
      cursor: "pointer",
      height: fixedHeight,
      textAlign: "center",
      backgroundColor: alpha(blue[600], 0.1),
      color: blue[600],
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      width: "100%",
      border: "1px solid #1e1e1e",
      borderLeft: "none",
      "&:hover": {
        backgroundColor: alpha(blue[600], 0.3),
      },
    }}
  >
    <Typography sx={{ fontWeight: "bold", fontSize: "10px" }}>
      {rowDataName}
    </Typography>

    <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
      <Typography sx={{ fontWeight: "bold", fontSize: "14px" }}>
        {cabinCode}
      </Typography>
      <PhotoCamera sx={{ color: blue[600], fontSize: 16 }} />
    </Box>
  </Box>
);

export default function SinglePricingRowMobile({
  cabin,
  eventId,
  //cabinTypeSlug,
  cabinCategoryType,
  onOpenDetail,
}: {
  cabin: any;
  eventId: number;
  cabinCategoryType: string;
  onOpenDetail: () => void;
}) {
  //const [openModal, setOpenModal] = useState(false);

  const {
    code: cabinCode,
    decks_static: cabinDecks,
    capacity,
    price_and_availability: {
      full_title,
      price,
      cabin_category_id,
      is_available,
      decks,
      description = "",
      inventory,
    } = {},
  } = cabin;

  const singlePrice: CabinPriceType = {
    full_title,
    price,
    cabin_code: cabinCode,
    capacity,
    is_available,
    cabin_category_id,
    decks,
    description,
    inventory,
  };

  const selectedCabinDetail = {
    ...cabin,
    ...singlePrice,
    cabin_category_type: cabinCategoryType,
  };

  return (
    <>
      <Grid container>
        <Grid xs={4}>
          <CabinCode
            cabinCode={cabinCode}
            rowDataName={full_title}
            onOpenDetail={onOpenDetail}
          />
        </Grid>

        <Grid xs={4}>
          <Box
            sx={{
              height: fixedHeight,
              display: "flex",
              flexDirection: "column",
              justifyContent: "center",
              alignItems: "center",
              padding: "0 10px",
              textAlign: "center",
              backgroundColor: alpha(grey[800], 0.3),
              border: `1px solid ${grey[700]}`,
            }}
          >
            <Typography fontSize="13px">Decks {cabinDecks}</Typography>
            <Typography fontSize="13px">{capacity} passengers</Typography>
          </Box>
        </Grid>

        <Grid xs={4}>
          <SingleCell
            isMobile
            singlePrice={singlePrice}
            //onOpenModal={() => setOpenModal(true)}
            eventId={eventId}
            cabinCategoryType={cabinCategoryType}
          />
        </Grid>
      </Grid>
    </>
  );
}
