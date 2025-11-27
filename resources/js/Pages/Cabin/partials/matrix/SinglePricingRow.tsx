import { useState } from "react";
import { Box, Typography } from "@mui/material";
import { blue, grey } from "@mui/material/colors";
import { alpha } from "@mui/material/styles";
import SingleCell from "./SingleCell";
import { PhotoCamera } from "@mui/icons-material";
import ModalCabin from "./ModalCabin";
import { CabinDetail, PriceAndCapacity } from "@/types/cabin";

// table sizes
export const fixedHeight: string = "65px";

export type SelectedCabinDetail =
  CabinDetail & Partial<PriceAndCapacity> & {
  cabin_category_type: string;
};

// Cabin code
const CabinCode = ({
  cabinCode,
  onOpenDetail,
}: {
  index: number;
  rowDataLength: number;
  cabinCode: string;
  onOpenDetail: () => void;
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
  rowDataLength: number;
  index: number;
  cabin: CabinDetail;
  isLast: boolean;
  cabinCategoryType: string;
  eventId: number;
  onOpenDetail: () => void;
};

// Pricing row
export default function SinglePricingRow({
  rowDataLength,
  index,
  cabin,
  cabinCategoryType,
  eventId,
  onOpenDetail,
}: Props) {

  const cabinsPrices = (
    priceAndAvailability: PriceAndAvailability,
    cabinCode: string
  ): CabinPriceType[] => {
    const capacities = [2, 3, 4, 5, 6, 7, 8];

    return capacities
    .map((capacity) => {
      const key = `price_capacity_${capacity}` as keyof PriceAndAvailability;
      const data = priceAndAvailability[key];
      if (!data) return null;

      return {
        full_title: data.full_title,
        price: data.price ? data.price : "-",
        is_available: data.is_available,
        cabin_code: cabinCode,
        cabin_category_id: data.cabin_category_id,
        capacity: data.capacity,
        decks: data.decks,
        iframe: data.iframe,
        images: data.images,
        description: data.description,
        inventory: data.inventory,
      };
    })
    .filter(Boolean) as CabinPriceType[];
  };

  const cabinCode = cabin.code;
  const cabinDecks = cabin.decks_static;
  const cainCategoryType = cabinCategoryType;

  // states
  const [openModal, setOpenModal] = useState<boolean>(false);

  const [selectedCabinDetail, setSelectedCabinDetail] = useState<SelectedCabinDetail | null>(null);

  // handle modal
  const handleModalOpen = (fullTitle: string, price: string, cabinCode: string, singlePrice: CabinPriceType) => {
    setSelectedCabinDetail({
      ...cabin,
      ...singlePrice,
      cabin_category_type: cainCategoryType,
    });

    setOpenModal(true);
  };

  // shared props for the cells
  const sharedCellProps = {
    eventId,
    cabinCategoryType: cabinCategoryType,
    onOpenModal: handleModalOpen,
  };

  // close modal
  const handleModalClose = () => {
    setOpenModal(false);
  };

  // short circuiting
  const priceAndAvailability = cabin.price_and_availability;

  const cabinPrices: CabinPriceType[] = cabinsPrices(priceAndAvailability, cabinCode);

  return (
    <Box
      sx={{
        display: "grid",
        gridTemplateColumns: {
          xs: `75px 75px repeat(${rowDataLength - 1}, minmax(70px, 1fr))`,
        },
        justifyItems: "center",
        alignItems: "center",
        boxSizing: "border-box",
        "&:not(:last-child)": {
          borderBottom: "1px solid",
          borderColor: alpha(grey[700], 0.5),
        },
      }}
    >
      <CabinCode
        index={index}
        rowDataLength={rowDataLength}
        cabinCode={cabinCode}
        onOpenDetail={onOpenDetail}
      />
      <Box
        sx={{
          height: fixedHeight,
          textAlign: "center",
          display: "flex",
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
          {cabinDecks}
        </Typography>
      </Box>
      {cabinPrices.map((price, index) => {
        if (rowDataLength < index + 2) {
          return null;
        }

        return (
          <SingleCell
            key={index}
            singlePrice={price}
            {...sharedCellProps}
          />
        );
      })}

      <ModalCabin
        eventId={eventId}
        open={openModal}
        handleModalClose={handleModalClose}
        selectedCabinDetail={selectedCabinDetail}
      />
    </Box>
  );
}
