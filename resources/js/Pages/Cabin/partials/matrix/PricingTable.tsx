import { useState } from "react";
import { Box, useMediaQuery, Typography } from "@mui/material";
import { useTheme } from "@mui/material/styles";
import Sticky from "react-sticky-el";

import PricingHeaderMobile from "./PricingHeaderMobile";
import PricingRowsMobile from "./PricingRowsMobile";
import PricingRows from "@/Pages/Cabin/partials/matrix/PricingRows";
import PricingHeader from "@/Pages/Cabin/partials/matrix/PricingHeader";
import { MainCategory } from "@/types/cabin";

type TableTitleType = {
  imgByCat: any;
  categoryName: string;
};

interface PricingTableProps extends TableTitleType {
  data: { main_category: MainCategory };
  eventId: number;
}

const TableTitle = ({
  imgByCat,
  categoryName,
  isSticky,
  isMobile,
}: TableTitleType & { isSticky?: boolean; isMobile?: boolean }) => {
  const imageSize = isSticky && isMobile ? 50 : 70;
  return (
    <Box
      sx={{
        display: "flex",
        alignItems: "center",
        backgroundColor: "#1e1e1e",
      }}
    >
      <Box
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          width: `${imageSize}px`,
          height: `${imageSize}px`,
          overflow: "hidden",
          borderRadius: "0px",
          transition: "all 0.2s ease-in-out",
          mr: 2,
        }}
      >
        <img
          src={imgByCat}
          alt="Cabin Interior"
          width={imageSize}
          height={imageSize}
          //quality={100}
          style={{ objectFit: "cover" }}
        />
      </Box>
      <Typography
        fontSize={isSticky ? 14 : 16}
        fontWeight={600}
      >
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
}: PricingTableProps) {
  const theme = useTheme();
  const maxCapacity = data.main_category.max_capacity;
  const mainCategoryName = data.main_category.name;
console.log(data)
  const [isSticky, setIsSticky] = useState(false);

  const isMobile = useMediaQuery(
    `(max-width:${theme.breakpoints.values.md}px)`
  );

  // Consolidate Sticky props
  const stickyProps = {
    topOffset: isMobile ? -70 : -40,
    boundaryElement: ".pricing-table-container",
    onFixedToggle: setIsSticky,
    stickyStyle: {
      zIndex: 2,
      backgroundColor: "#191919",
    },
  };

  // Conditionally choose header content based on screen size
  const headerContent = isMobile ? (
    <>
      <TableTitle
        imgByCat={imgByCat}
        categoryName={categoryName}
        isSticky={isSticky}
        isMobile={isMobile}
      />
      <PricingHeaderMobile
        maxCapacity={maxCapacity}
        mainCategoryName={mainCategoryName}
      />
    </>
  ) : (
    <>
      <TableTitle
        imgByCat={imgByCat}
        categoryName={categoryName}
        isSticky={isSticky}
      />
      <PricingHeader maxCapacity={maxCapacity} />
    </>
  );

  return (
    <Box
      className="pricing-table-container"
      sx={{ position: "relative", mx: "auto" }}
    >
      <Sticky {...stickyProps}>{headerContent}</Sticky>

      {isMobile ? (
        <PricingRowsMobile
          data={data}
          eventId={eventId}
        />
      ) : (
        <PricingRows data={data} eventId={eventId} />
      )}
    </Box>
  );
}
