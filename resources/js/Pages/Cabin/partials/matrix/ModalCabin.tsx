import { useState } from "react";
import {
  Button,
  Dialog,
  DialogContent,
  DialogTitle,
  FormControlLabel,
  Checkbox,
  Typography,
  Box,
  Alert,
  Divider,
  IconButton,
  alpha,
  useMediaQuery,
  useTheme,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import { localNumberFormat } from "./utils/stringUtils";
import { red, blue } from "@mui/material/colors";
import { ApiError } from "@/src/app/types/errors";
import IframeComponent from "./IframeComponent";
import ImageComponent from "./ImageComponent";
import { SelectedCabinDetail } from "./SinglePricingRow";

type Props = {
  eventId: number;
  open: boolean;
  handleModalClose: () => void;
  selectedCabinDetail: SelectedCabinDetail | null;
};

// round to two decimals
function roundToTwoDecimals(value: number): number {
  return Math.round(value * 100) / 100;
}

// handle total single
function handleTotalSingle(price: string, singleTicketFeeAddon: number, taxPrice: number) {
  const rounded = roundToTwoDecimals(
    Number(price) + roundToTwoDecimals(singleTicketFeeAddon) + roundToTwoDecimals(taxPrice),
  );

  return rounded;
}

// handle total
function handleTotal(price: string, capacity: number, taxPrice: number) {
  if (!capacity) {
    return 1;
  }

  // const capacityInt = parseInt(capacity);
  const tax = taxPrice * capacity;

  return Number(price) * capacity + tax;
}

// Modal component, accepting content dynamically
export default function ModalCabin({ open, eventId, handleModalClose, selectedCabinDetail }: Props) {
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down("md"));

  const [modalLoading, setModalLoading] = useState<boolean>(false);
  const [isAccepted, setIsAccepted] = useState<boolean>(false);
  const [error, setError] = useState<ApiError | null>(null);

  const {
    capacity,
    price,
    full_title: cabinFullTitle,
    description,
    images,
    iframe,
  } = selectedCabinDetail || {};

  // close modal
  const closeModal = () => {
    setModalLoading(false);
    setIsAccepted(false);
    handleModalClose();
  };

  if (!price) return null;

  return (
    <Dialog
      open={open}
      onClose={closeModal}
      aria-labelledby="alert-dialog-title"
      aria-describedby="alert-dialog-description"
      fullScreen={fullScreen}
      maxWidth="xl"
      slotProps={{
        paper: { sx: { background: "#212121ff" } },
      }}
    >
      <DialogTitle
        id="alert-dialog-title"
        sx={{
          textAlign: "center",
          fontWeight: 500,
          pt: 5,
        }}
      >
        {cabinFullTitle}
      </DialogTitle>
      <Divider
        sx={{
          width: "100%",
        }}
      />
      <IconButton
        aria-label="close"
        onClick={handleModalClose}
        sx={(theme) => ({
          position: "absolute",
          right: 0,
          top: 2,
          color: theme.palette.grey[500],
        })}
      >
        <CloseIcon />
      </IconButton>
      <DialogContent
        sx={{
          p: 4,
          display: "flex",
          mr: { md: 0 },
          flexWrap: { xs: "wrap", md: "nowrap" },
          gap: 4,
          justifyContent: "center",
          alignItems: "flex-start",
        }}
        className="scrollbar"
      >
        <Box
          sx={{
            width: "100%",
            maxWidth: { xs: "100%", md: "400px" },
          }}
        >
          {error && (
            <Alert severity="error" sx={{ my: 2 }}>
              {error.errorMessage}
            </Alert>
          )}
          <Box
            sx={{
              display: "flex",
              flexDirection: "column",
            }}
          >
            <TableRow firstCol={"pricePerPerson"} secondCol={localNumberFormat(price, "en")} />
            <Divider
              sx={{
                width: "100%",
              }}
            />
            {/*<TableRow firstCol={"tax"} secondCol={localNumberFormat(taxPrice, 'en')} />*/}
            <TableRow firstCol={"tax"} secondCol={localNumberFormat(22, 'en')} />
          </Box>
          <Divider
            sx={{
              width: "100%",
            }}
          />
          {cabinTypeSlug === "private-cabin" ? (
            <>
            {/*<PrivateCabin price={price} taxPrice={taxPrice} capacity={Number(capacity)} />*/}
            <PrivateCabin price={price} taxPrice={2} capacity={Number(capacity)} />
            </>
          ) : (
            <>
            {/*<SingleTicket stFeePrice={stFeePrice ? stFeePrice : 0} price={price} locale={'en'} taxPrice={taxPrice} />*/}
            <SingleTicket stFeePrice={0} price={price} locale={'en'} taxPrice={1} />
            </>
          )}
          {/*<EventStatusAlert purchaseAccess={haveAccess} accessMessage={accessMessage} variant="body2" />*/}

          {true && (
            <Box
              sx={{
                mt: 2,
              }}
            >
              {!error && (
                <Box
                  sx={{
                    mt: 4,
                    display: "flex",
                    p: 1,
                    flexDirection: "column",
                    backgroundColor: !isAccepted ? alpha(red[600], 0.1) : alpha(blue[600], 0.1),
                    border: `1px solid ${!isAccepted ? red[600] : blue[600]}`,
                    borderRadius: 1,
                  }}
                >
                  <FormControlLabel
                    label={"accept_cabin_config"}
                    control={<Checkbox checked={isAccepted} onChange={(e) => setIsAccepted(e.target.checked)} />}
                  />
                  <Button
                    fullWidth
                    disabled={!isAccepted}
                    loading={modalLoading}
                    onClick={() => {}}
                    variant="contained"
                    sx={{
                      mt: 2,
                    }}
                  >
                    confirm
                  </Button>
                </Box>
              )}
            </Box>
          )}
        </Box>
        <Box
          sx={{
            gap: 2,
          }}
        >
          <Typography
            align="center"
            variant="body2"
            dangerouslySetInnerHTML={{
              __html: description?.['en' as keyof typeof description] || "",
            }}
            sx={{ m: "10px auto 20px" }}
          />
          <Box
            sx={{
              display: "flex",
              flexDirection: { xs: "column", md: "row" },
              gap: 2,
              justifyContent: "center",
              minWidth: { xs: "auto", xl: "900px" },
            }}
          >
            <IframeComponent iframe={iframe} />
            <ImageComponent images={images} />
          </Box>
          <Box
            sx={{
              mt: 2,
              width: "100%",
            }}
          >
            {/**** IMAGE DISCLAIMER ****/}
            <Typography
              component="small"
              align="center"
              variant="caption"
              sx={{
                display: "block",
                width: "100%",
                maxWidth: "400px",
                margin: "10px auto",
                m: "10px auto",
              }}
            >
              imageDisclaimer
            </Typography>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
}

// Table row component
const TableRow = ({ firstCol, secondCol }: { firstCol: string; secondCol: string }) => {
  return (
    <Box
      sx={{
        display: "flex",
        py: 1.5,
        alignItems: "top",
        justifyContent: "space-between",
      }}
    >
      <Typography sx={{ fontWeight: "bold" }}>{firstCol}</Typography>
      <Box
        sx={{
          display: "flex",
          flexDirection: "column",
          alignItems: "end",
        }}
      >
        <Typography
          sx={{
            textAlign: "right",
          }}
        >
          {secondCol}
        </Typography>
      </Box>
    </Box>
  );
};

// Private cabin component
const PrivateCabin = ({ price, taxPrice, capacity }: { price: string; taxPrice: number; capacity: number }) => {
  //const tCabinModal = useTranslations("CabinModal");
  const locale = "en";

  return (
    <>
      <TableRow
        firstCol={"single_after_tax"}
        secondCol={localNumberFormat(Number(price) + Number(taxPrice))}
      />
      <Divider
        sx={{
          width: "100%",
        }}
      />

      <TableRow firstCol={"capacity"} secondCol={capacity.toString()} />
      <Divider
        sx={{
          width: "100%",
        }}
      />
      <Box
        sx={{
          display: "flex",
          alignItems: { xs: "start", md: "center" },
          justifyContent: "space-between",
          py: 1.5,
          gap: { xs: 2, md: 4 },
        }}
      >
        <Typography sx={{ fontWeight: "bold", textDecoration: "underline" }}>subTotal</Typography>
        <Typography
          sx={{
            textAlign: "right",
            fontWeight: "bold",
          }}
        >
          {localNumberFormat(handleTotal(price, capacity, taxPrice), locale)}
          <Box
            component={"span"}
            sx={{
              display: "block",
              fontWeight: "normal",
              fontSize: "10px",
            }}
          >
            {`(${localNumberFormat(Number(price) + Number(taxPrice), locale)} x ${capacity} = ${localNumberFormat(handleTotal(price, capacity, taxPrice), locale)})`}
          </Box>
        </Typography>
      </Box>
    </>
  );
};

// Single ticket component
const SingleTicket = ({
  stFeePrice,
  price,
  locale,
  taxPrice,
}: {
  stFeePrice: number;
  price: string;
  locale: string;
  taxPrice: number;
}) => {
  //const tCabinModal = useTranslations("CabinModal");

  return (
    <>
      <TableRow firstCol={"singleTicket"} secondCol={localNumberFormat(stFeePrice)} />
      <Divider
        sx={{
          width: "100%",
        }}
      />
      <TableRow
        firstCol={"subTotal"}
        secondCol={localNumberFormat(handleTotalSingle(price, stFeePrice, taxPrice), locale)}
      />
    </>
  );
};
