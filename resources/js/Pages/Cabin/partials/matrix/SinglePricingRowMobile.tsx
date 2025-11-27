import { useState } from "react";
import { Box, Typography, Grid } from "@mui/material";
import { blue, grey } from "@mui/material/colors";
import { alpha } from "@mui/material/styles";
import SingleCell from "./SingleCell";
import { PhotoCamera } from "@mui/icons-material";
import ModalCabin from "./ModalCabin";
import { CabinPriceType } from "@/types/cabin";

export const fixedHeight: string = "65px";

// Cabin code
const CabinCode = ({
  cabinCode,
  onOpenDetail,
  rowDataName,
}: {
  rowDataLength?: number;
  cabinCode: string;
  onOpenDetail: () => void;
  rowDataName: string;
}) => {
  return (
    <Box
      component={"button"}
      onClick={() => onOpenDetail()}
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
        boxSizing: "border-box",
        width: "100%",
        borderTop: "1px solid #1e1e1e",
        borderRight: "1px solid #1e1e1e",
        borderBottom: "1px solid #1e1e1e",
        borderLeft: "none",
        "&:hover": {
          backgroundColor: alpha(blue[600], 0.3),
        },
      }}
    >
      <Typography
        sx={{
          fontWeight: "bold",
          fontSize: "10px",
        }}
      >
        {rowDataName}
      </Typography>
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          gap: 1,
        }}
      >
        <Typography
          sx={{
            fontWeight: "bold",
            fontSize: "14px",
          }}
        >
          {cabinCode}
        </Typography>
        <PhotoCamera
          sx={{
            color: blue[600],
            fontSize: 16,
          }}
        />
      </Box>
    </Box>
  );
};

type Props = {
  index?: number;
  cabin: any;
  eventId: number;
  cabinCategoryType: string;
  onOpenDetail: () => void;
};

// Pricing row
export default function SinglePricingRowMobile({
  cabin,
  eventId,
  cabinCategoryType,
  onOpenDetail,
}: Props) {

  const cabinCode = cabin.code;
  const cabinDecks = cabin.decks_static;
  const cabinFullTitle = cabin?.price_and_availability.full_title;
  const price = cabin.price_and_availability.price;
  const capacity = cabin.capacity;
  const cabinCategory = cabin.price_and_availability.cabin_category_id;
  const isAvailable = cabin.price_and_availability.is_available;
  const decks = cabin.price_and_availability.decks;

  // states
  const [openModal, setOpenModal] = useState<boolean>(false);

  const singlePrice: CabinPriceType = {
    full_title: cabinFullTitle,
    price: price,
    cabin_code: cabinCode,
    capacity: capacity,
    is_available: isAvailable,
    cabin_category_id: cabinCategory,
    decks: decks,
    description: cabin?.price_and_availability?.description ?? "",
  }

  const selectedCabinDetail = {
    ...cabin,
    ...singlePrice,
    cabin_category_type: cabinCategoryType,
  };

  // handle modal
  const handleModalOpen = () => {
    setOpenModal(true);
  };

  // close modal
  const handleModalClose = () => {
    setOpenModal(false);
  };

  return (
    <>
      <Grid container>
        <Grid size={4}>
          <CabinCode
            cabinCode={""}
            rowDataName={cabinFullTitle}
            onOpenDetail={onOpenDetail}
          />
        </Grid>
        <Grid size={4}>
          <Box
            sx={{
              height: fixedHeight,
              textAlign: "center",
              display: "flex",
              flexDirection: "column",
              padding: "0 10px",
              alignItems: "center",
              justifyContent: "center",
              boxSizing: "border-box",
              borderColor: grey[700],
              backgroundColor: alpha(grey[800], 0.3),
              width: "100%",
            }}
          >
            <Typography
              sx={{
                fontSize: "13px",
              }}
            >
              decks {cabinDecks}
            </Typography>
            <Typography
              sx={{
                fontSize: "13px",
              }}
            >
              {capacity} passengers
            </Typography>
          </Box>
        </Grid>
        <Grid size={4}>
          <SingleCell
            // isMobile={true}
            // eventId={eventId}
            // fullTitle={cabinFullTitle}
            // price={price}
            // cabinCode={cabinCode}
            // capacity={capacity}
            // cabinCategory={cabinCategory}
            // available={isAvailable}
            // categoryDecks={decks}
            // onOpenModal={handleModalOpen}
            // cabinTypeId={cabinTypeId}
            // cabinCategoryType={cabinCategoryType}
            isMobile={true}
            singlePrice={singlePrice}
            onOpenModal={handleModalOpen}
            eventId={eventId}
            cabinCategoryType={cabinCategoryType}
          />
        </Grid>
      </Grid>
      <ModalCabin
        eventId={eventId}
        open={openModal}
        handleModalClose={handleModalClose}
        selectedCabinDetail={selectedCabinDetail}
      />
    </>
  );
}
