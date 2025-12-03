import { useState } from "react";
import {
  alpha,
  Box,
  Button,
  Checkbox,
  Dialog,
  DialogContent,
  DialogTitle,
  Divider,
  FormControlLabel,
  IconButton,
  Typography,
  useMediaQuery,
  useTheme,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";

import TableRow from "./TableRow";
import PrivateCabin from "./PrivateCabin";
import SingleTicket from "./SingleTicket";
import IframeComponent from "./IframeComponent";
import ImageComponent from "./ImageComponent";

import { localNumberFormat } from "./utils/stringUtils";
import { dialogContentStyles, dialogPaperStyles } from "./utils/dialogStyles";

type Props = {
  eventId: number;
  open: boolean;
  handleModalClose: () => void;
  selectedCabinDetail: any | null;
  cabinTypeSlug: string;
};

export default function ModalCabin_bkp({
  open,
  handleModalClose,
  selectedCabinDetail,
  cabinTypeSlug,
}: Props) {
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down("md"));

  const [modalLoading, setModalLoading] = useState(false);
  const [isAccepted, setIsAccepted] = useState(false);

  if (!selectedCabinDetail?.price) return null;

  const {
    price,
    capacity,
    full_title,
    description,
    images,
    iframe,
  } = selectedCabinDetail;

  const closeModal = () => {
    setModalLoading(false);
    setIsAccepted(false);
    handleModalClose();
  };

  return (
    <Dialog
      open={open}
      onClose={closeModal}
      fullScreen={fullScreen}
      maxWidth="xl"
      slotProps={{
        paper: { sx: dialogPaperStyles },
      }}
    >
      <DialogTitle sx={{ textAlign: "center", fontWeight: 500, pt: 5 }}>
        {full_title}
      </DialogTitle>

      <Divider />

      <IconButton
        aria-label="close"
        onClick={handleModalClose}
        sx={{ position: "absolute", right: 0, top: 2, color: theme.palette.grey[500] }}
      >
        <CloseIcon />
      </IconButton>

      <DialogContent sx={dialogContentStyles} className="scrollbar">
        <Box sx={{ width: "100%", maxWidth: { xs: "100%", md: "400px" } }}>
          <Box sx={{ display: "flex", flexDirection: "column" }}>
            <TableRow firstCol="pricePerPerson" secondCol={localNumberFormat(price)} />
            <Divider />
            <TableRow firstCol="tax" secondCol={localNumberFormat(22)} />
          </Box>

          <Divider />

          {cabinTypeSlug === "private-cabin" ? (
            <PrivateCabin price={price} taxPrice={2} capacity={Number(capacity)} />
          ) : (
            <SingleTicket stFeePrice={0} price={price} locale="en" taxPrice={1} />
          )}

          <Box sx={{ mt: 4 }}>
            <Box
              sx={{
                p: 1,
                display: "flex",
                flexDirection: "column",
                backgroundColor: !isAccepted ? alpha("#f44336", 0.1) : alpha("#1e88e5", 0.1),
                border: `1px solid ${!isAccepted ? "#f44336" : "#1e88e5"}`,
                borderRadius: 1,
              }}
            >
              <FormControlLabel
                label="accept_cabin_config"
                control={
                  <Checkbox
                    checked={isAccepted}
                    onChange={(e) => setIsAccepted(e.target.checked)}
                  />
                }
              />

              <Button
                fullWidth
                disabled={!isAccepted}
                loading={modalLoading}
                onClick={() => {}}
                variant="contained"
                sx={{ mt: 2 }}
              >
                confirm
              </Button>
            </Box>
          </Box>
        </Box>

        <Box sx={{ gap: 2 }}>
          <Typography
            align="center"
            variant="body2"
            dangerouslySetInnerHTML={{ __html: description?.en || "" }}
            sx={{ m: "10px auto 20px" }}
          />

          <Box sx={{ display: "flex", flexDirection: { xs: "column", md: "row" }, gap: 2 }}>
            <IframeComponent iframe={iframe} />
            <ImageComponent images={images} />
          </Box>

          <Typography
            component="small"
            align="center"
            variant="caption"
            sx={{ display: "block", mt: 2 }}
          >
            Note: All cabin images are representative samples. Actual cabins may differ in appearance.
          </Typography>
        </Box>
      </DialogContent>
    </Dialog>
  );
}
