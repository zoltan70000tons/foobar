import { useState } from "react";
import { Box, Typography, useMediaQuery } from "@mui/material";
import { useTheme } from "@mui/material/styles";
import Sticky from "react-sticky-el";

import PricingHeaderMobile from "./PricingHeaderMobile";
import PricingRowsMobile from "./PricingRowsMobile";
import PricingRows from "@/Pages/Cabin/partials/matrix/PricingRows";
import PricingHeader from "@/Pages/Cabin/partials/matrix/PricingHeader";
import { MainCategory } from "@/types/cabin";

type TableTitleType = {
  imgByCat: string;
  categoryName: string;
};

interface PricingTableProps extends TableTitleType {
  data: { main_category: MainCategory };
  eventId: number;
}

const TableTitle = ({
  imgByCat,
  categoryName,
  isSticky = false,
  isMobile = false,
}: TableTitleType & { isSticky?: boolean; isMobile?: boolean }) => {
  const imageSize = isSticky && isMobile ? 50 : 70;

  return (
    <Box
      sx={{
        display: "flex",
        alignItems: "center",
        backgroundColor: "#1e1e1e",
        px: 1.5,
        py: 1,
      }}
    >
      <Box
        sx={{
          width: imageSize,
          height: imageSize,
          overflow: "hidden",
          borderRadius: 0,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          transition: "all 0.2s ease-in-out",
          mr: 2,
        }}
      >
        <img
          src={imgByCat}
          alt={categoryName}
          width={imageSize}
          height={imageSize}
          style={{ objectFit: "cover" }}
        />
      </Box>

      <Typography fontSize={isSticky ? 14 : 16} fontWeight={600}>
        {categoryName}
      </Typography>
    </Box>
  );
};

export default function PricingTable({
  imgByCat,
  categoryName,
  data,
  eventId,
  cabinTypeSlug,
}: PricingTableProps) {
  const theme = useTheme();
  const { main_category } = data;
  const { max_capacity, name } = main_category;

  const [isSticky, setSticky] = useState(false);
  const isMobile = useMediaQuery(
    `(max-width:${theme.breakpoints.values.md}px)`
  );

  const stickyProps = {
    topOffset: isMobile ? -70 : -40,
    boundaryElement: ".pricing-table-container",
    onFixedToggle: (fixed: boolean) => setSticky(fixed),
    stickyStyle: {
      zIndex: 2,
      backgroundColor: "#191919",
    },
  };

  const renderHeader = () => (
    <>
      <TableTitle
        imgByCat={imgByCat}
        categoryName={categoryName}
        isSticky={isSticky}
        isMobile={isMobile}
      />

      {isMobile ? (
        <PricingHeaderMobile
          maxCapacity={max_capacity}
          mainCategoryName={name}
        />
      ) : (
        <PricingHeader maxCapacity={max_capacity} />
      )}
    </>
  );

  return (
    <Box className="pricing-table-container" sx={{ position: "relative", mx: "auto" }}>
      <Sticky {...stickyProps}>{renderHeader()}</Sticky>

      {isMobile ? (
        <PricingRowsMobile data={data} eventId={eventId} cabinTypeSlug={cabinTypeSlug} />
      ) : (
        <PricingRows data={data} eventId={eventId} cabinTypeSlug={cabinTypeSlug} />
      )}
    </Box>
  );
}
